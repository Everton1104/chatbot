@extends('layouts.main.app')

@section('style')
    <style>
        /* Conversas */
        .conversas-container {
            height: 100vh;
            width: 30vw;
            margin-left: 10px;
            overflow-y: scroll;
        }
        .conversas-container::-webkit-scrollbar {
            width: 8px;
            background: #2d2d2d;
        }
        .conversas-container::-webkit-scrollbar-thumb {
            background-color: #05ad8e;
            border-radius: 10px;
        }
        .sub-conversas-container {
            margin: 15px;
            margin-top: 50px;
        }
        .conversa {
            cursor: pointer;
            height: 100px;
            border-radius: 6px;
            display: flex;
            align-items: center;
        }
        .conversa:hover{
            background-color: #505050;
        }
        .img-perfil {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            margin: 10px;
            object-fit: cover;
        }
        .nome-perfil {
            font-weight: bold;
            font-size: 20px;
        }
        .conversas-container-mobile {
            position: absolute;
            left: -50px;
            width: 50px;
            height: 100vh;
        }

        /* Mensagens */
        .msgs-container::-webkit-scrollbar {
            width: 8px;
            background: #2d2d2d;
        }
        .msgs-container::-webkit-scrollbar-thumb {
            background-color: #05ad8e;
            border-radius: 10px;
        }
        .msgs-container {
            height: 100vh;
            width: 70vw;
            margin-right: 10px;
            background-image: url('{{ Storage::url('whatsapp-fundo.jpg') }}');
            overflow-y: auto;
            overflow-x: hidden;
            padding-left: 15px;
            padding-right: 15px;
            padding-bottom: 70px;
        }
        .msg-hora {
            font-size: 12px;
        }
        .msg-send {
            background-color: #144d37;
            padding: 10px;
            border-radius: 6px;
            margin-left: 15px;
            position: relative;
            max-width: 60%;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        .msg-receive {
            background-color: #242626;
            padding: 10px;
            border-radius: 6px;
            position: relative;
            max-width: 60%;
            word-break: break-word;
            overflow-wrap: break-word;
        }
        .msg-header {
            background-color: #2d2d2d;
            position: absolute;
            left: 30vw;
            width: 70vw;
            z-index: 2;
        }
        .msg-footer {
            position: absolute;
            bottom: 15px;
            left: 30vw;
            width: 70vw;
            z-index: 2;
            padding-left: 15px;
            padding-right: 20px;
        }
        #fechar-conversa {
            display: none;
        }
        @media (max-width: 767px) {
            .conversas-container {
                transform: translateX(-100vw);
                display: none;
            }
            .msgs-container {
                width: 100vw;
                height: 95vh;
                padding-left: 10px;
                padding-right: 10px;
            }
            .msg-header {
                width: 100vw;
                padding-left: 45px;
                z-index: 2;
                left: 0px;
                position: fixed;
                cursor: pointer;
            }
            .msg-footer {
                width: 100vw;
                left: 0px;
                right: 0px;
                padding-left: 10px;
                padding-right: 20px;
            }
            #fechar-conversa {
                display: block;
            }
        }
        .audio-player {
            max-width: 100%;
        }
        .video-player {
            max-width: 100%;
        }
        /* orelha balao */
        .msg-send::before {
            content: "";
            position: absolute;
            top: 0;
            right: -15px;
            width: 0;
            height: 0;
            border-top: 15px solid #144d37;
            border-left: 15px solid transparent;
            border-right: 15px solid transparent;
        }
        .msg-receive::before {
            content: "";
            position: absolute;
            top: 0;
            left: -15px;
            width: 0;
            height: 0;
            border-top: 15px solid #242626;
            border-left: 15px solid transparent;
            border-right: 15px solid transparent;
        }
    </style>   
@endsection

