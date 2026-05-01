(function () {
  const chatbotUrl = new URL('../php/chatbot.php', document.currentScript.src).href;
  const html = `
    <button id="chatbot-bubble" aria-label="Open chatbot">?</button>
    <div id="chatbot-panel" role="dialog" aria-label="ArenaSync assistant">
      <div id="chatbot-header">ArenaSync Assistant</div>
      <div id="chatbot-messages"></div>
      <form id="chatbot-form">
        <input type="text" id="chatbot-input" placeholder="Ask about events, signups..." autocomplete="off" required>
        <button type="submit" id="chatbot-send">Send</button>
      </form>
    </div>
  `;
  document.body.insertAdjacentHTML('beforeend', html);

  const bubble   = document.getElementById('chatbot-bubble');
  const panel    = document.getElementById('chatbot-panel');
  const form     = document.getElementById('chatbot-form');
  const input    = document.getElementById('chatbot-input');
  const sendBtn  = document.getElementById('chatbot-send');
  const messages = document.getElementById('chatbot-messages');

  function addMessage(text, type) {
    const div = document.createElement('div');
    div.className = `chatbot-msg ${type}`;
    div.textContent = text;
    messages.appendChild(div);
    messages.scrollTop = messages.scrollHeight;
  }

  bubble.addEventListener('click', () => {
    panel.classList.toggle('open');
    if (panel.classList.contains('open') && messages.children.length === 0) {
      addMessage("Hi! I'm the ArenaSync assistant. How can I help?", 'bot');
    }
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const message = input.value.trim();
    if (!message) return;

    addMessage(message, 'user');
    input.value = '';
    sendBtn.disabled = true;

    try {
      const response = await fetch(chatbotUrl, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ message })
      });
      const data = await response.json();
      if (data.reply) {
        addMessage(data.reply, 'bot');
      } else {
        addMessage(data.error || 'Something went wrong.', 'error');
      }
    } catch (err) {
      addMessage('Could not reach the chatbot service.', 'error');
    } finally {
      sendBtn.disabled = false;
      input.focus();
    }
  });
})();
