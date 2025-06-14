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
    <style>
        .body-dark {
            color: #f2f2f2;
            background-color: #2d2d2d;
        }
        .card-body {
            background-color: #5d5d5d;
            height: 70vh;
            overflow-y: auto;
        }
        .msgbox {
            background-color: #f5f5f5;
            color: #1d1d1d;
        }
    </style>
</head>
<body class="body-dark">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0">CHAT</h5>
                    </div>
                    
                    <div class="card-body" id="chat-messages">
                    </div>
                    
                    <div class="card-footer bg-secondary text-white">
                        <div class="input-group">
                            <input type="text" id="message-input" class="form-control" placeholder="Digite sua mensagem...">
                            <button id="send-button" class="btn btn-primary">
                                <svg height="28px" viewBox="0 -960 960 960" width="24px" fill="#f2f2f2"><path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/></svg>
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
                $('#chat-messages').append(`
                    <div class="card msgbox my-3 p-1">
                        <div>${data.message}</div>
                        <div class="form-text">${data.hora}</div>
                    </div>
                `);
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
                        console.error('Erro ao enviar a mensagem axios'+err);
                    });
                }
            }
        });
    </script>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>