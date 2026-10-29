<?php

namespace Modules\Assistant\Config;

use CodeIgniter\Config\BaseConfig;

class Assistant extends BaseConfig
{
    /**
     * Nama Asisten
     */
    public string $botName = 'KANIA';

    /**
     * Kata Pemicu Pesan (Prefix Trigger)
     * Bot dapat merespon jika pesan diawali kata ini (case-insensitive)
     */
    public string $triggerWord = 'Kania';

    /**
     * Wajib Awali dengan Kata Pemicu (false = setiap chat masuk otomatis dijawab langsung tanpa perlu kata kunci)
     */
    public bool $requireTriggerWord = false;

    /**
     * Nomor WhatsApp Whitelist
     * Format: nomor internasional atau lokal tanpa tanda plus (misal '6285111470089', '085111470089')
     */
    public array $allowedPhoneNumbers = [
        '6285111470089',
        '085111470089',
        '6281322900089',
        '081322900089',
    ];

    /**
     * Izinkan semua nomor (true = semua nomor yang chat bebas dilayani untuk testing data)
     */
    public bool $allowAnyNumberIfEmptyWhitelist = true;

    /**
     * AI Provider Utama ('groq', 'openai_compatible', atau 'gemini')
     * Rekomendasi: 'groq' (100% Free, kuota ribuan request/hari, model Llama 3.3 70B super cepat)
     */
    public string $aiProvider = 'groq';

    /**
     * Base URL untuk OpenAI-Compatible Endpoint (Groq, OpenRouter, DeepSeek, Together, Ollama)
     */
    public string $openaiBaseUrl = 'https://api.groq.com/openai/v1';

    /**
     * Groq API Key (100% Free dari console.groq.com)
     * Bisa diatur di sini atau di file .env (GROQ_API_KEY)
     */
    public string $groqApiKey = '';

    /**
     * Model Utama (Groq Flagship 120B Reasoning Model / Qwen 3.8 27B / GPT-OSS 20B)
     * Model openai/gpt-oss-120b memiliki penalaran diagnostik mendalam dan native tool calling setara GPT-4o / Claude 3.5 Sonnet.
     */
    public string $groqModel = 'openai/gpt-oss-120b';

    /**
     * Otomatis beralih ke provider cadangan (Fallback) jika provider utama error/limit
     */
    public bool $enableFallback = true;

    /**
     * Google Gemini API Key (Cadangan / Multimodal Image)
     * Bisa diatur di sini atau di file .env (GEMINI_API_KEY)
     */
    public string $geminiApiKey = '';

    /**
     * Gemini Model Name (Cadangan)
     */
    public string $geminiModel = 'gemini-3.8-flash';

    /**
     * Temperature AI (0.1 - 0.2 untuk akurasi data analitis yang presisi)
     */
    public float $temperature = 0.15;

    /**
     * Max Tokens Output
     */
    public int $maxTokens = 1500;

    /**
     * URL WhatsApp Gateway (Node.js Service)
     */
    public string $waGatewayUrl = 'http://127.0.0.1:3300';

    /**
     * Webhook Secret Token (opsional untuk verifikasi request)
     */
    public string $webhookSecret = 'cjk_kania_secret_2026';
}

