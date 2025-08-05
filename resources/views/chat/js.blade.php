<script>
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
                Object.entries(item.msgs).forEach(([key, msgs]) => {
                //item.msgs.forEach(msgs => {
                    // tipos de msg 0 = boas vindas, 1 = bot, 2 = user, 3 = troca nome, 4 = texto, 5 = audio, 6 = imagem, 7 = Procurar Congr
                    switch (msgs.tipo) {
                        case 4:
                            if(msgs.conversa_id_to == id) {
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-end">
                                        <div class="col-auto msg-send">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <span class="msg-hora float-end">${new Date(msgs.created_at).toLocaleString('pt-BR', { hour: '2-digit', minute: '2-digit' })}</span>
                                        </div>
                                    </div>
                                `);
                            }else{
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-start">
                                        <div class="col-auto msg-receive">
                                            <div class="msg-text col-12">${msgs.msg.replace(/\n/g, '<br>')}</div>
                                            <span class="msg-hora float-end">${new Date(msgs.created_at).toLocaleString('pt-BR', { hour: '2-digit', minute: '2-digit' })}</span>
                                        </div>
                                    </div>
                                `);
                            }
                            break;
                        case 5:
                            if(msgs.conversa_id_to == id) {
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-end">
                                        <div class="col-auto msg-send">
                                            <div class="msg-text col-12">
                                                <audio class="audio-player" controls>
                                                    <source src="/storage/whatsapp/${msgs.link}?{{time()}}" type="audio/mpeg">
                                                    Audio indisponivel em seu navegador.
                                                </audio>
                                            </div>
                                            <span class="msg-hora float-end">${new Date(msgs.created_at).toLocaleString('pt-BR', { hour: '2-digit', minute: '2-digit' })}</span>
                                        </div>
                                    </div>
                                `);
                            }else{
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-start">
                                        <div class="col-auto msg-receive">
                                            <div class="msg-text col-12">
                                                <audio class="audio-player" controls>
                                                    <source src="/storage/whatsapp/${msgs.link}?{{time()}}" type="audio/mpeg">
                                                    Audio indisponivel em seu navegador.
                                                </audio>
                                            </div>
                                            <span class="msg-hora float-end">${new Date(msgs.created_at).toLocaleString('pt-BR', { hour: '2-digit', minute: '2-digit' })}</span>
                                        </div>
                                    </div>
                                `);
                            }
                            break;
                        case 6:
                            if(msgs.conversa_id_to == id) {
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-end">
                                        <div class="col-auto msg-send">
                                            <div class="msg-text col-12">
                                                <a href="storage/whatsapp/${msgs.link}" target="_blank"><img class="img-fluid" style="max-width: 25vw; max-height: 25vw" src="storage/whatsapp/700791209356909.jpeg" alt="teste"></a>
                                                <div class="msg-text col-12">${msgs.msg}</div>
                                            </div>
                                            <span class="msg-hora float-end">${new Date(msgs.created_at).toLocaleString('pt-BR', { hour: '2-digit', minute: '2-digit' })}</span>
                                        </div>
                                    </div>
                                `);
                            }else{
                                $('#lista-msgs').append(`
                                    <div class="m-3 row d-flex justify-content-start">
                                        <div class="col-auto msg-receive">
                                            <div class="msg-text col-12">
                                                <a href="storage/whatsapp/${msgs.link}" target="_blank"><img class="img-fluid" style="max-width: 25vw; max-height: 25vw" src="storage/whatsapp/1960125011428470.jpeg" alt="teste"></a>
                                                <div class="msg-text col-12">${msgs.msg}</div>
                                            </div>
                                            <span class="msg-hora float-end">${new Date(msgs.created_at).toLocaleString('pt-BR', { hour: '2-digit', minute: '2-digit' })}</span>
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

    $(document).ready(() => {
        getConversas()
        window.Echo.channel('chat').listen('.chat.message', (data) => {
            getConversas()
            if(id_conversa == data.message){
                getMsgs(id_conversa)
            }
        });
    });
</script>
