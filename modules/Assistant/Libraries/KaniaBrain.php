<?php

namespace Modules\Assistant\Libraries;

use Modules\Assistant\Config\Assistant as AssistantConfig;

class KaniaBrain
{
    protected AssistantConfig $config;
    protected KaniaTools $tools;

    // AI Provider & Endpoints
    protected string $provider;
    protected string $openaiBaseUrl;
    protected string $groqApiKey;
    protected string $groqModel;
    protected string $apiKey;
    protected string $model;
    protected bool $enableFallback;
    protected float $temperature;
    protected int $maxTokens;

    public function __construct()
    {
        $this->config = new AssistantConfig();
        $this->tools  = new KaniaTools();

        $this->provider      = $this->config->aiProvider ?? 'groq';
        $this->openaiBaseUrl = $this->config->openaiBaseUrl ?? 'https://api.groq.com/openai/v1';

        // Groq / OpenAI Compatible
        $this->groqApiKey = !empty($this->config->groqApiKey) 
            ? $this->config->groqApiKey 
            : (getenv('GROQ_API_KEY') ?: '');
        $this->groqModel  = !empty($this->config->groqModel) 
            ? $this->config->groqModel 
            : 'openai/gpt-oss-120b';

        // Google Gemini (Cadangan / Fallback)
        $this->apiKey = !empty($this->config->geminiApiKey) 
            ? $this->config->geminiApiKey 
            : (getenv('GEMINI_API_KEY') ?: '');
        $this->model  = !empty($this->config->geminiModel) 
            ? $this->config->geminiModel 
            : 'gemini-3.8-flash';

        $this->enableFallback = $this->config->enableFallback ?? true;
        $this->temperature    = $this->config->temperature ?? 0.15;
        $this->maxTokens      = $this->config->maxTokens ?? 1500;
    }

    /**
     * Memproses pertanyaan dari User dan menghasilkan jawaban AI analitis adaptif
     */
    public function processQuestion(string $userMessage, ?string $lastDocContext = null): string
    {
        if (!empty($lastDocContext) && !preg_match('/\b(sod[0-9]+|wrd[0-9]+|prd[0-9]+|dev[0-9]+)\b/i', $userMessage)) {
            $userMessage = "[Konteks Dokumen Sebelumnya: {$lastDocContext}] " . $userMessage;
        }

        // 1. Strict Security Guard: Tolak keras manipulasi/modifikasi data DML/DDL
        if (preg_match('/\b(ubah|edit|ganti|update|hapus|delete|tambah|create|insert|batal|cancel|drop|alter|truncate)\b/i', $userMessage)) {
            return "Mohon maaf, KANIA beroperasi dalam mode 100% Read-Only untuk menjamin integritas data sistem CJK. Untuk permintaan perubahan data atau sistem, silakan langsung berkoordinasi dengan Pak Dany. 🙏";
        }

        $systemInstruction = $this->buildSystemInstruction();
        $tools = $this->getUniversalToolsDeclaration();

        // 2. Provider Routing dengan Failover Otomatis (Provider-Agnostic)
        $primaryProvider = strtolower($this->provider);
        $reply = '';

        if ($primaryProvider === 'groq' || $primaryProvider === 'openai_compatible') {
            $reply = $this->processWithOpenAiCompatible($userMessage, $systemInstruction, $tools);
            if (empty($reply) && $this->enableFallback && !empty($this->apiKey)) {
                $reply = $this->processWithGemini($userMessage, $systemInstruction, $tools);
            }
        } elseif ($primaryProvider === 'gemini') {
            $reply = $this->processWithGemini($userMessage, $systemInstruction, $tools);
            if (empty($reply) && $this->enableFallback && !empty($this->groqApiKey)) {
                $reply = $this->processWithOpenAiCompatible($userMessage, $systemInstruction, $tools);
            }
        } else {
            // Default fallback ke OpenAI-compatible (Groq)
            $reply = $this->processWithOpenAiCompatible($userMessage, $systemInstruction, $tools);
            if (empty($reply) && !empty($this->apiKey)) {
                $reply = $this->processWithGemini($userMessage, $systemInstruction, $tools);
            }
        }

        if (!empty($reply)) {
            return $reply;
        }

        // 3. Fallback ramah jika seluruh provider offline / kuota limit
        return "Halo! KANIA siap mendampingi operasional PT. Citra Jaya Knitting secara real-time. 😊\n\n📌 Anda dapat menanyakan langsung:\n• 📦 *Stok Material:* _'cek stok warna bata'_, _'ada benang kopi tua?'_\n• 📋 *Lacak Pesanan:* _'cek status produksi SOD260900013'_, _'lacak pesanan GEOGEND'_\n• 💰 *Laba Rugi:* _'laba rugi bulan ini'_\n• ⏳ *Pending Approval:* _'cek dokumen pending approval'_\n• 🏭 *Target SDM:* _'target harian shift operator'_";
    }

