import makeWASocket, { useMultiFileAuthState, DisconnectReason } from '@whiskeysockets/baileys';
import pino from 'pino';
import express from 'express';
import axios from 'axios';
import qrcode from 'qrcode';
import qrcodeTerminal from 'qrcode-terminal';
import path from 'path';
import fs from 'fs';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

const PORT = process.env.PORT || 3300;
const WEBHOOK_URL = process.env.WEBHOOK_URL || 'http://citraknitt.com/assistant/webhook';
const AUTH_FOLDER = path.join(__dirname, 'auth_session');

let sock = null;
let currentQR = null;
let isConnected = false;
let isStarting = false;

async function startWhatsAppBot() {
  if (isStarting) return;
  isStarting = true;

  try {
    if (!fs.existsSync(AUTH_FOLDER)) {
      fs.mkdirSync(AUTH_FOLDER, { recursive: true });
    }

    const { state, saveCreds } = await useMultiFileAuthState(AUTH_FOLDER);

    sock = makeWASocket({
      auth: state,
      logger: pino({ level: 'silent' }),
      printQRInTerminal: false,
      browser: ['KANIA Assistant', 'Chrome', '1.0.0']
    });

    sock.ev.on('creds.update', saveCreds);

    sock.ev.on('connection.update', async (update) => {
      const { connection, lastDisconnect, qr } = update;

      if (qr) {
        currentQR = qr;
        console.log('\n[KANIA WA GATEWAY] QR Code Baru Tersedia di http://citraknitt.com/assistant/qr\n');
        qrcodeTerminal.generate(qr, { small: true });
      }

      if (connection === 'close') {
        isConnected = false;
        currentQR = null;
        console.log('[KANIA WA GATEWAY] Koneksi close/reset. Menghubungkan ulang dalam 3 detik...');
        isStarting = false;
        setTimeout(startWhatsAppBot, 3000);
      } else if (connection === 'open') {
        isConnected = true;
        currentQR = null;
        isStarting = false;
        console.log('✅ [KANIA WA GATEWAY] Berhasil terhubung ke WhatsApp! KANIA siap standby 24 jam.');
      }
    });

    // Event: Menerima Pesan Masuk
    sock.ev.on('messages.upsert', async ({ messages, type }) => {
      for (const msg of messages) {
        const sender = msg.key.remoteJid;
        const fromMe = msg.key.fromMe;

        // Abaikan broadcast atau pesan keluar dari bot sendiri
        if (!sender || sender.includes('broadcast') || fromMe) continue;

        const text = msg.message?.conversation ||
                     msg.message?.extendedTextMessage?.text ||
                     msg.message?.imageMessage?.caption ||
                     '';

        console.log(`[EVENT] Upsert type: ${type}, fromMe: ${fromMe}, sender: ${sender}, text: "${text}"`);

        if (!text || text.trim() === '') continue;

        const pushName = msg.pushName || 'Dany';
        console.log(`\n📩 [PESAN MASUK] Dari: ${sender} (${pushName}) -> "${text}"`);

        // Teruskan pesan ke Backend CJK CodeIgniter 4 Webhook
        try {
          const resp = await axios.post(WEBHOOK_URL, {
            from: sender,
            message: text,
            pushName: pushName,
            timestamp: msg.messageTimestamp
          }, { timeout: 35000 });

          const aiReply = resp.data?.reply;
          console.log(`✅ Webhook status:`, resp.data?.status);

          if (aiReply) {
            // 1. Kirim balasan dengan quoted msg ke sender
            const res1 = await sock.sendMessage(sender, { text: aiReply }, { quoted: msg });
            console.log(`📤 [PESAN TERKIRIM (QUOTED)] Ke: ${sender}, status: ${res1?.status}`);

            // 2. Jika pengirim adalah @lid milik Dany, coba juga kirim ke nomor HP biasa jika ada
            if (sender.includes('@lid')) {
              try {
                const res2 = await sock.sendMessage('6281322900089@s.whatsapp.net', { text: aiReply });
                console.log(`📤 [DUAL-DELIVERY] Terkirim ke 6281322900089@s.whatsapp.net, status: ${res2?.status}`);
              } catch (errLid) {
                console.log(`⚠️ Dual delivery:`, errLid.message);
              }
            }
          }
        } catch (err) {
          console.error('❌ Gagal meneruskan pesan ke Webhook CJK:', err.message);
        }
      }
    });

    // Event: Monitor update status pengiriman pesan dari WhatsApp server
    sock.ev.on('messages.update', (updates) => {
      for (const u of updates) {
        console.log(`[STATUS UPDATE] remoteJid: ${u.key?.remoteJid}, id: ${u.key?.id}, status: ${u.update?.status}`);
      }
    });
  } catch (err) {
    console.error('❌ Gagal inisialisasi socket Baileys:', err);
    isStarting = false;
    setTimeout(startWhatsAppBot, 5000);
  }
}

