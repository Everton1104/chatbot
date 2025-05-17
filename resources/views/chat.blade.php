<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel Chat</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Chat Room</h5>
                    </div>
                    
                    <div class="card-body" id="chat-messages" style="height: 400px; overflow-y: auto;">
                        <!-- Messages will appear here -->
                    </div>
                    
                    <div class="card-footer">
                        <div class="input-group">
                            <input type="text" id="message-input" class="form-control" placeholder="Type your message...">
                            <button id="send-button" class="btn btn-primary">
                                <i class="bi bi-send"></i> Send
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <script>
        $(document).ready(() => {
            // Listen for messages
            window.Echo.channel('chat').listen('.chat.message', function(data) {
                $('#chat-messages').append('[' + data.hora + '] ' + data.nome + ': ' + data.message + '<br><br>');
            });

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
                if (message) {
                    axios.post('chat-send', {
                        message: message,
                    })
                    .then((res) => {
                        $('#message-input').val('');
                    })
                    .catch(function(err) {
                        console.error('Erro ao enviar a mensagem axios');
                    });
                }
            }
        });
    </script>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>