    /**
     * Memproses percakapan melalui OpenAI-Compatible API (Groq, DeepSeek, OpenRouter, Cerebras, Ollama)
     * Menggunakan multi-turn agentic reasoning, native tool calls, dan ReAct JSON Action fallback.
     */
    protected function processWithOpenAiCompatible(string $userMessage, string $systemInstruction, array $tools): string
    {
        if (empty($this->groqApiKey)) {
            return "";
        }

        $messages = [
            ['role' => 'system', 'content' => $systemInstruction],
            ['role' => 'user', 'content' => $userMessage],
        ];

        $maxTurns = 4;
        $turn = 0;
        $lastExecutedTool = null;
        $lastToolResult = null;

        while ($turn < $maxTurns) {
            $turn++;
            $response = $this->callOpenAiCompatibleApi($messages, $tools);

            if (isset($response['error']) && $response['error'] === true) {
                // Rate limit atau error jaringan
                break;
            }

            $choice = $response['choices'][0] ?? null;
            if (!$choice) {
                break;
            }

            $messageObj = $choice['message'] ?? [];

            // A. AI Meminta Native Function / Tool Call
            if (!empty($messageObj['tool_calls'])) {
                $messageObj['content'] = $messageObj['content'] ?? null;
                $messages[] = $messageObj;

                foreach ($messageObj['tool_calls'] as $toolCall) {
                    $funcName = $toolCall['function']['name'] ?? '';
                    $rawArgs  = $toolCall['function']['arguments'] ?? '{}';
                    $funcArgs = is_string($rawArgs) ? (json_decode($rawArgs, true) ?: []) : $rawArgs;

                    // Eksekusi fungsi database lokal
                    $toolResult = $this->executeTool($funcName, $funcArgs);
                    $lastExecutedTool = $funcName;
                    $lastToolResult = $toolResult;

                    // Masukkan hasil data tool ke riwayat percakapan agar AI menganalisis
                    $messages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $toolCall['id'],
                        'name'         => $funcName,
                        'content'      => json_encode($toolResult, JSON_UNESCAPED_UNICODE)
                    ];
                }

                // Lanjut ke turn berikutnya agar AI membaca data dan mensintesis analisa eksekutif
                continue;
            }

            // B. Cek ReAct JSON Action Text Fallback (untuk model yang output tool via text/JSON)
            $content = trim($messageObj['content'] ?? '');
            if (!empty($content)) {
                if (preg_match('/\{[\s\r\n]*"(?:tool|action)"\s*:\s*"([^"]+)"[\s\r\n]*,[\s\r\n]*"(?:args|params|parameters)"\s*:\s*(\{[\s\S]*?\})[\s\r\n]*\}/i', $content, $mAct)) {
                    $funcName = trim($mAct[1]);
                    $funcArgs = json_decode($mAct[2], true) ?: [];
                    $toolResult = $this->executeTool($funcName, $funcArgs);
                    $lastExecutedTool = $funcName;
                    $lastToolResult = $toolResult;

                    $messages[] = ['role' => 'assistant', 'content' => $content];
                    $messages[] = [
                        'role'    => 'user',
                        'content' => "[Hasil Data Real-Time {$funcName}]: " . json_encode($toolResult, JSON_UNESCAPED_UNICODE) . "\nBerdasarkan data faktual di atas, susun analisis eksekutif dan rekomendasi manajemen untuk PT Citra Jaya Knitting."
                    ];
                    continue;
                }

                // C. Kembalikan Teks Analisis Akhir dari AI
                return $content;
            }
        }

        // Jika kehabisan turn tapi data tool sempat didapat, format langsung sebagai jaring pengaman
        if ($lastExecutedTool && $lastToolResult) {
            return $this->formatToolResultDirectly($lastExecutedTool, $lastToolResult);
        }