@section('main')
    <div class="d-flex">
        <div class="conversas-container">
            <div class="sub-conversas-container">
                <svg id="fechar-conversa" onclick="fecharConversa()" height="3rem" viewBox="0 -960 960 960" width="2rem" fill="#d2d2d2"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg>
                <input type="text" id="pesquisar" class="form-control" placeholder="Pesquisar...">
                <div id="lista-conversas">
                    <div class="my-3 conversa">
                        <img class="img-perfil" src="storage/whatsapp/0.jpg" alt="ft">
                        <span class="nome-perfil">
                            .
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="msgs-container">
            <div class="msg-header" onclick="fecharConversa()">
                <div class="d-flex align-items-center">
                    <img id="img-perfil-header" class="img-perfil d-none" src="storage/whatsapp/0.jpg" alt="ft">
                    <h2 id="nome-header">Selecione uma conversa</h2>
                </div>
            </div>
            <div style="height: 100px;"></div>
            {{-- mensagens --}}
            <div id="msg-inicio" class="justify-content-center">
                <h1 class="text-center">Bem vindo ao Chat</h1>
                <h2 class="text-center">{{ Auth::user()->name }}</h2>
            </div>
                <div id="lista-msgs">
            </div>
            <div class="msg-footer d-none d-flex">
                {{-- Anexo --}}
                <button class="btn me-2 btn-anexo" onclick="enviarDoc()">
                    <svg height="28px" viewBox="0 -960 960 960" width="30px" fill="#ffffff"><path d="M720-330q0 104-73 177T470-80q-104 0-177-73t-73-177v-370q0-75 52.5-127.5T400-880q75 0 127.5 52.5T580-700v350q0 46-32 78t-78 32q-46 0-78-32t-32-78v-370h80v370q0 13 8.5 21.5T470-320q13 0 21.5-8.5T500-350v-350q-1-42-29.5-71T400-800q-42 0-71 29t-29 71v370q-1 71 49 120.5T470-160q70 0 119-49.5T640-330v-390h80v390Z"/></svg>
                </button>
                {{-- btn-cancelar-audio --}}
                <button class="btn me-2 btn-danger btn-cancelar-audio d-none">
                    <svg height="28px" viewBox="0 -960 960 960" width="30px" fill="#0a0a0a"><path d="m256-200-56-56 224-224-224-224 56-56 224 224 224-224 56 56-224 224 224 224-56 56-224-224-224 224Z"/></svg>
                </button>
                {{-- Textarea Msg --}}
                <textarea type="text" style="height: 40px" id="msg-text" class="form-control" placeholder="Digite sua mensagem..."></textarea>
                {{-- Audio Player --}}
                <audio id="audioPlayback" controls class="audio-player-gravacao d-none"></audio> 
                {{-- btn-enviar-txt --}}
                <button class="btn btn-success ms-2 btn-envia-text d-none" onclick="enviaMsg()&&getMsgs(id_conversa)">
                    <svg height="28px" viewBox="0 -960 960 960" width="30px" fill="#0a0a0a"><path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/></svg>
                </button>
                {{-- btn-grava-audio --}}
                <button class="btn btn-success ms-2 btn-grava-audio" id="startBtn">
                    <svg height="28px" viewBox="0 -960 960 960" width="30px" fill="#0a0a0a"><path d="M480-400q-50 0-85-35t-35-85v-240q0-50 35-85t85-35q50 0 85 35t35 85v240q0 50-35 85t-85 35Zm0-240Zm-40 520v-123q-104-14-172-93t-68-184h80q0 83 58.5 141.5T480-320q83 0 141.5-58.5T680-520h80q0 105-68 184t-172 93v123h-80Zm40-360q17 0 28.5-11.5T520-520v-240q0-17-11.5-28.5T480-800q-17 0-28.5 11.5T440-760v240q0 17 11.5 28.5T480-480Z"/></svg>
                </button>
                {{-- btn-stop-audio --}}
                <button class="btn btn-success ms-2 btn-stop-audio d-none" id="stopBtn">
                    <svg height="28px" viewBox="0 -960 960 960" width="30px" fill="#0a0a0a"><path d="M320-640v320-320Zm-80 400v-480h480v480H240Zm80-80h320v-320H320v320Z"/></svg>
                </button>
                {{-- btn-envia-audio --}}
                <button class="btn btn-success ms-2 btn-envia-audio d-none">
                    <svg height="28px" viewBox="0 -960 960 960" width="30px" fill="#0a0a0a"><path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/></svg>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scriptEnd')
    @include('chat.js')
@endsection
