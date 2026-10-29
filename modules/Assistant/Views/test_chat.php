<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KANIA - Executive AI Assistant | Citra Jaya Knitting</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #e5ddd5;
      font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
      height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0;
    }
    .chat-container {
      width: 100%;
      max-width: 720px;
      height: 90vh;
      background: #fff;
      border-radius: 12px;
      display: flex;
      flex-direction: column;
      box-shadow: 0 4px 20px rgba(0,0,0,0.15);
      overflow: hidden;
    }
    .chat-header {
      background: #075e54;
      color: #fff;
      padding: 14px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    .chat-header .avatar {
      width: 44px;
      height: 44px;
      background: #128c7e;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
      margin-right: 12px;
    }
    .chat-body {
      flex: 1;
      padding: 20px;
      background: #efeae2;
      overflow-y: auto;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .message-bubble {
      max-width: 80%;
      padding: 10px 14px;
      border-radius: 8px;
      font-size: 14px;
      line-height: 1.5;
      white-space: pre-wrap;
      box-shadow: 0 1px 2px rgba(0,0,0,0.1);
    }
    .msg-user {
      background: #d9fdd3;
      align-self: flex-end;
      border-bottom-right-radius: 2px;
    }
    .msg-bot {
      background: #fff;
      align-self: flex-start;
      border-bottom-left-radius: 2px;
    }
    .msg-time {
      font-size: 11px;
      color: #888;
      text-align: right;
      margin-top: 4px;
    }
    .quick-chips {
      padding: 8px 16px;
      background: #f0f2f5;
      display: flex;
      gap: 8px;
      overflow-x: auto;
      border-top: 1px solid #ddd;
    }
    .chip-btn {
      font-size: 12px;
      padding: 4px 10px;
      border-radius: 14px;
      background: #fff;
      border: 1px solid #cbd5e1;
      color: #0369a1;
      cursor: pointer;
      white-space: nowrap;
    }
    .chip-btn:hover {
      background: #e0f2fe;
    }
    .chat-footer {
      padding: 12px 16px;
      background: #f0f2f5;
      display: flex;
      gap: 10px;
      align-items: center;
    }
  </style>
</head>
<body>

<div class="chat-container">
  <!-- Header -->
  <div class="chat-header">
    <div class="d-flex align-items-center">
      <div class="avatar"><i class="fas fa-robot text-white"></i></div>
      <div>
        <div class="fw-bold fs-6">KANIA (Asisten AI CJK)</div>
        <div style="font-size: 12px; opacity: 0.85;">Asisten Cerdas PT. Citra Jaya Knitting</div>
      </div>
    </div>
    <span class="badge bg-success"><i class="fas fa-circle me-1" style="font-size: 8px;"></i> Online</span>
  </div>

  <!-- Messages Body -->
  <div class="chat-body" id="chatBox">
    <div class="message-bubble msg-bot">
Halo! 🌸
Saya <strong>KANIA</strong>, Asisten AI Resmi PT. Citra Jaya Knitting.

Saya siap membantu pengecekan data riil sistem:
• 📦 <strong>Stok Bahan Baku & Benang</strong> per Gudang & Lot
• 📊 <strong>Ringkasan Laba Rugi</strong> & Finansial Terkini
• 🧵 <strong>Status SPK (Work Order)</strong>, Pemakaian Benang (BTK) & Hasil Jadi (BTM)
• ⚠️ <strong>Transaksi Pending Approval</strong>
• 🎯 <strong>Target Harian & Produktivitas Operator SDM</strong>

Ada yang bisa saya bantu carikan hari ini?
      <div class="msg-time">Sekarang</div>
    </div>
  </div>

  <!-- Quick Prompts -->
  <div class="quick-chips">
    <button class="chip-btn" onclick="sendQuick('Kania, cek stok benang di gudang')">📦 Cek Stok Benang</button>
    <button class="chip-btn" onclick="sendQuick('Kania, bagaimana posisi laba rugi bulan ini?')">📈 Laba Rugi Bulan Ini</button>
    <button class="chip-btn" onclick="sendQuick('Kania, cek progres SPK WRD260700021')">🧵 Progres SPK WRD260700021</button>
    <button class="chip-btn" onclick="sendQuick('Kania, apakah ada transaksi pending approval?')">⚠️ Pending Approval</button>
    <button class="chip-btn" onclick="sendQuick('Kania, rekap target harian operator SDM hari ini')">🎯 Target SDM Hari Ini</button>
  </div>

  <!-- Input Form -->
  <form id="chatForm" class="chat-footer">
    <input type="text" id="userInput" class="form-control" placeholder="Ketik pesan (contoh: Kania, cek stok benang...)" autocomplete="off">
    <button type="submit" class="btn btn-success px-4" id="btnSend">
      <i class="fas fa-paper-plane"></i>
    </button>
  </form>
</div>

<script>
  const chatBox = document.getElementById('chatBox');
  const chatForm = document.getElementById('chatForm');
  const userInput = document.getElementById('userInput');
  const btnSend = document.getElementById('btnSend');

  function appendMessage(text, isUser = false) {
    const bubble = document.createElement('div');
    bubble.className = `message-bubble ${isUser ? 'msg-user' : 'msg-bot'}`;
    bubble.innerHTML = text;
    
    const time = document.createElement('div');
    time.className = 'msg-time';
    const now = new Date();
    time.innerText = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
    bubble.appendChild(time);

    chatBox.appendChild(bubble);
    chatBox.scrollTop = chatBox.scrollHeight;
  }

  function sendQuick(text) {
    userInput.value = text;
    chatForm.dispatchEvent(new Event('submit'));
  }

  chatForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const msg = userInput.value.trim();
    if (!msg) return;

    appendMessage(msg, true);
    userInput.value = '';
    btnSend.disabled = true;

    // Loading indicator
    const loadingDiv = document.createElement('div');
    loadingDiv.className = 'message-bubble msg-bot';
    loadingDiv.id = 'loadingMsg';
    loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> <em>Kania sedang membaca database CJK...</em>';
    chatBox.appendChild(loadingDiv);
    chatBox.scrollTop = chatBox.scrollHeight;

    try {
      const formData = new FormData();
      formData.append('message', msg);

      const response = await fetch('/assistant/test-ask', {
        method: 'POST',
        body: formData
      });

      const data = await response.json();
      const loadEl = document.getElementById('loadingMsg');
      if (loadEl) loadEl.remove();

      if (data.status) {
        appendMessage(data.reply, false);
      } else {
        appendMessage('Maaf, terjadi kendala teknis: ' + (data.message || 'Gagal memproses.'), false);
      }
    } catch (err) {
      const loadEl = document.getElementById('loadingMsg');
      if (loadEl) loadEl.remove();
      appendMessage('Maaf, tidak dapat terhubung ke server asisten.', false);
    } finally {
      btnSend.disabled = false;
    }
  });
</script>

</body>
</html>