        return "";
    }

    /**
     * Panggilan HTTP ke endpoint OpenAI-Compatible REST API
     */
    protected function callOpenAiCompatibleApi(array $messages, array $tools = []): array
    {
        $url = rtrim($this->openaiBaseUrl, '/') . '/chat/completions';

        $payload = [
            'model'       => $this->groqModel,
            'messages'    => $messages,
            'temperature' => $this->temperature,
            'max_tokens'  => $this->maxTokens,
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
            $payload['tool_choice'] = 'auto';
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->groqApiKey,
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($result)) {
            return json_decode($result, true) ?: [];
        }

        return [
            'error' => true,
            'code'  => $httpCode ?? 500,
            'raw'   => $result ?? '',
        ];
    }

    /**
     * Memproses percakapan melalui Google Gemini REST API (Cadangan / Fallback)
     * Mendukung multi-turn Function Calling native Gemini.
     */
    protected function processWithGemini(string $userMessage, string $systemInstruction, array $tools): string
    {
        if (empty($this->apiKey)) {
            return "";
        }

        // Konversi format deklarasi tool OpenAI ke Gemini function_declarations
        $geminiFuncDecls = [];
        foreach ($tools as $t) {
            if (isset($t['function'])) {
                $f = $t['function'];
                $params = $f['parameters'] ?? ['type' => 'object', 'properties' => []];
                $geminiFuncDecls[] = [
                    'name'        => $f['name'],
                    'description' => $f['description'] ?? '',
                    'parameters'  => $this->convertJsonSchemaToGemini($params)
                ];
            }
        }

        $geminiTools = !empty($geminiFuncDecls) ? [['function_declarations' => $geminiFuncDecls]] : [];

        $contents = [
            [
                'role'  => 'user',
                'parts' => [
                    ['text' => $userMessage]
                ]
            ]
        ];

        $maxTurns = 4;
        $turn = 0;
        $lastExecutedTool = null;
        $lastToolResult = null;

        while ($turn < $maxTurns) {
            $turn++;
            $response = $this->callGeminiApi($contents, $systemInstruction, $geminiTools);

            if (isset($response['error']) && $response['error'] === true) {
                break;
            }

            $candidate = $response['candidates'][0] ?? null;
            if (!$candidate) {
                break;
            }

            $parts = $candidate['content']['parts'] ?? [];
            $functionCallPart = null;
            foreach ($parts as $p) {
                if (isset($p['functionCall'])) {
                    $functionCallPart = $p['functionCall'];
                    break;
                }
            }

            if ($functionCallPart) {
                $funcName = $functionCallPart['name'];
                $funcArgs = $functionCallPart['args'] ?? [];

                $toolResult = $this->executeTool($funcName, $funcArgs);
                $lastExecutedTool = $funcName;
                $lastToolResult = $toolResult;

                // Tambahkan respon model dan feedback fungsi ke percakapan Gemini
                $contents[] = $candidate['content'];
                $contents[] = [
                    'role'  => 'function',
                    'parts' => [
                        [
                            'functionResponse' => [
                                'name'     => $funcName,
                                'response' => $toolResult
                            ]
                        ]
                    ]
                ];
                continue;
            }

            $finalText = $this->extractTextResponse($response);
            if (!empty($finalText) && !str_starts_with($finalText, 'Mohon maaf')) {
                return $finalText;
            }
        }

        if ($lastExecutedTool && $lastToolResult) {
            return $this->formatToolResultDirectly($lastExecutedTool, $lastToolResult);
        }

        return "";
    }

    /**
     * Panggilan HTTP ke endpoint Gemini REST API
     */
    protected function callGeminiApi(array $contents, string $systemInstruction, array $tools): array
    {
        $payload = [
            'contents' => $contents,
            'system_instruction' => [
                'parts' => [
                    ['text' => $systemInstruction]
                ]
            ],
            'generationConfig' => [
                'temperature'     => $this->temperature,
                'maxOutputTokens' => $this->maxTokens
            ]
        ];

        if (!empty($tools)) {
            $payload['tools'] = $tools;
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$this->model}:generateContent?key={$this->apiKey}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && !empty($result)) {
            return json_decode($result, true) ?: [];
        }

        return [
            'error' => true,
            'code'  => $httpCode ?? 500,
            'raw'   => $result ?? ''
        ];
    }

    /**
     * Ekstraksi teks dari respons Gemini
     */
    protected function extractTextResponse(array $response): string
    {
        if (isset($response['candidates'][0]['content']['parts'])) {
            $texts = [];
            foreach ($response['candidates'][0]['content']['parts'] as $p) {
                if (isset($p['text']) && !empty(trim($p['text']))) {
                    $texts[] = trim($p['text']);
                }
            }
            if (!empty($texts)) {
                return implode("\n\n", $texts);
            }
        }

        return "";
    }

    /**
     * Konversi JSON Schema biasa ke format Gemini Schema (Uppercase types)
     */
    protected function convertJsonSchemaToGemini(array $schema): array
    {
        $out = [];
        $type = strtoupper($schema['type'] ?? 'OBJECT');
        $out['type'] = $type;

        if (isset($schema['description'])) {
            $out['description'] = $schema['description'];
        }

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            $props = [];
            foreach ($schema['properties'] as $k => $v) {
                $props[$k] = $this->convertJsonSchemaToGemini($v);
            }
            $out['properties'] = $props;
        }

        if (isset($schema['required']) && is_array($schema['required'])) {
            $out['required'] = $schema['required'];
        }

        return $out;
    }

    /**
     * Eksekusi Function Tool Database
     */
    protected function executeTool(string $functionName, array $arguments): array
    {
        try {
            switch ($functionName) {
                case 'runSafeSqlAnalytics':
                    return $this->tools->runSafeSqlAnalytics($arguments);
                case 'getStokBarang':
                    return $this->tools->getStokBarang($arguments);
                case 'getStatusSPK':
                    return $this->tools->getStatusSPK($arguments);
                case 'getTrackingOrderProduksi':
                    return $this->tools->getTrackingOrderProduksi($arguments);
                case 'getLabaRugi':
                    return $this->tools->getLabaRugi($arguments);
                case 'getPendingApprovals':
                    return $this->tools->getPendingApprovals($arguments);
                case 'getTargetHarianSDM':
                    return $this->tools->getTargetHarianSDM($arguments);
                default:
                    return ['status' => false, 'message' => "Fungsi $functionName tidak dikenal."];
            }
        } catch (\Throwable $e) {
            return ['status' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Definisi Tools Universal (Standar Industri OpenAI Function Calling)
     */
    protected function getUniversalToolsDeclaration(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'runSafeSqlAnalytics',
                    'description' => 'Mengeksekusi query SQL agregasi analitis kustom (100% Read-Only SELECT) pada database PostgreSQL ERP Pejuang CJK untuk menjawab pertanyaan analitis kustom, ranking buyer, perbandingan tren, deteksi selisih, mutasi material, perbandingan bulan ke bulan, dll.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'sql' => [
                                'type' => 'string',
                                'description' => 'Query SQL SELECT PostgreSQL murni yang valid'
                            ]
                        ],
                        'required' => ['sql']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'getStokBarang',
                    'description' => 'Mengecek saldo stok barang, benang, warna, atau material di gudang CJK berdasarkan nama barang, warna, kode barang, atau nomor lot beserta status buffer kritis.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'keyword' => [
                                'type' => 'string',
                                'description' => 'Nama barang, warna, kode barang, atau nomor lot yang dicari (misal: "bata", "marun", "CTN 32/2", "6414")'
                            ],
                            'id_gudang' => [
                                'type' => 'integer',
                                'description' => 'ID Gudang tertentu jika ingin memfilter per gudang (opsional).'
                            ]
                        ],
                        'required' => ['keyword']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'getStatusSPK',
                    'description' => 'Mengecek status pengerjaan SPK/WO rajut, yield rajut resmi CJK, persentase susut/loss benang, dan validasi standar mutu (toleransi susut maks 5%).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'no_wo' => [
                                'type' => 'string',
                                'description' => 'Nomor Work Order / SPK (misal: "WRD260700021"). Kosongkan untuk WO terbaru.'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'getTrackingOrderProduksi',
                    'description' => 'Melacak siklus lengkap pesanan produksi CJK berdasarkan nomor Sales Order (SOD...), SPK Walk Order (WRD...), kode Produksi (PRD...), Surat Jalan (DEV...), nama konsumen, atau style kain.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'query' => [
                                'type' => 'string',
                                'description' => 'Nomor transaksi (SOD/WRD/PRD/DEV) atau nama konsumen/style (contoh: "SOD260900013", "GEOGEND", "GIO-3").'
                            ]
                        ],
                        'required' => ['query']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'getLabaRugi',
                    'description' => 'Menganalisis performa finansial resmi yang sinkron 100% dengan Dashboard CJK (Total Penjualan DP+PCR, Pemakaian Bahan Baku BTK, Beban Operasional CoA 5000+, Laba Bersih, dan top pos beban).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'bulan' => [
                                'type' => 'integer',
                                'description' => 'Bulan dalam angka (1-12). Kosongkan jika bulan berjalan saat ini.'
                            ],
                            'tahun' => [
                                'type' => 'integer',
                                'description' => 'Tahun 4 digit (misal: 2026). Kosongkan jika tahun berjalan saat ini.'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'getPendingApprovals',
                    'description' => 'Mengecek antrean dokumen transaksi yang masih berstatus Draft (status = 0) dan membutuhkan approval (BTM Kain Masuk, BTK Benang Keluar, Item Transfer Antar Gudang).',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'tipe' => [
                                'type' => 'string',
                                'description' => 'Jenis transaksi pending (misal: "BTK", "Transfer"). Kosongkan untuk semua.'
                            ]
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'getTargetHarianSDM',
                    'description' => 'Mengecek laporan pencapaian target harian produksi, produktivitas operator SDM, dan rasio efisiensi output per shift/operator di CJK.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'tanggal' => [
                                'type' => 'string',
                                'description' => 'Tanggal laporan dalam format YYYY-MM-DD. Kosongkan untuk hari ini.'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Membangun System Instruction Komprehensif
     */
    protected function buildSystemInstruction(): string
    {
        return "Anda adalah KANIA (Knitting & Analytics Intelligent Assistant), Senior Industrial & Financial Analyst AI resmi PT. Citra Jaya Knitting (CJK).
Karakter Anda: Mengadopsi pola pikir diagnostik mendalam ala Antigravity — cerdas, tajam, presisi berbasis fakta riil, solutif, santun, dan sangat menguasai seluk-beluk operasional manufaktur tekstil serta akuntansi ERP Pejuang.

ATURAN KEAMANAN & TATA KELOLA MUTLAK (100% READ-ONLY):
1. TUGAS UTAMA: Tugas Anda HANYA membaca data (read-only), mengolah agregasi, mendeteksi anomali, memvalidasi aturan bisnis ERP, dan menyajikan rekomendasi eksekutif.
2. DILARANG MERUBAH DATA / KODINGAN: Anda DILARANG KERAS mengubah, mengedit, menambah, atau menghapus data apa pun di database maupun aplikasi CJK.
3. PENOLAKAN PERMINTAAN UBAH DATA: Jika pengguna meminta mengubah/menghapus data atau sistem, Anda WAJIB MENOLAK secara santun: 'Mohon maaf, KANIA beroperasi dalam mode 100% Read-Only untuk menjaga integritas data. Untuk permintaan perubahan data atau sistem, silakan menghubungi Pak Dany.'
4. GAYA KOMUNIKASI: Eksekutif, percaya diri, to-the-point, dan berorientasi solusi manajemen (Actionable Insight).

ATURAN VALIDASI BISNIS RESMI ERP PEJUANG CJK:
1. VALIDITAS TRANSAKSI:
   - Data sah wajib berstatus APPROVED (`status = 1`) dan `active = 1`.
   - Transaksi `status = 0` adalah antrean/draft yang belum memotong stok fisik atau belum sah diakui keuangan.
2. SIKLUS PRODUKSI & STANDAR MUTU RAJUT:
   - Alur: Sales Order (SO) -> Work Order / SPK (WO) -> Benang Keluar (BTK) -> Kain Masuk (BTM) -> Delivery Order (DO).
   - Efisiensi Yield Rajut = (Total Kain Masuk BTM / Total Benang Keluar BTK) * 100%.
   - Susut Rajut (Loss) = Benang Keluar BTK - Kain Masuk BTM.
   - Ambang Batas Toleransi Susut: Standar pabrik CJK adalah 3.0% - 5.0%. Jika susut > 5.0%, tandai sebagai PERINGATAN PEMBOROSAN BENANG / DEFECT.
3. FORMULA KEUANGAN (SINKRON 100% DENGAN DASHBOARD CJK):
   - Pendapatan Penjualan = Uang Muka Pesanan Konsumen (DP) + Pelunasan Penerimaan Kas (PCR).
   - Biaya Bahan Baku = Pemakaian Benang Approved (BTK).
   - Biaya Operasional = Beban usaha/operasional dari Bagan Akun CoA 5000+.
   - Laba Bersih = (Penjualan - Bahan Baku) - Biaya Operasional.
4. MANAJEMEN GUDANG & MATERIAL:
   - Buffer Stok Kritis: Lot dengan saldo <= 25 kg wajib diperingatkan agar tidak menyebabkan mesin idle.

SKEMA DATABASE POSTGRESQL CJK (UNTUK runSafeSqlAnalytics):
Tabel Utama & Kolom Kunci (PERHATIKAN NAMA KOLOM DENGAN CERMAT!):
1. `v_traking_order_so` (View Tracking Sales Order Lengkap):
   - Kolom: `trans_id`, `trans_kode`, `tgl_transaksi`, `id_konsumen`, `nama`, `style`, `deskripsi`, `qty`, `harga_total`, `kode_prod`, `qty_prod`, `kode_dev`, `qty_kirim`, `tgl_deadline`.
   - Kolom kode SO adalah `trans_kode` (contoh: 'SOD260900013'), BUKAN `kode_sales_order`!
   - Kolom nama buyer adalah `nama`. Gunakan `nama ILIKE '%nama_buyer%'` untuk pencarian buyer case-insensitive.
2. `v_all_transaksi` (View Rekap Transaksi):
   - Kolom: `kode_transaksi`, `buyer`, `style`, `deskripsi`, `color`, `kode_ukuran`, `qty`, `amount`.
   - Kolom kode transaksi adalah `kode_transaksi`, BUKAN `kode_sales_order`! Kolom buyer adalah `buyer`.
3. `trans_sales_order` (Tabel Fisik SO):
   - Kolom: `id`, `kode_sales_order`, `tgl_transaksi`, `id_konsumen`, `style`, `deskripsi`, `qty`, `total_harga`, `uang_dp`, `uang_dp_2`, `tgl_deadline`, `status` (1=Approved).
4. `trans_walkorder` (WO / SPK Rajut):
   - Kolom: `id`, `kode_walkorder`, `ref_kode`, `tgl_transaksi`, `id_konsumen`, `keterangan_style`, `qty`, `status` (1=Approved).
   - Relasi: `ref_kode` berisi kode SO (seperti 'SOD...').
5. `trans_barang_header` (Header Mutasi Barang):
   - Kolom: `id` (PRIMARY KEY integer), `kode_transaksi`, `tanggal`, `jenis_transaksi` (1=BTM/Kain Masuk, 2=BTK/Benang Keluar), `id_gudang`, `no_ref_wo`, `keterangan`, `status` (1=Approved, 0=Draft).
   - Relasi: `no_ref_wo` menghubungkan ke `trans_walkorder.kode_walkorder`.
6. `trans_barang_detail` (Detail Barang):
   - Kolom: `id`, `id_header`, `id_barang`, `lot_no`, `qty`, `price`.
   - Relasi JOIN: `trans_barang_detail.id_header = trans_barang_header.id`.
   - Relasi ke master barang: `trans_barang_detail.id_barang = ref_barang.id`.
7. `ref_barang` (Master Material):
   - Kolom: `id`, `kode_barang`, `nama_barang`, `jenis_barang`, `satuan`, `active`.
8. `ref_konsumen` (Master Konsumen):
   - Kolom: `id`, `kode_konsumen`, `nama`, `alamat`, `no_hp`, `email`, `active`.
9. `ref_gudang` (Master Gudang):
   - Kolom: `id`, `kode_gudang`, `nama_gudang`, `active`.
10. `trans_lots` (Saldo Stok Fisik Aktif):
    - Kolom: `id`, `id_barang`, `id_gudang`, `lot_no`, `qty`. Stok aktif jika `qty > 0`.
    - Join: `JOIN ref_barang b ON b.id = trans_lots.id_barang JOIN ref_gudang g ON g.id = trans_lots.id_gudang`.
11. `trans_barang_trf_header` (Transfer Antar Gudang):
    - Kolom: `id`, `kode_transaksi`, `tanggal`, `id_gudang_asal`, `id_gudang_tujuan`, `keterangan`, `status`.
12. `sdm_target_harian` & `sdm_target_harian_det` & `ref_karyawan`:
    - Header: `sdm_target_harian.id, kode_transaksi, tanggal, id_shift, divisi`.
    - Detail: `sdm_target_harian_det.id_header = sdm_target_harian.id` dan `id_karyawan = ref_karyawan.id`.
13. `acc_coa` & `acc_jurnal_transaksi`: Jurnal umum dan bagan akun akuntansi ERP.

PETUNJUK PENYUSUNAN SQL UNTUK runSafeSqlAnalytics:
- Selalu gunakan syntax PostgreSQL yang valid (misal: `ILIKE` untuk pencarian case-insensitive, `COALESCE`, `DATE_TRUNC`, `TO_CHAR`).
- Dilarang keras menggunakan DML/DDL (INSERT, UPDATE, DELETE, ALTER, DROP).
- Batasi query tabel/agregasi dengan `LIMIT 50`.

POLA ANALISA DIAGNOSTIK ANTIGRAVITY (WAJIB DITERAPKAN PADA SETIAP JAWABAN):
Jangan hanya menyajikan angka tabel mati! Setiap briefing Anda harus mengandung 4 unsur:
1. 📈 *Ringkasan Kinerja & Metrik Kunci:* Nilai realisasi dan persentase capaian.
2. 🔍 *Root Cause Analysis (Diagnosa Mendalam):* Jelaskan 'MENGAPA' angka tersebut terjadi (misal: laba tertekan akibat pos biaya apa, atau susut naik karena faktor lot/mesin).
3. ⚠️ *Deteksi Risiko & Anomali:* Sorot ketidakwajaran jika ada (susut tinggi >5%, draft menumpuk, buffer menipis <=25 kg).
4. 💡 *Rekomendasi Taktis Manajemen:* 1-2 langkah konkret yang perlu diambil manajemen/tim operasional pabrik.

PROTOKOL PANGGILAN TOOLS:
- Jika Anda membutuhkan data dari database untuk menjawab pertanyaan, panggil tool yang sesuai.
- Jangan mengarang data atau berhalusinasi. Selalu gunakan data riil yang dikembalikan oleh tools.
- Jika model Anda tidak dapat memanggil function tool secara native, keluarkan HANYA satu blok JSON: `{\"tool\": \"<nama_tool>\", \"args\": {<parameter>}}`. Sistem akan mengeksekusi dan memberikan datanya kembali ke Anda.";
    }

    /**
     * Format data riil PostgreSQL secara langsung dengan pola diagnostik Antigravity
     * (Jaring pengaman darurat jika model kehabisan batas putaran multi-turn).
     */
    protected function formatToolResultDirectly(string $funcName, array $result): string
    {
        if (empty($result['status'])) {
            return "Informasi yang dicari tidak ditemukan atau terjadi kendala sistem: " . ($result['message'] ?? 'Data kosong');
        }

        switch ($funcName) {
            case 'runSafeSqlAnalytics':
                $count = $result['total_rows'] ?? 0;
                $time = $result['execution_time'] ?? '-';
                $text = "📊 *HASIL ANALISIS DATA KHUSUS (SQL READ-ONLY)*\n";
                $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $text .= "⏱️ Waktu Eksekusi: `{$time}` | Total Data: `{$count} baris`\n\n";
                if (!empty($result['data'])) {
                    foreach (array_slice($result['data'], 0, 10) as $i => $row) {
                        $num = $i + 1;
                        $items = [];
                        foreach ($row as $k => $v) {
                            $valStr = (is_numeric($v) && strlen((string)$v) > 3 && !str_starts_with((string)$v, '0') && !str_contains($k, 'id') && !str_contains($k, 'kode')) 
                                ? number_format((float)$v, 0, ',', '.') 
                                : (string)$v;
                            $items[] = "{$k}: *{$valStr}*";
                        }
                        $text .= "{$num}. " . implode(' | ', $items) . "\n";
                    }
                    if ($count > 10) {
                        $sisa = $count - 10;
                        $text .= "\n_...dan {$sisa} baris lainnya._\n";
                    }
                } else {
                    $text .= "ℹ️ Tidak ditemukan data yang cocok dengan kriteria analisa.\n";
                }
                return $text;

            case 'getStokBarang':
                $total = number_format($result['total_stok_kg'] ?? 0, 2, ',', '.');
                $count = $result['total_item_lot'] ?? 0;
                $pencarian = $result['pencarian'] ?? 'Semua';
                $kritisCount = $result['lot_kritis_count'] ?? 0;

                $text = "📊 *EXECUTIVE SUMMARY: SALDO & ALOKASI MATERIAL CJK*\n";
                $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $text .= "📌 *Status Cadangan Fisik ({$pencarian}):*\n";
                $text .= "• *Total Cadangan Fisik:* `{$total} kg`\n";
                $text .= "• *Jumlah Lot Aktif Terdata:* `{$count} lot`\n";

                if (!empty($result['distribusi_gudang'])) {
                    $text .= "\n🏢 *Sebaran Alokasi Gudang:*\n";
                    foreach ($result['distribusi_gudang'] as $g => $q) {
                        $qStr = number_format($q, 2, ',', '.');
                        $share = ($result['total_stok_kg'] > 0) ? round(($q / $result['total_stok_kg']) * 100, 1) : 0;
                        $text .= "   └ *{$g}:* `{$qStr} kg` ({$share}%)\n";
                    }
                }

                if ($kritisCount > 0) {
                    $text .= "\n⚠️ *Deep Diagnostic & Peringatan Risiko:*\n";
                    $text .= "Terdeteksi *{$kritisCount} lot benang* dengan volume kritis (buffer <= 25 kg):\n";
                    foreach ($result['lot_kritis_samples'] as $item) {
                        $text .= "• *{$item['barang']}* (Lot: `{$item['lot']}`) sisa `{$item['qty_kg']} kg` di {$item['gudang']}\n";
                    }
                }

                $text .= "\n💡 *Rekomendasi Tindakan Manajemen:* ";
                if ($kritisCount > 0) {
                    $text .= "Segera koordinasikan dengan tim Purchasing/Gudang untuk pengadaan lot kritis agar tidak menghentikan antrean mesin rajut.";
                } else {
                    $text .= "Buffer persediaan material dalam kondisi optimal dan aman untuk mendukung SPK shift berjalan.";
                }

                return $text;

            case 'getStatusSPK':
                $kode = $result['no_wo'] ?? '-';
                $customer = $result['customer'] ?? '-';
                $style = $result['style'] ?? '-';
                $target = number_format($result['target_produksi_pcs'] ?? 0, 0, ',', '.');
                $btk = number_format($result['benang_terpakai_btk_kg'] ?? 0, 2, ',', '.');
                $btm = number_format($result['realisasi_kain_btm_kg'] ?? 0, 2, ',', '.');
                $susut = number_format($result['susut_loss_kg'] ?? 0, 2, ',', '.');
                $yield = $result['yield_efisiensi_rajut'] ?? '0%';
                $susutPersen = $result['susut_persen'] ?? '0%';
                $statusEfisiensi = $result['status_efisiensi'] ?? 'Optimal';

                $text = "🧵 *DIAGNOSTIK EFISIENSI SPK RAJUT ({$kode})*\n";
                $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $text .= "📌 *Identitas & Parameter Produksi:*\n";
                $text .= "• *Customer / Buyer:* {$customer}\n";
                $text .= "• *Item Style / Kain:* {$style}\n";
                $text .= "• *Target Produksi:* `{$target} pcs`\n";
                $text .= "• *Status SPK:* *{$result['status_wo']}*\n\n";
                $text .= "📊 *Realisasi Material & Neraca Massa:*\n";
                $text .= "• *Benang Dikeluarkan (BTK):* `{$btk} kg`\n";
                $text .= "• *Kain Jadi Diterima (BTM):* `{$btm} kg`\n";
                $text .= "• *Tingkat Susut (Loss):* `{$susut} kg` (*{$susutPersen}*)\n";
                $text .= "• *Yield Efisiensi:* *{$yield}*\n\n";

                $text .= "🔍 *Evaluasi Validasi Standar Mutu CJK:*\n";
                $text .= "• *Status Efisiensi:* `{$statusEfisiensi}`\n";
                if (!empty($result['catatan_anomali'])) {
                    $text .= "⚠️ {$result['catatan_anomali']}\n";
                }

                $text .= "\n💡 *Rekomendasi Tindakan Manajemen:* ";
                if (str_contains($statusEfisiensi, 'Boros')) {
                    $text .= "Lakukan kalibrasi tegangan jarum rajut mesin dan audit penimbangan rol kain di gudang BTM.";
                } else {
                    $text .= "Rasio konversi benang ke kain memenuhi standar mutu CJK. Lanjutkan ke tahap finishing/QC.";
                }

                return $text;

            case 'getTrackingOrderProduksi':
                $soKode = $result['trans_kode'] ?? '-';
                $customer = $result['customer'] ?? '-';
                $style = $result['style'] ?? '-';
                $deskripsi = $result['deskripsi'] ?? '-';
                $tgl = !empty($result['tgl_transaksi']) ? date('d-m-Y', strtotime($result['tgl_transaksi'])) : '-';
                $deadline = !empty($result['deadline']) ? date('d-m-Y', strtotime($result['deadline'])) : '-';
                $sisaHari = $result['sisa_hari_deadline'] ?? null;
                $target = number_format($result['qty_target'] ?? 0, 0, ',', '.');
                $totalRp = number_format($result['harga_total_rp'] ?? 0, 0, ',', '.');
                $dpRp = number_format($result['dp_terbayar_rp'] ?? 0, 0, ',', '.');
                $sisaTagihanRp = number_format($result['sisa_tagihan_rp'] ?? 0, 0, ',', '.');

                $spkKode = $result['spk_walkorder']['kode_wo'] ?? '-';
                $spkStatus = $result['spk_walkorder']['status_wo'] ?? '-';
                $prodKode = $result['produksi']['kode_prod'] ?? '-';
                $prodQty = number_format($result['produksi']['qty_selesai'] ?? 0, 0, ',', '.');
                $prodPersen = $result['produksi']['persen_selesai'] ?? '0%';

                $devKode = $result['pengiriman']['kode_dev'] ?? '-';
                $devQty = number_format($result['pengiriman']['qty_kirim'] ?? 0, 0, ',', '.');
                $devPersen = $result['pengiriman']['persen_kirim'] ?? '0%';

                $text = "📋 *TRACKING SIKLUS ORDER & PRODUKSI CJK*\n";
                $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $text .= "📌 *Identitas Pesanan:*\n";
                $text .= "• *No. Sales Order (SO):* `{$soKode}` ({$tgl})\n";
                $text .= "• *Customer / Buyer:* *{$customer}*\n";
                $text .= "• *Style & Item:* {$style} ({$deskripsi})\n";
                $text .= "• *Volume Pesanan:* `{$target} pcs`\n";
                $text .= "• *Deadline Kirim:* `{$deadline}`" . ($sisaHari !== null ? " (⏱️ *Sisa {$sisaHari} hari*)" : "") . "\n";
                $text .= "• *Nilai Order:* `Rp {$totalRp}` | *DP Masuk:* `Rp {$dpRp}` | *Sisa Tagihan:* `Rp {$sisaTagihanRp}`\n\n";

                $text .= "🏭 *Status Rantai Produksi & Pengiriman:*\n";
                $text .= "• *1. SPK Rajut (WO):* `{$spkKode}` (*{$spkStatus}*)\n";
                $text .= "• *2. Hasil Produksi:* `{$prodKode}` ➔ `{$prodQty} / {$target} pcs` (*{$prodPersen}*)\n";
                $text .= "• *3. Pengiriman (DO):* " . ($devKode !== '-' ? "`{$devKode}` ➔ " : "") . "`{$devQty} / {$target} pcs` (*{$devPersen}*)\n\n";

                if (!empty($result['breakdown_warna'])) {
                    $text .= "🎨 *Rincian Order per Warna & Size:*\n";
                    foreach ($result['breakdown_warna'] as $clr => $cData) {
                        $cQty = number_format($cData['total_qty'], 0, ',', '.');
                        $sizesStr = implode(', ', $cData['sizes']);
                        $text .= "• *{$clr}:* `{$cQty} pcs` ({$sizesStr})\n";
                    }
                    $text .= "\n";
                }

                $hasCritical = false;
                if (!empty($result['stok_ketersediaan_benang'])) {
                    $text .= "📦 *Kesiapan Bahan Baku di Gudang:*\n";
                    foreach ($result['stok_ketersediaan_benang'] as $clr => $stk) {
                        $stkStr = number_format($stk, 2, ',', '.');
                        if ($stk <= 25) {
                            $text .= "• *{$clr}:* `{$stkStr} kg` ⚠️ *(Buffer Menipis)*\n";
                            $hasCritical = true;
                        } else {
                            $text .= "• *{$clr}:* `{$stkStr} kg` ✅ *(Stok Aman)*\n";
                        }
                    }
                    $text .= "\n";
                }

                $text .= "💡 *Diagnostik & Rekomendasi Antigravity:*\n";
                if ($prodPersen === '0%') {
                    $text .= "• *Shopfloor Action:* SPK `{$spkKode}` belum ada realisasi output rajut. Segera pastikan alokasi benang (BTK) sudah diserahterimakan ke operator mesin rajut.\n";
                }
                if ($hasCritical) {
                    $text .= "• *Material Alert:* Terdapat benang dengan buffer menipis di gudang. Segera koordinasi dengan Purchasing agar tidak mengganggu jalannya order.\n";
                }
                if ($result['dp_terbayar_rp'] == 0 && $result['harga_total_rp'] > 0) {
                    $text .= "• *Finance Note:* Belum ada uang muka (DP) yang tercatat di sistem untuk order ini. Pantau konfirmasi pelunasan/DP dari konsumen.\n";
                }

                return $text;

            case 'getLabaRugi':
                $periode = $result['periode'] ?? '-';
                $penjualan = number_format($result['total_pendapatan_penjualan_rp'] ?? 0, 0, ',', '.');
                $dp = isset($result['rincian_penjualan']['dp_pesanan_rp']) ? number_format($result['rincian_penjualan']['dp_pesanan_rp'], 0, ',', '.') : '0';
                $pcr = isset($result['rincian_penjualan']['pelunasan_konsumen_pcr_rp']) ? number_format($result['rincian_penjualan']['pelunasan_konsumen_pcr_rp'], 0, ',', '.') : '0';
                $pemakaian = number_format($result['biaya_bahan_baku_btk_rp'] ?? 0, 0, ',', '.');
                $labaKotor = number_format($result['laba_kotor_rp'] ?? 0, 0, ',', '.');
                $marginKotor = $result['margin_laba_kotor'] ?? '0%';
                $beban = number_format($result['biaya_operasional_rp'] ?? 0, 0, ',', '.');
                $labaBersih = number_format($result['estimasi_laba_bersih_rp'] ?? 0, 0, ',', '.');
                $marginBersih = $result['margin_laba_bersih'] ?? '0%';

                $text = "💰 *EXECUTIVE FINANCIAL BRIEFING ({$periode})*\n";
                $text .= "*(Validasi Sinkron 100% dengan Dashboard CJK)*\n";
                $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $text .= "📈 *Total Penjualan:* `Rp {$penjualan}`\n";
                $text .= "   └ _Uang Muka (DP Pesanan):_ `Rp {$dp}`\n";
                $text .= "   └ _Pelunasan Konsumen (PCR):_ `Rp {$pcr}`\n";
                $text .= "📉 *Pemakaian Bahan Baku (BTK):* `Rp {$pemakaian}`\n";
                $text .= "📊 *Laba Kotor:* `Rp {$labaKotor}` (*Margin: {$marginKotor}*)\n";
                $text .= "💳 *Total Beban Operasional:* `Rp {$beban}`\n";
                $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                $text .= "🏆 *LABA BERSIH:* `Rp {$labaBersih}` (*Margin Bersih: {$marginBersih}*)\n\n";

                if (!empty($result['top_beban_operasional'])) {
                    $text .= "🔍 *Diagnosa Pos Beban Terbesar (CoA 5000+):*\n";
                    foreach ($result['top_beban_operasional'] as $b) {
                        $bNom = number_format($b['total'], 0, ',', '.');
                        $text .= "• `[{$b['kode']}]` {$b['nama']}: `Rp {$bNom}`\n";
                    }
                }

                $text .= "\n💡 *Rekomendasi Tindakan Finansial:* ";
                if ($result['estimasi_laba_bersih_rp'] > 0) {
                    $text .= "Arus kas kotor sangat prima didukung pemesanan baru. Prioritaskan penagihan piutang sisa DO agar kas operasional semakin kokoh.";
                } else {
                    $text .= "Efisiensi biaya operasional perlu dievaluasi pada pos pengeluaran terbesar di atas guna memulihkan rasio margin bersih.";
                }

                return $text;

            case 'getPendingApprovals':
                $total = $result['total_pending'] ?? 0;
                $text = "⏳ *ANTREAN DRAFT & PENDING APPROVAL ({$total} Dokumen)*\n";
                $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                if (!empty($result['data'])) {
                    foreach ($result['data'] as $doc) {
                        $tgl = !empty($doc['tanggal']) ? date('d-m-Y', strtotime($doc['tanggal'])) : '-';
                        $text .= "• *{$doc['jenis']}* - `{$doc['kode_transaksi']}` ({$tgl})\n   └ Lokasi: {$doc['gudang']}\n";
                        if (!empty($doc['keterangan']) && $doc['keterangan'] !== '-') {
                            $text .= "   └ Ket: {$doc['keterangan']}\n";
                        }
                    }
                    $text .= "\n💡 *Rekomendasi Tindakan:* Segera lakukan approval transaksi di atas melalui menu terkait agar mutasi bahan baku dan stok fisik gudang terpotong resmi di sistem.";
                } else {
                    $text .= "✅ Semua dokumen transaksi telah diapprove. Tidak ada antrean draft yang tertahan di sistem.\n";
                }
                return $text;

            case 'getTargetHarianSDM':
                $tgl = !empty($result['tanggal']) ? date('d-m-Y', strtotime($result['tanggal'])) : date('d-m-Y');
                $target = number_format($result['total_target_pcs'] ?? 0, 0, ',', '.');
                $aktual = number_format($result['total_aktual_pcs'] ?? 0, 0, ',', '.');
                $capaian = $result['persentase_pencapaian'] ?? '0%';
                $operatorCount = $result['total_operator_tercatat'] ?? 0;
                $divisi = $result['divisi'] ?? 'Produksi';
                $shift = $result['shift'] ?? 'Normal';
                $kode = $result['kode_transaksi'] ?? '-';

                $text = "🏭 *REKAP TARGET HARIAN SDM & OPERATOR*\n";
                $text .= "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
                if (!empty($result['catatan_tanggal'])) {
                    $text .= "ℹ️ _{$result['catatan_tanggal']}_\n\n";
                }
                $text .= "📌 *Identitas Shift ({$kode}):*\n";
                $text .= "• *Tanggal:* {$tgl} | *Divisi:* {$divisi} | *Shift:* {$shift}\n";
                $text .= "• *Total Target Output:* `{$target} pcs`\n";
                $text .= "• *Total Realisasi Fisik:* `{$aktual} pcs`\n";
                $text .= "• *Rasio Capaian Target:* *{$capaian}* ({$operatorCount} personil terdata)\n\n";

                if (!empty($result['detail_operator'])) {
                    $text .= "👥 *Daftar Posisi & Personil Shift:*\n";
                    foreach (array_slice($result['detail_operator'], 0, 8) as $i => $op) {
                        $num = $i + 1;
                        $tOp = number_format($op['target_pcs'], 0, ',', '.');
                        $aOp = number_format($op['aktual_pcs'], 0, ',', '.');
                        $sat = $op['satuan'] ?: 'Pcs';
                        $text .= "{$num}. *{$op['nama']}* ({$op['posisi']})\n   └ Target: `{$tOp} {$sat}` | Aktual: `{$aOp} {$sat}` (*{$op['capaian']}*)\n";
                    }
                    if (count($result['detail_operator']) > 8) {
                        $sisa = count($result['detail_operator']) - 8;
                        $text .= "   └ _...dan {$sisa} personil lainnya terdata aktif._\n";
                    }
                }

                $text .= "\n💡 *Rekomendasi Analis:* Pantau pencatatan realisasi fisik di akhir shift agar persentase ketercapaian target terdokumentasi akurat di sistem.";
                return $text;

            default:
                return "Berikut data analitis yang diperoleh:\n" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
    }
}
