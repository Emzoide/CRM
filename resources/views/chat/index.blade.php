{{-- resources/views/chat/whatsapp.blade.php --}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp CRM</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --whatsapp-green: #128C7E;
            --whatsapp-light-green: #25D366;
            --whatsapp-teal: #075E54;
            --whatsapp-light-bg: #F0F2F5;
            --whatsapp-chat-bg: #E4DDD6;
            --whatsapp-outgoing: #DCF8C6;
            --whatsapp-incoming: #FFFFFF;
            --whatsapp-header: #EDEDED;
            --whatsapp-border: #D1D7DB;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            height: 100vh;
            margin: 0;
            font-family: 'Open Sans', sans-serif;
            background-color: var(--whatsapp-light-bg);
            color: #333;
        }

        /* Main container */
        .container {
            display: flex;
            width: 100%;
            height: 100%;
            max-width: 1400px;
            margin: 0 auto;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.2);
        }

        /* Sidebar styles */
        .sidebar {
            width: 30%;
            max-width: 420px;
            min-width: 300px;
            background-color: #FFFFFF;
            border-right: 1px solid var(--whatsapp-border);
            display: flex;
            flex-direction: column;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            background-color: var(--whatsapp-header);
            height: 60px;
        }

        .sidebar-header h2 {
            font-size: 16px;
            font-weight: 500;
            color: var(--whatsapp-teal);
        }

        .search-container {
            padding: 8px 12px;
            background-color: #FFFFFF;
        }

        .search-box {
            display: flex;
            align-items: center;
            background-color: var(--whatsapp-light-bg);
            border-radius: 18px;
            padding: 0 12px;
            height: 36px;
        }

        .search-box input {
            width: 100%;
            border: none;
            background: transparent;
            outline: none;
            padding: 8px;
            font-size: 14px;
        }

        .conversations {
            flex: 1;
            overflow-y: auto;
        }

        .conversations ul {
            list-style: none;
        }

        .conversation-item {
            display: flex;
            padding: 12px 16px;
            border-bottom: 1px solid var(--whatsapp-light-bg);
            cursor: pointer;
            transition: background-color 0.2s;
        }

        .conversation-item:hover {
            background-color: #F5F5F5;
        }

        .conversation-item.active {
            background-color: #EBEBEB;
        }

        .avatar {
            width: 49px;
            height: 49px;
            border-radius: 50%;
            background-color: #DFE5E7;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #919191;
            font-weight: bold;
            font-size: 18px;
        }

        .conversation-info {
            flex: 1;
            overflow: hidden;
        }

        .conversation-top {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .contact-name {
            font-weight: 500;
            font-size: 16px;
            color: #111B21;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .conversation-time {
            font-size: 12px;
            color: #667781;
            white-space: nowrap;
        }

        .conversation-preview {
            font-size: 14px;
            color: #667781;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-count {
            background-color: var(--whatsapp-light-green);
            color: white;
            border-radius: 50%;
            min-width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            margin-left: 5px;
        }

        /* Chat area styles */
        .chat {
            flex: 1;
            display: flex;
            flex-direction: column;
            background-color: var(--whatsapp-chat-bg);
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='100' height='100' viewBox='0 0 100 100'%3E%3Cg fill-rule='evenodd'%3E%3Cg fill='%23d5d5d5' fill-opacity='0.4'%3E%3Cpath opacity='.5' d='M96 95h4v1h-4v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4h-9v4h-1v-4H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15v-9H0v-1h15V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h9V0h1v15h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9h4v1h-4v9zm-1 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm9-10v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-10 0v-9h-9v9h9zm-9-10h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9zm10 0h9v-9h-9v9z'/%3E%3Cpath d='M6 5V0H5v5H0v1h5v94h1V6h94V5H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }

        .chat-header {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            background-color: var(--whatsapp-header);
            height: 60px;
            border-left: 1px solid var(--whatsapp-border);
        }

        .chat-contact-info {
            display: flex;
            align-items: center;
            flex: 1;
        }

        .chat-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #DFE5E7;
            margin-right: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #919191;
            font-weight: bold;
            font-size: 16px;
        }

        .chat-contact-name {
            font-weight: 500;
            font-size: 16px;
        }

        .messages-container {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }

        #noConv {
            margin: auto;
            color: #667781;
            font-size: 14px;
            text-align: center;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .message {
            max-width: 65%;
            margin-bottom: 8px;
            position: relative;
            display: flex;
            flex-direction: column;
        }

        .message.outgoing {
            align-self: flex-end;
        }

        .message.incoming {
            align-self: flex-start;
        }

        .message-content {
            padding: 8px 12px;
            border-radius: 7.5px;
            font-size: 14px;
            box-shadow: 0 1px 0.5px rgba(0, 0, 0, 0.13);
            position: relative;
        }

        .message.outgoing .message-content {
            background-color: var(--whatsapp-outgoing);
            border-top-right-radius: 0;
        }

        .message.incoming .message-content {
            background-color: var(--whatsapp-incoming);
            border-top-left-radius: 0;
        }

        .message.outgoing .message-content:before {
            content: "";
            position: absolute;
            top: 0;
            right: -8px;
            width: 0;
            height: 0;
            border-left: 8px solid var(--whatsapp-outgoing);
            border-top: 8px solid transparent;
        }

        .message.incoming .message-content:before {
            content: "";
            position: absolute;
            top: 0;
            left: -8px;
            width: 0;
            height: 0;
            border-right: 8px solid var(--whatsapp-incoming);
            border-top: 8px solid transparent;
        }

        .message-meta {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            margin-top: 2px;
            font-size: 11px;
            color: #667781;
        }

        .message-time {
            margin-right: 4px;
        }

        .message-status {
            display: inline-block;
            width: 16px;
            height: 11px;
            position: relative;
        }

        .message-status.sending:after {
            content: "✓";
            color: #A6A6A6;
        }

        .message-status.sent:after {
            content: "✓";
            color: #A6A6A6;
        }

        .message-status.delivered:after {
            content: "✓✓";
            color: #A6A6A6;
            font-size: 10px;
            letter-spacing: -2px;
        }

        .message-status.read:after {
            content: "✓✓";
            color: #53BDEB;
            font-size: 10px;
            letter-spacing: -2px;
        }

        .input-container {
            display: flex;
            align-items: center;
            padding: 10px 16px;
            background-color: var(--whatsapp-header);
            border-top: 1px solid var(--whatsapp-border);
        }

        .message-input {
            flex: 1;
            background-color: white;
            border-radius: 21px;
            padding: 9px 12px;
            border: 1px solid var(--whatsapp-border);
            outline: none;
            font-size: 15px;
            font-family: 'Open Sans', sans-serif;
        }

        .send-button {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--whatsapp-light-green);
            color: white;
            border: none;
            margin-left: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .send-button:disabled {
            background-color: #E9EDEF;
            cursor: default;
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #CCCCCC;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #AAAAAA;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h2>INSAC CRM</h2>
            </div>
            <div class="search-container">
                <div class="search-box">
                    <span>🔍</span>
                    <input type="text" placeholder="Buscar o empezar un nuevo chat">
                </div>
            </div>
            <div class="conversations">
                <ul id="conversations"></ul>
            </div>
        </div>
        <div class="chat">
            <div class="chat-header" id="chat-header" style="display: none;">
                <div class="chat-contact-info">
                    <div class="chat-avatar" id="chat-avatar"></div>
                    <div class="chat-contact-name" id="chat-contact-name"></div>
                </div>
            </div>
            <div class="messages-container" id="messages">
                <p id="noConv">Selecciona una conversación para comenzar a chatear</p>
            </div>
            <form id="inputBar" class="input-container" style="display:none;">
                <input id="msgInput" class="message-input" type="text" placeholder="Escribe un mensaje" autocomplete="off">
                <button type="submit" class="send-button" id="send-button" disabled>➤</button>
            </form>
        </div>
    </div>

    <script>
        const apiBase = '/api';
        let currentConvId = null;
        let currentContact = null;
        let conversations = []; // Variable global para almacenar las conversaciones

        document.addEventListener('DOMContentLoaded', () => {
            fetchConversations();

            // Habilitar/deshabilitar botón de enviar según contenido del input
            const msgInput = document.getElementById('msgInput');
            const sendButton = document.getElementById('send-button');

            msgInput.addEventListener('input', () => {
                sendButton.disabled = msgInput.value.trim() === '';
            });

            // Enviar mensaje
            document.getElementById('inputBar').addEventListener('submit', async e => {
                e.preventDefault();
                const text = msgInput.value.trim();
                if (!text || !currentConvId) return;

                // Mostrar inmediatamente
                appendMessage({
                    content: text,
                    from_me: true,
                    timestamp: new Date().toISOString(),
                    status: 'sending'
                });
                msgInput.value = '';
                sendButton.disabled = true;

                // Guardar y enviar a servidor
                let res = await fetch(`${apiBase}/conversations/${currentConvId}/messages`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        from_me: true,
                        message_id: Date.now().toString(),
                        message_type: 'text',
                        content: text,
                        conversation_id: currentConvId,
                        timestamp: new Date().toISOString()
                    })
                });
                if (res.status === 401) {
                    await pedirTokenYGuardar();
                }
                const msg = await res.json();
                if (!msg.success) {
                    if (res.status === 401) {
                        await pedirTokenYGuardar();
                    } else {
                        alert(msg.error || 'No se pudo enviar el mensaje.');
                    }
                    loadMessages(currentConvId);
                    return;
                }
                // Actualizar el último mensaje
                loadMessages(currentConvId);
                // Actualizar la lista de conversaciones para mostrar el último mensaje
                fetchConversations();
            });
        });

        async function fetchConversations() {
            const res = await fetch(`${apiBase}/conversations`);
            conversations = await res.json(); // Guardar las conversaciones en la variable global
            const ul = document.getElementById('conversations');
            ul.innerHTML = '';

            conversations.forEach(c => {
                const li = document.createElement('li');
                li.className = 'conversation-item';
                if (currentConvId === c.id) {
                    li.classList.add('active');
                }

                // Crear el avatar con la primera letra del número
                const avatar = document.createElement('div');
                avatar.className = 'avatar';
                avatar.textContent = c.contact.wa_id.charAt(0).toUpperCase();

                // Información de la conversación
                const info = document.createElement('div');
                info.className = 'conversation-info';

                // Parte superior: nombre y hora
                const top = document.createElement('div');
                top.className = 'conversation-top';

                const name = document.createElement('div');
                name.className = 'contact-name';

                // Mostrar nombre y número en el formato deseado
                if (c.contact.name) {
                    const nameSpan = document.createElement('span');
                    nameSpan.textContent = c.contact.name;
                    name.appendChild(nameSpan);

                    const phoneSpan = document.createElement('span');
                    phoneSpan.style.fontSize = '12px';
                    phoneSpan.style.color = '#667781';
                    phoneSpan.textContent = ` (${formatPhoneNumber(c.contact.wa_id)})`;
                    name.appendChild(phoneSpan);
                } else {
                    name.textContent = formatPhoneNumber(c.contact.wa_id);
                }

                const time = document.createElement('div');
                time.className = 'conversation-time';
                // Si hay mensajes, mostrar la hora del último
                if (c.last_message && c.last_message.timestamp) {
                    time.textContent = formatMessageTime(c.last_message.timestamp);
                }

                top.appendChild(name);
                top.appendChild(time);

                // Vista previa del mensaje
                const preview = document.createElement('div');
                preview.className = 'conversation-preview';
                if (c.last_message) {
                    preview.textContent = c.last_message.content || 'Mensaje multimedia';
                }

                info.appendChild(top);
                info.appendChild(preview);

                // Si hay mensajes no leídos, mostrar contador
                if (c.unread_count && c.unread_count > 0) {
                    const count = document.createElement('div');
                    count.className = 'message-count';
                    count.textContent = c.unread_count;
                    info.appendChild(count);
                }

                li.appendChild(avatar);
                li.appendChild(info);

                li.dataset.id = c.id;
                li.dataset.waId = c.contact.wa_id;
                li.addEventListener('click', () => selectConversation(c.id, c.contact.wa_id, li));
                ul.appendChild(li);
            });
        }

        function formatPhoneNumber(number) {
            // Formatear número de teléfono para mejor visualización
            if (number.length > 10) {
                return `+${number.slice(0, 2)} ${number.slice(2)}`;
            }
            return number;
        }

        function formatMessageTime(timestamp) {
            const date = new Date(timestamp);
            const now = new Date();
            const isToday = date.toDateString() === now.toDateString();

            if (isToday) {
                return date.toLocaleTimeString('es-PE', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: false
                });
            } else {
                return date.toLocaleDateString('es-PE', {
                    day: '2-digit',
                    month: '2-digit'
                });
            }
        }

        async function selectConversation(id, waId, element) {
            currentConvId = id;
            currentContact = waId;

            // Actualizar UI
            document.querySelectorAll('.conversation-item').forEach(li => li.classList.remove('active'));
            element.classList.add('active');

            document.getElementById('noConv').style.display = 'none';
            document.getElementById('inputBar').style.display = 'flex';
            document.getElementById('chat-header').style.display = 'flex';

            // Actualizar header del chat
            document.getElementById('chat-avatar').textContent = waId.charAt(0).toUpperCase();

            const contactName = document.getElementById('chat-contact-name');
            const conversation = conversations.find(c => c.id === id);
            if (conversation && conversation.contact.name) {
                contactName.innerHTML = `${conversation.contact.name}<br><span style="font-size: 12px; color: #667781;">${formatPhoneNumber(waId)}</span>`;
            } else {
                contactName.textContent = formatPhoneNumber(waId);
            }

            loadMessages(id);
        }

        async function loadMessages(convId) {
            const res = await fetch(`${apiBase}/conversations/${convId}/messages`);
            const msgs = await res.json();
            const container = document.getElementById('messages');
            container.innerHTML = '';

            // Ordenar mensajes por timestamp
            msgs.sort((a, b) => new Date(a.timestamp) - new Date(b.timestamp));

            // Agrupar mensajes por fecha
            let currentDate = null;

            msgs.forEach(m => {
                const msgDate = new Date(m.timestamp).toLocaleDateString('es-PE');

                // Si es un nuevo día, agregar separador de fecha
                if (msgDate !== currentDate) {
                    currentDate = msgDate;
                    const dateDiv = document.createElement('div');
                    dateDiv.style.textAlign = 'center';
                    dateDiv.style.margin = '10px 0';
                    dateDiv.style.position = 'relative';

                    const dateBadge = document.createElement('span');
                    dateBadge.style.backgroundColor = '#E1F2FB';
                    dateBadge.style.color = '#54656F';
                    dateBadge.style.fontSize = '12px';
                    dateBadge.style.padding = '5px 12px';
                    dateBadge.style.borderRadius = '8px';
                    dateBadge.style.boxShadow = '0 1px 0.5px rgba(0, 0, 0, 0.13)';

                    const today = new Date().toLocaleDateString('es-PE');
                    const yesterday = new Date();
                    yesterday.setDate(yesterday.getDate() - 1);
                    const yesterdayStr = yesterday.toLocaleDateString('es-PE');

                    if (msgDate === today) {
                        dateBadge.textContent = 'HOY';
                    } else if (msgDate === yesterdayStr) {
                        dateBadge.textContent = 'AYER';
                    } else {
                        dateBadge.textContent = formatDateHeader(m.timestamp);
                    }

                    dateDiv.appendChild(dateBadge);
                    container.appendChild(dateDiv);
                }

                appendMessage(m);
            });

            container.scrollTop = container.scrollHeight;
        }

        function formatDateHeader(timestamp) {
            const date = new Date(timestamp);
            return date.toLocaleDateString('es-PE', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });
        }

        function appendMessage(m) {
            const container = document.getElementById('messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = m.from_me ? 'message outgoing' : 'message incoming';

            const content = document.createElement('div');
            content.className = 'message-content';
            content.textContent = m.content;

            const meta = document.createElement('div');
            meta.className = 'message-meta';

            // Formatear hora
            const date = new Date(m.timestamp);
            const time = document.createElement('span');
            time.className = 'message-time';
            time.textContent = date.toLocaleTimeString('es-PE', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });

            meta.appendChild(time);

            // Agregar indicador de estado para mensajes enviados
            if (m.from_me) {
                const status = document.createElement('span');
                status.className = 'message-status';

                // Añadir clase según el estado del mensaje
                if (m.status === 'sending') {
                    status.classList.add('sending');
                } else if (m.status === 'sent') {
                    status.classList.add('sent');
                } else if (m.status === 'delivered') {
                    status.classList.add('delivered');
                } else if (m.status === 'read') {
                    status.classList.add('read');
                }

                meta.appendChild(status);
            }

            messageDiv.appendChild(content);
            messageDiv.appendChild(meta);
            container.appendChild(messageDiv);
        }

        function pedirTokenYGuardar() {
            const wurl = 'https://developers.facebook.com/apps/612510948474019/whatsapp-business/wa-dev-console/?business_id=481320065770698';

            // Abrir enlace en nueva ventana
            window.open(wurl, '_blank');

            // Pedir token
            const token = prompt('Token copiado. Pégalo aquí:');

            if (token) {
                return fetch('/api/whatsapp/token', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        token
                    })
                });
            }
            return Promise.resolve();
        }
    </script>
</body>

</html>