// REST API: Kirim Pesan WhatsApp
app.post('/send', async (req, res) => {
  const { to, message } = req.body;

  if (!sock || !isConnected) {
    return res.status(503).json({ status: false, message: 'WhatsApp belum terkoneksi / scan QR dahulu.' });
  }

  if (!to || !message) {
    return res.status(400).json({ status: false, message: 'Field "to" dan "message" wajib diisi.' });
  }

  try {
    let targetJid = to;
    if (!targetJid.includes('@')) {
      targetJid = `${targetJid.replace(/[^0-9]/g, '')}@s.whatsapp.net`;
    }

    const sent = await sock.sendMessage(targetJid, { text: message });
    console.log(`📤 [PESAN TERKIRIM] Ke: ${targetJid}, status: ${sent?.status}`);

    return res.json({ status: true, message: 'Pesan berhasil dikirim.', msg_status: sent?.status });
  } catch (err) {
    console.error('❌ Gagal mengirim pesan WA:', err.message);
    return res.status(500).json({ status: false, message: err.message });
  }
});

// Endpoint untuk Reset Sesi & Generate QR Baru lewat Browser
app.get('/reset-qr', async (req, res) => {
  try {
    isConnected = false;
    currentQR = null;
    isStarting = false;

    if (sock) {
      try { sock.end(); } catch (e) {}
    }

    if (fs.existsSync(AUTH_FOLDER)) {
      fs.rmSync(AUTH_FOLDER, { recursive: true, force: true });
    }
    fs.mkdirSync(AUTH_FOLDER, { recursive: true });

    setTimeout(startWhatsAppBot, 1000);
    return res.redirect('/qr');
  } catch (e) {
    return res.status(500).send('Gagal reset QR: ' + e.message);
  }
});

