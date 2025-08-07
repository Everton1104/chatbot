<script>

    $(document).ready(() => {
        getConversas()
        window.Echo.channel('chat').listen('.chat.message', (data) => {
            getConversas()
            if(id_conversa == data.message){
                getMsgs(id_conversa)
            }
        });
    });

    $(document).on('keyup', (e) => {
        if(e.key == "Escape"){
            $('#msg-inicio').removeClass('d-none');
            $('.msg-footer').addClass('d-none');
            $('#img-perfil-header').addClass('d-none');
            $('#nome-header').text('Selecione uma conversa');
            $('#lista-msgs').html('');
            fecharConversa();
            id_conversa = 0
        }
    })

    conversas = true
    function fecharConversa()
    {
        if (window.innerWidth < 768){
            if(conversas){
                $('.conversas-container').css('display', 'block');
                $('.msgs-container').css('display', 'none');
                $('.conversas-container').css('width', '100vw');
                $('.conversas-container').css('transform', 'translateX(0vw)');
            }else{
                $('.conversas-container').css('display', 'none');
                $('.msgs-container').css('display', 'block');
                $('.conversas-container').css('width', '30vw');
                $('.conversas-container').css('transform', 'translateX(-100vw)');
            }
            conversas = !conversas
        }
    }

    let id_conversa = 0
    function getMsgs(id)
    {
        axios.post('loadMsgs', {id})
            .then((res) => {
                getConversas()
                item = res.data
                id_conversa = item.id
                fecharConversa();
                $('#lista-msgs').html('');
                $('#msg-inicio').addClass('d-none');
                $('.msg-footer').removeClass('d-none');
                $('#img-perfil-header').removeClass('d-none');
                setTimeout(() => {
                    $('.msgs-container').scrollTop($('.msgs-container')[0].scrollHeight);
                }, 250);
                if(item.numero){
                    $('#nome-header').text(item.nome + ' - (' + item.numero.substring(2, 4) + ') '+ item.numero.substring(4, 9) + '-' + item.numero.substring(9));
                }else{
                    $('#nome-header').text(item.nome);
                }

                // Um unico player de audio para reutilização em todas as mensagens
                $('#lista-msgs').append(`
                    <div id="global-audio-player" style="display:none">
                        <audio id="main-audio-player" controls>
                            Seu navegador não suporta o elemento de áudio.
                        </audio>
                    </div>
                `);
                const mainAudioPlayer = document.getElementById('main-audio-player');

                // Um unico player de video para reutilização em todas as mensagens
                $('#lista-msgs').append(`
                    <div id="global-video-player" style="display:none">
                        <video id="main-video-player" controls>
                            Seu navegador não suporta o elemento de video.
                        </video>
                    </div>
                `);
                const mainVideoPlayer = document.getElementById('main-video-player');

                Object.entries(item.msgs).forEach(([key, msgs]) => {
                //item.msgs.forEach(msgs => {
                    // tipos de msg 0 = boas vindas, 1 = bot, 2 = user, 3 = troca nome, 4 = texto, 5 = audio, 6 = imagem, 7 = video, 8 = documento, 10 = Procurar Congr
                    switch (msgs.tipo) {
                        case 4:
                            if(msgs.conversa_id_to == id) {
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-end">
                                        <div class="col-auto msg-send">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                            }else{
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-start">
                                        <div class="col-auto msg-receive">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                            }
                            break;
                        case 5: // audio
                            if(msgs.conversa_id_to == id) {
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-end">
                                        <div class="col-auto msg-send">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <div class="msg-text col-12">
                                                <button class="btn btn-sm btn-outline-primary play-audio-btn align-self-center my-3" 
                                                        data-src="/storage/whatsapp/${msgs.link}?{{time()}}">
                                                    Ouvir audio
                                                </button>
                                            </div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                                // Evernto para trazer o player global e colocar na msg
                                $(document).on('click', '.play-audio-btn', function() {
                                    const audioSrc = $(this).data('src');
                                    $('.play-audio-btn').removeClass('d-none');
                                    $(this).addClass('d-none');
                                    mainAudioPlayer.pause();
                                    mainAudioPlayer.currentTime = 0;
                                    mainAudioPlayer.src = audioSrc;
                                    $(this).parent().append($('#global-audio-player').show());
                                    setTimeout(() => {
                                        mainAudioPlayer.play().catch(e => {});
                                    }, 250);
                                });
                            }else{
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-start">
                                        <div class="col-auto msg-receive">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <div class="msg-text col-12">
                                                <button class="btn btn-sm btn-outline-primary play-audio-btn align-self-center my-3" 
                                                        data-src="/storage/whatsapp/${msgs.link}?{{time()}}">
                                                    Ouvir audio
                                                </button>
                                            </div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                                // Evernto para trazer o player global e colocar na msg
                                $(document).on('click', '.play-audio-btn', function() {
                                    const audioSrc = $(this).data('src');
                                    $('.play-audio-btn').removeClass('d-none');
                                    $(this).addClass('d-none');
                                    mainAudioPlayer.pause();
                                    mainAudioPlayer.currentTime = 0;
                                    mainAudioPlayer.src = audioSrc;
                                    $(this).parent().append($('#global-audio-player').show());
                                    setTimeout(() => {
                                        mainAudioPlayer.play().catch(e => {});
                                    }, 250);
                                });
                            }
                            break;
                        case 6:
                            if(msgs.conversa_id_to == id) {
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-end">
                                        <div class="col-auto msg-send">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <div class="msg-text col-12">
                                                <a href="storage/whatsapp/${msgs.link}" target="_blank"><img class="img-fluid" style="max-width: 25vw; max-height: 25vw" src="storage/whatsapp/${msgs.link}" alt="${msgs.msg}"></a>
                                                <div class="msg-text col-12">${msgs.msg}</div>
                                            </div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                            }else{
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-start">
                                        <div class="col-auto msg-receive">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <div class="msg-text col-12">
                                                <a href="storage/whatsapp/${msgs.link}" target="_blank"><img class="img-fluid" style="max-width: 25vw; max-height: 25vw" src="storage/whatsapp/${msgs.link}" alt="${msgs.msg}"></a>
                                                <div class="msg-text col-12">${msgs.msg}</div>
                                            </div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                            }
                            break;
                        case 7: //video
                            if(msgs.conversa_id_to == id) {
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-end">
                                        <div class="col-auto msg-send">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <div class="msg-text col-12">
                                                <button class="btn btn-sm btn-outline-primary play-video-btn my-3" 
                                                        data-src="/storage/whatsapp/${msgs.link}?{{time()}}">
                                                    Reproduzir video
                                                </button>
                                            </div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                                // Evernto para trazer o player global e colocar na msg
                                $(document).on('click', '.play-video-btn', function() {
                                    const videoSrc = $(this).data('src');
                                    $('.play-video-btn').removeClass('d-none');
                                    $(this).addClass('d-none');
                                    mainVideoPlayer.pause();
                                    mainVideoPlayer.currentTime = 0;
                                    mainVideoPlayer.src = videoSrc;
                                    $(this).parent().append($('#global-video-player').show());
                                    setTimeout(() => {
                                        mainVideoPlayer.play().catch(e => {});
                                    }, 250);
                                });
                            }else{
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-start">
                                        <div class="col-auto msg-receive">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <div class="msg-text col-12">
                                                <button class="btn btn-sm btn-outline-primary play-video-btn my-3" 
                                                        data-src="/storage/whatsapp/${msgs.link}?{{time()}}">
                                                    Reproduzir video
                                                </button>
                                            </div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                                // Evernto para trazer o player global e colocar na msg
                                $(document).on('click', '.play-video-btn', function() {
                                    const videoSrc = $(this).data('src');
                                    $('.play-video-btn').removeClass('d-none');
                                    $(this).addClass('d-none');
                                    mainVideoPlayer.pause();
                                    mainVideoPlayer.currentTime = 0;
                                    mainVideoPlayer.src = videoSrc;
                                    $(this).parent().append($('#global-video-player').show());
                                    setTimeout(() => {
                                        mainVideoPlayer.play().catch(e => {});
                                    }, 250);
                                });
                            }
                            break;
                        case 8:
                            if(msgs.conversa_id_to == id) {
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-end">
                                        <div class="col-auto msg-send">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <div class="msg-text col-12"><a href="{{url('/')}}/storage/whatsapp/${msgs.link}" target="_blank">Link para o arquivo</a></div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                            }else{
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-start">
                                        <div class="col-auto msg-receive">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <div class="msg-text col-12"><a href="{{url('/')}}/storage/whatsapp/${msgs.link}" target="_blank">Link para o arquivo</a></div>
                                            <span class="msg-hora float-end">${new Intl.DateTimeFormat('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(new Date(msgs.created_at))}</span>
                                        </div>
                                    </div>
                                `);
                            }
                            break;
                    }
        
                });
            })
            .catch((err) => {
                console.log('Erro axios ->'+err);
            });
    }

    function enviaMsg()
    {
        let msg = $('#msg-text').val();
        axios.post('enviaMsg', {msg, id: id_conversa})
            .then((res) => {
                $('#msg-text').val('');
            })
            .catch((err) => {
                console.log('Erro ao enviar a mensagem axios ->'+err);
            });
    }

    let searchTimeout;
    $('#pesquisar').on('keyup', (e) => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            pesquisar();
        }, 2000);
    });
    function pesquisar() {
        let busca = $('#pesquisar').val();
        axios.post('procurarConversa', {busca})
            .then((res) => {
                $('#lista-conversas').html('');
                res.data.forEach($conversa => {
                    $('#lista-conversas').append(`
                        <div class="my-3 conversa" onclick="getMsgs(${$conversa.id})">
                            <img class="img-perfil" src="storage/whatsapp/${ $conversa.foto ?? '0.jpg' }" alt="ft">
                            <span class="nome-perfil">
                                ${ $conversa.name??$conversa.nome }
                                <br>
                                ${ $conversa.numero ? '(' + $conversa.numero.substring(2,4) + ') ' + $conversa.numero.substring(4,9) + '-' + $conversa.numero.substring(9) : '' }
                            </span>
                        </div>
                    `);
                });
            })
            .catch((err) => {
                console.log('Erro axios ->'+err);
            });
    }

    function getConversas()
    {
        axios.post('getConversas')
            .then((res) => {
                $('#lista-conversas').html('');
                if(res.data.length > 0) {
                    res.data.forEach($conversa => {
                        $('#lista-conversas').append(`
                            <div class="my-3 conversa row" onclick="getMsgs(${$conversa.id})">
                                <div class="col-11 d-flex">
                                    <img class="img-perfil" src="storage/whatsapp/${ $conversa.foto ?? '0.jpg' }" alt="ft">
                                    <span class="nome-perfil">
                                        ${ $conversa.name??$conversa.nome }
                                        <br>
                                        ${ $conversa.numero ? '(' + $conversa.numero.substring(2,4) + ') ' + $conversa.numero.substring(4,9) + '-' + $conversa.numero.substring(9) : '' }
                                    </span>
                                </div>
                                <div class="col-1 badge pill bg-success ${ $conversa.nao_lidas==0?'d-none':'' }">
                                    ${ $conversa.nao_lidas }
                                </div>
                            </div>
                        `);
                    });
                }
            })
            .catch((err) => {
                console.log('Erro axios ->'+err);
            });
    }

    function enviarDoc()
    {
        const input = document.createElement('input');
        input.type = 'file';
        input.accept = '*';
        input.style.display = 'none';
        input.onchange = (e) => {
            const file = e.target.files[0];
            const formData = new FormData();
            formData.append('file', file);
            formData.append('id', id_conversa);
            axios.post('enviaArq', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .then((res) => {
                console.log(res.data);
            })
            .catch((err) => {
                console.log('Erro axios ->'+err);
            });
        };
        document.body.appendChild(input);
        input.click();
    }

    $('#msg-text').on('keyup', (e) => {
        if($('#msg-text').val() != '') {
            $('.btn-envia-text').removeClass('d-none');
            $('.btn-grava-audio').addClass('d-none');
            $('.btn-stop-audio').addClass('d-none');
            $('.btn-envia-audio').addClass('d-none');
            $('.audio-player-gravacao').addClass('d-none');
        } else {
            $('.btn-envia-text').addClass('d-none');
            $('.btn-grava-audio').removeClass('d-none');
        }
    });

    // Gravação de áudio
        let mediaRecorder;
        let audioChunks = [];
        const statusDiv = document.getElementById('status');
        
        // Elementos do DOM
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        const audioPlayback = document.getElementById('audioPlayback');

        // Iniciar gravação
        startBtn.addEventListener('click', async () => {
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                mediaRecorder = new MediaRecorder(stream);
                
                mediaRecorder.ondataavailable = (event) => {
                    audioChunks.push(event.data);
                };
                
                mediaRecorder.start(100); // Coletar dados a cada 100ms

                $('.btn-stop-audio').removeClass('d-none');
                $('.btn-anexo').prop('disabled', true);
                $('.btn-grava-audio').addClass('d-none');

                $('#msg-text').val('Gravando...');
                $('#msg-text').prop('disabled', true);

            } catch (error) {
                statusDiv.textContent = "Erro: " + error.message;
                console.error("Erro ao acessar microfone:", error);
            }
        });

        // Parar gravação
        stopBtn.addEventListener('click', async () => {
            mediaRecorder.stop();
            
            // Parar todas as trilhas do stream
            mediaRecorder.stream.getTracks().forEach(track => track.stop());
            
            // Esperar pelo evento 'onstop'
            mediaRecorder.onstop = async () => {
                let audioBlob = new Blob(audioChunks, { type: 'audio/webm' });
                audioChunks = [];

                audioPlayback.src = URL.createObjectURL(audioBlob);
                $('.audio-player-gravacao').removeClass('d-none');
                $('.btn-envia-audio').removeClass('d-none');
                $('#msg-text').addClass('d-none');
                $('.btn-stop-audio').addClass('d-none');
                $('.btn-cancelar-audio').removeClass('d-none');

                $('#msg-text').val('');
                $('#msg-text').prop('disabled', false);
            };
        });

        // Enviar gravação
        $('.btn-envia-audio').on('click', async function () {
            $('.btn-grava-audio').removeClass('d-none');
            $('#msg-text').removeClass('d-none');
            $('.btn-envia-audio').addClass('d-none');
            $('.audio-player-gravacao').addClass('d-none');
            $('.btn-cancelar-audio').addClass('d-none');
            $('.btn-anexo').prop('disabled', false);

            const formData = new FormData();
            const audioElement = document.querySelector('.audio-player-gravacao');
            const audioBlob = await fetch(audioElement.src).then(response => response.blob());
            const file = new File([audioBlob], 'gravacao.webm', { type: 'audio/webm' });
            
            formData.append('file', file);
            formData.append('id', id_conversa);

            axios.post('enviaArq', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
            .catch((err) => {
                console.log('Erro axios ->'+err);
            });
        })

        // Cancelar gravação
        $('.btn-cancelar-audio').on('click', () => {
            $('.btn-grava-audio').removeClass('d-none');
            $('#msg-text').removeClass('d-none');
            $('.btn-envia-audio').addClass('d-none');
            $('.audio-player-gravacao').addClass('d-none');
            $('.btn-cancelar-audio').addClass('d-none');
            $('.btn-anexo').prop('disabled', false);
        })
    // Gravação de áudio

</script>
