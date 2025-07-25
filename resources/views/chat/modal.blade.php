<style>
    .modal-body {
        background-image: url('{{ Storage::url('whatsapp-fundo.jpg') }}');
        height: 70vh;
        overflow-y: auto;
    }
    .msgbox-me {
        background-color: #005c4b;
        color: #f1f1f1;
    }
    .msgbox-other {
        background-color: #202c33;
        color: #f1f1f1;
    }
    #send-button {
        background-color: #005c4b
    }
    #send-button:hover {
        background-color: #05ad8e
    }
</style>


<div class="modal fade" id="modal-chat" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modal-chat-label" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-white">
                <h5 class="modal-title" id="modal-chat-label">CHAT</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer bg-secondary text-white">
                <div class="input-group">
                    <input type="text" id="message-input" class="form-control" placeholder="Digite sua mensagem...">
                    <button id="send-button" class="btn" title="Enviar">
                        <svg height="28px" viewBox="0 -960 960 960" width="24px" fill="#f2f2f2"><path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/></svg>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<button type="button" class="btn btn-secondary" style="position: fixed; left: 100px" data-bs-toggle="modal" data-bs-target="#modal-chat">
    Abrir Chat
</button>

<script>
    $(document).ready(() => {
        // Listen for messages
        window.Echo.channel('chat').listen('.chat.message', function(data) {
            if(data.user_id == {{Auth::user()->id}}){
                $('#chat-messages').append(`
                <div class="row my-3">
                    <div class="col-6"></div>
                    <div class="card msgbox-me p-2 col-6">
                        <div>${data.message}</div>
                        <div class="form-text text-white">${data.hora}</div>
                    </div>
                </div>
                `);
            }else{
                $('#chat-messages').append(`
                    <div class="row my-3">
                        <div class="card msgbox-other my-3 p-2 w-50 col-6">
                            <div>${data.message}</div>
                            <div class="form-text text-white">${data.hora}</div>
                        </div>
                        <div class="col-6"></div>
                    </div>
                `);
            }
            $('#chat-messages').scrollTop($('#chat-messages')[0].scrollHeight);
        });

        $('#message-input').on('keyup', (e) => {
            if($('#message-input').val().length > 0){
                $('#send-button').css('background-color', '#05ad8e');
            }else{
                $('#send-button').css('background-color', '#005c4b');
            }
        })

        // Handle send message
        $('#send-button').click(function() {
            sendMessage();
        });

        $('#message-input').on('keyup', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });

        function sendMessage() {
            const message = $('#message-input').val().trim();
            if (message.length > 0) {
                axios.post('chat-send', {
                    message,
                })
                .then((res) => {
                    $('#message-input').val('');
                })
                .catch(function(err) {
                    console.error('Erro ao enviar a mensagem axios'+err);
                });
            }
        }
    });
</script>