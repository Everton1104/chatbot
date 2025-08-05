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
                <textarea type="text" style="height: 40px" id="msg-text" class="form-control me-2" placeholder="Digite sua mensagem..."></textarea>
                <button class="btn btn-success" onclick="enviaMsg()&&getMsgs(id_conversa)">
                    <svg height="28px" viewBox="0 -960 960 960" width="30px" fill="#0a0a0a"><path d="M120-160v-640l760 320-760 320Zm80-120 474-200-474-200v140l240 60-240 60v140Zm0 0v-400 400Z"/></svg>
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scriptEnd')
    @include('chat.js')
@endsection