// Web Route: Menampilkan QR Code di Browser
app.get('/qr', async (req, res) => {
  if (isConnected) {
    return res.send(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Status KANIA</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
          body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; text-align: center; padding: 40px 15px; background: #f0f2f5; margin: 0; }
          .card { max-width: 440px; margin: auto; background: #fff; padding: 35px 25px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
          .icon { font-size: 54px; margin-bottom: 12px; }
          h2 { color: #075e54; margin: 0 0 10px 0; }
          p { color: #555; font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
          .btn-reset { display: inline-block; padding: 8px 16px; font-size: 13px; color: #d9534f; border: 1px solid #d9534f; background: #fff; border-radius: 8px; text-decoration: none; font-weight: bold; }
          .btn-reset:hover { background: #fee2e2; }
        </style>
      </head>
      <body>
        <div class="card">
          <div class="icon">✅</div>
          <h2>WhatsApp KANIA Online!</h2>
          <p>Asisten AI KANIA sedang aktif 24 jam nonstop melayani permintaan data CJK melalui WhatsApp.</p>
          <a href="/assistant/qr?reset=1" onclick="return confirm('Apakah Anda ingin memutuskan koneksi WhatsApp dan generate QR baru?');" class="btn-reset">Tautkan Ulang Nomor Lain / Reset</a>
        </div>
      </body>
      </html>
    `);
  }

  if (!currentQR) {
    // Jika belum ada QR dan bot tidak running, panggil startWhatsAppBot
    if (!isStarting) {
      startWhatsAppBot();
    }

    return res.send(`
      <!DOCTYPE html>
      <html>
      <head>
        <title>Menyiapkan QR KANIA</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <style>
          body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; text-align: center; padding: 50px 15px; background: #f0f2f5; margin: 0; }
          .card { max-width: 420px; margin: auto; background: #fff; padding: 35px 20px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
          .spinner { border: 4px solid rgba(0,0,0,0.1); width: 42px; height: 42px; border-radius: 50%; border-left-color: #075e54; animation: spin 1s linear infinite; margin: 0 auto 15px auto; }
          @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
          h3 { color: #075e54; margin: 0 0 8px 0; }
          p { color: #666; font-size: 13px; margin: 0; }
        </style>
      </head>
      <body>
        <div class="card">
          <div class="spinner"></div>
          <h3>Sedang Menghubungkan ke WhatsApp...</h3>
          <p>QR Code sedang disiapkan. Halaman akan memuat ulang otomatis...</p>
        </div>
        <script>setTimeout(() => location.reload(), 2000);</script>
      </body>
      </html>
    `);
  }

  const qrImageUrl = await qrcode.toDataURL(currentQR);
  return res.send(`
    <!DOCTYPE html>
    <html>
    <head>
      <title>Tautkan WhatsApp KANIA</title>
      <meta name="viewport" content="width=device-width, initial-scale=1">
      <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; text-align: center; padding: 30px 15px; background: #f0f2f5; margin: 0; }
        .card { max-width: 420px; margin: auto; background: #fff; padding: 25px 20px; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h3 { color: #075e54; margin: 0 0 10px 0; font-size: 18px; }
        .instructions { font-size: 13px; color: #555; text-align: left; background: #f8fafc; padding: 12px 16px; border-radius: 10px; margin-bottom: 18px; line-height: 1.5; }
        .instructions ol { margin: 0; padding-left: 18px; }
        .qr-frame { background: #fff; display: inline-block; padding: 10px; border: 2px solid #e2e8f0; border-radius: 12px; }
        .qr-frame img { width: 250px; height: 250px; display: block; }
        .info { font-size: 12px; color: #888; margin-top: 14px; }
        .btn-reload { display: inline-block; margin-top: 12px; padding: 6px 14px; font-size: 12px; color: #075e54; border: 1px solid #075e54; border-radius: 6px; text-decoration: none; }
      </style>
    </head>
    <body>
      <div class="card">
        <h3>📲 Tautkan WhatsApp KANIA</h3>
        <div class="instructions">
          <ol>
            <li>Buka <strong>WhatsApp</strong> di HP Anda.</li>
            <li>Buka <strong>Menu Titik Tiga / Pengaturan</strong>.</li>
            <li>Pilih <strong>Perangkat Tertaut (Linked Devices)</strong>.</li>
            <li>Klik <strong>Tautkan Perangkat</strong> lalu scan QR di bawah:</li>
          </ol>
        </div>
        <div class="qr-frame">
          <img src="${qrImageUrl}" alt="Scan QR WhatsApp KANIA" />
        </div>
        <div class="info">QR Code refresh otomatis setiap beberapa detik.</div>
        <a href="/assistant/qr?reset=1" class="btn-reload">🔄 Generate Ulang QR Baru</a>
      </div>
      <script>setTimeout(() => location.reload(), 4000);</script>
    </body>
    </html>
  `);
});

// Status Service
app.get('/status', (req, res) => {
  res.json({
    status: isConnected ? 'online' : 'waiting_qr',
    connected: isConnected,
    has_qr: !!currentQR,
    port: PORT,
    webhook_url: WEBHOOK_URL
  });
});

// Start Service
app.listen(PORT, () => {
  console.log(`🚀 [KANIA WA GATEWAY] Service berjalan di port ${PORT}`);
  startWhatsAppBot();
});
