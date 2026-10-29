<?php

namespace Modules\Assistant\Controllers;

use App\Controllers\BaseController;
use Modules\Assistant\Config\Assistant as AssistantConfig;
use Modules\Assistant\Libraries\KaniaBrain;
use Modules\Assistant\Libraries\KaniaTools;

class Kania extends BaseController
{
    protected AssistantConfig $config;
    protected KaniaBrain $brain;
    protected KaniaTools $tools;

    public function __construct()
    {
        $this->MOD_ALIAS = "MOD_ASISTEN_AI";
        $this->config = new AssistantConfig();
        $this->brain  = new KaniaBrain();
        $this->tools  = new KaniaTools();
    }

    /**
     * Webhook utama yang menerima chat masuk dari WhatsApp Gateway
     */
    public function webhook()
    {
        $input = $this->request->getJSON(true) ?: $this->request->getPost();

        if (empty($input)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'No payload received'
            ])->setStatusCode(400);
        }

        $senderRaw = $input['from'] ?? '';
        $message   = trim($input['message'] ?? $input['body'] ?? '');
        $pushName  = $input['pushName'] ?? 'Pak Edwin';

        // Bersihkan nomor pengirim & siapkan variasi format (08... dan 628...)
        $senderPhone = preg_replace('/[^0-9]/', '', explode('@', $senderRaw)[0]);
        $phoneVariants = [
            $senderPhone,
            '0' . preg_replace('/^62/', '', $senderPhone),
            '62' . preg_replace('/^0/', '', $senderPhone)
        ];

        // 1. Validasi Whitelist Nomor (Hanya nomor yang didaftarkan yang dilayani)
        if (!$this->config->allowAnyNumberIfEmptyWhitelist && !empty($this->config->allowedPhoneNumbers)) {
            $isAllowed = str_contains($senderRaw, '@lid');
            if (!$isAllowed) {
                foreach ($phoneVariants as $pv) {
                    if (in_array($pv, $this->config->allowedPhoneNumbers, true)) {
                        $isAllowed = true;
                        break;
                    }
                }
            }

            if (!$isAllowed) {
                return $this->response->setJSON([
                    'status' => 'ignored',
                    'reason' => 'Sender not in whitelist'
                ]);
            }
        }

        // 2. Validasi Prefix Trigger: Hanya jika requireTriggerWord diaktifkan
        if (!empty($this->config->requireTriggerWord)) {
            $triggerPattern = preg_quote($this->config->triggerWord, '/');
            if (!preg_match('/^(' . $triggerPattern . '|kania|kira|kirana)([\s,.:!?\-].*|$)/i', $message, $matches)) {
                return $this->response->setJSON([
                    'status' => 'ignored',
                    'reason' => 'No trigger word found at start of message'
                ]);
            }
        }

        // Bersihkan kata pemicu jika ada di awal pertanyaan
        $triggerPattern = preg_quote($this->config->triggerWord, '/');
        $cleanQuestion = preg_replace('/^(' . $triggerPattern . '|kania|kira|kirana)[\s,.:!?\-]*/i', '', $message);

        // Jika hanya memanggil nama / salam singkat, balas dengan perkenalan langsung
        if (empty(trim($cleanQuestion)) || preg_match('/^(halo|hai|p|tes|test|siang|pagi|sore|malam|assalamualaikum|ass|askum|bot)$/i', trim($message))) {
            $greetingName = !empty($pushName) ? $pushName : 'Pak';
            $aiReply = "Halo {$greetingName}! Saya *KANIA*, Asisten AI resmi Citra Jaya Knitting (CJK).\n\nAda data atau laporan yang bisa saya bantu cek hari ini? Contoh perintah:\n• *Cek Stok:* `cek stok katun putih`\n• *Status SPK:* `status SPK WO rajut hari ini`\n• *Laporan Omset:* `berapa total omset penjualan bulan ini?`";
            return $this->response->setJSON([
                'status'   => 'success',
                'question' => $message,
                'reply'    => $aiReply
            ]);
        }

        // 3. Ambil konteks dokumen terakhir untuk nomor ini (jika ada)
        $cacheKey = 'kania_last_doc_' . $senderPhone;
        $lastDoc  = cache()->get($cacheKey);

        // Proses pertanyaan melalui AI Brain KANIA
        $aiReply = $this->brain->processQuestion($cleanQuestion, $lastDoc);

        // Update konteks jika ada kode transaksi dibahas
        if (preg_match('/\b(sod[0-9]+|wrd[0-9]+|prd[0-9]+|dev[0-9]+)\b/i', $cleanQuestion . ' ' . $aiReply, $mDoc)) {
            cache()->save($cacheKey, strtoupper($mDoc[1]), 3600);
        }

        return $this->response->setJSON([
            'status'   => 'success',
            'question' => $cleanQuestion,
            'reply'    => $aiReply
        ]);
    }

    /**
     * Kirim pesan balik ke service WhatsApp Gateway
     */
    protected function sendWhatsAppReply(string $to, string $message): bool
    {
        $url = rtrim($this->config->waGatewayUrl, '/') . '/send';

        $payload = [
            'to'      => $to,
            'message' => $message
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $res = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ($httpCode === 200);
    }

    /**
     * Halaman Scan QR Code WhatsApp KANIA
     */
    public function qr()
    {
        if ($this->request->getGet('reset') === '1') {
            @file_get_contents('http://127.0.0.1:3300/reset-qr');
            return redirect()->to('/assistant/qr');
        }

        $qrHtml = @file_get_contents('http://127.0.0.1:3300/qr');
        if (!$qrHtml) {
            return "<h3>⏳ Service gateway WhatsApp sedang menyiapkan koneksi... Mohon refresh beberapa detik lagi.</h3>";
        }
        return $this->response->setBody($qrHtml);
    }

    /**
     * Endpoint Pengujian Web Interaktif
     */
    public function testView()
    {
        return view('Modules\Assistant\Views\test_chat', [
            'title' => 'Test Chat KANIA - AI Executive Assistant'
        ]);
    }

    /**
     * Ajax Endpoint untuk Test Chat
     */
    public function testAsk()
    {
        $message = trim($this->request->getPost('message') ?? '');
        $sender  = trim($this->request->getPost('sender') ?? 'Pak Edwin');

        if (empty($message)) {
            return $this->response->setJSON([
                'status'  => false,
                'message' => 'Pesan tidak boleh kosong'
            ]);
        }

        // Bersihkan kata pemicu jika ada
        $trigger = preg_quote($this->config->triggerWord, '/');
        $cleanQuestion = preg_replace('/^' . $trigger . '[\s,.:!?\-]*/i', '', $message);

        $lastDoc = session()->get('kania_last_doc');
        $reply = $this->brain->processQuestion($cleanQuestion, $lastDoc);

        if (preg_match('/\b(sod[0-9]+|wrd[0-9]+|prd[0-9]+|dev[0-9]+)\b/i', $cleanQuestion . ' ' . $reply, $mDoc)) {
            session()->set('kania_last_doc', strtoupper($mDoc[1]));
        }

        return $this->response->setJSON([
            'status'   => true,
            'question' => $cleanQuestion,
            'reply'    => $reply
        ]);
    }

    /**
     * Status Service KANIA
     */
    public function status()
    {
        $waStatus = @file_get_contents('http://127.0.0.1:3300/status');

        return $this->response->setJSON([
            'assistant_name'  => $this->config->botName,
            'trigger_word'    => $this->config->triggerWord,
            'gemini_model'    => $this->config->geminiModel,
            'api_key_set'     => !empty($this->config->geminiApiKey) || !empty(getenv('GEMINI_API_KEY')),
            'whatsapp_gateway' => json_decode($waStatus, true) ?: 'offline',
            'server_time'     => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Halaman Web Pengaturan Asisten AI & WhatsApp Gateway (Khusus SuperAdmin di Modul Utilitas)
     */
    public function settings()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        } elseif (!$this->auth->isSuperAdmin() && !$this->auth->isAdmin()) {
            throw new \Exception('Akses Ditolak: Halaman pengaturan Asisten AI hanya dapat diakses oleh Superadmin.');
        }

        $this->data['titlehead'] = 'Pengaturan Asisten AI & WhatsApp Gateway';
        $this->data['config']    = $this->config;

        return view('Modules\Assistant\Views\settings', $this->data);
    }

    /**
     * Simpan Perubahan Pengaturan dari Web
     */
    public function saveSettings()
    {
        if (!$this->auth->loggedIn()) {
            return redirect()->to('/auth/login');
        } elseif (!$this->auth->isSuperAdmin() && !$this->auth->isAdmin()) {
            throw new \Exception('Akses Ditolak: Halaman pengaturan Asisten AI hanya dapat diakses oleh Superadmin.');
        }

        $botName        = trim($this->request->getPost('botName') ?? 'KANIA');
        $trigger        = trim($this->request->getPost('triggerWord') ?? 'Kania');
        $aiProvider     = trim($this->request->getPost('aiProvider') ?? 'groq');
        $groqModel      = trim($this->request->getPost('groqModel') ?? 'openai/gpt-oss-120b');
        $geminiModel    = trim($this->request->getPost('geminiModel') ?? 'gemini-3.8-flash');
        $enableFallback = (bool)$this->request->getPost('enableFallback');
        $allowAny       = (bool)$this->request->getPost('allowAnyNumber');
        $requireTrigger = (bool)$this->request->getPost('requireTriggerWord');

        $rawPhones = $this->request->getPost('allowedPhoneNumbers') ?? '';
        $phoneList = [];
        foreach (preg_split('/[\r\n,]+/', $rawPhones) as $p) {
            $cleaned = preg_replace('/[^0-9]/', '', trim($p));
            if (!empty($cleaned)) {
                $phoneList[] = $cleaned;
            }
        }

        // Tulis ulang ke file config Assistant.php
        $configFile = APPPATH . '../modules/Assistant/Config/Assistant.php';
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            
            // Replace botName
            $content = preg_replace("/public string \\\$botName = '[^']*';/", "public string \$botName = '{$botName}';", $content);
            // Replace triggerWord
            $content = preg_replace("/public string \\\$triggerWord = '[^']*';/", "public string \$triggerWord = '{$trigger}';", $content);
            // Replace aiProvider
            $content = preg_replace("/public string \\\$aiProvider = '[^']*';/", "public string \$aiProvider = '{$aiProvider}';", $content);
            // Replace groqModel
            $content = preg_replace("/public string \\\$groqModel = '[^']*';/", "public string \$groqModel = '{$groqModel}';", $content);
            // Replace geminiModel
            $content = preg_replace("/public string \\\$geminiModel = '[^']*';/", "public string \$geminiModel = '{$geminiModel}';", $content);
            // Replace enableFallback
            $boolFallback = $enableFallback ? 'true' : 'false';
            $content = preg_replace("/public bool \\\$enableFallback = (true|false);/", "public bool \$enableFallback = {$boolFallback};", $content);
            // Replace requireTriggerWord
            $boolTrigger = $requireTrigger ? 'true' : 'false';
            if (strpos($content, '$requireTriggerWord') !== false) {
                $content = preg_replace("/public bool \\\$requireTriggerWord = (true|false);/", "public bool \$requireTriggerWord = {$boolTrigger};", $content);
            } else {
                $content = preg_replace("/(public string \\\$triggerWord = '[^']*';)/", "$1\n    public bool \$requireTriggerWord = {$boolTrigger};", $content);
            }
            // Replace allowAny
            $boolStr = $allowAny ? 'true' : 'false';
            $content = preg_replace("/public bool \\\$allowAnyNumberIfEmptyWhitelist = (true|false);/", "public bool \$allowAnyNumberIfEmptyWhitelist = {$boolStr};", $content);
            
            // Replace phones
            $phonesExport = "[\n";
            foreach (array_unique($phoneList) as $num) {
                $phonesExport .= "        '{$num}',\n";
            }
            $phonesExport .= "    ];";
            $content = preg_replace("/public array \\\$allowedPhoneNumbers = \\[.*?\\];/s", "public array \$allowedPhoneNumbers = {$phonesExport}", $content);

            file_put_contents($configFile, $content);
        }

        return redirect()->to('/utilitas/asisten-ai')->with('success', 'Pengaturan Asisten AI & WhatsApp berhasil diperbarui!');
    }
}
