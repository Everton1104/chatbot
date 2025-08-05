<?php

namespace App\Http\Controllers;

use App\Events\ChatEnviaMensagem;
use App\Models\CongrsModel;
use App\Models\ConversasModel;
use App\Models\MensagensModel;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WhatsappController extends Controller
{
    // tipos de msg 0 = boas vindas, 1 = bot, 2 = user, 3 = troca nome, 4 = texto, 5 = audio, 6 = imagem, 7 = Procurar Congr
    // status de conversas 0 = padrao, 1 = dep ti
    public function show()
    {
        $request = Request::capture();
        $verifyToken = env('WEBHOOK_VERIFY_TOKEN');//senha de verificação pessoal
        $challenge = $request['hub_challenge'];
        $token = $request['hub_verify_token'];
        if ($token === $verifyToken) {
            return response($challenge, 200);
        }
        return response('Token de verificação inválido', 403);
    }

    public function index(Request $request)
    {
        $business_phone_number_id = $request['entry'][0]['changes'][0]['value']['metadata']['phone_number_id']??0;
        $nome = $request['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name']??'';
        $msg = $request['entry'][0]['changes'][0]['value']['messages'][0]??'';
        $wa_id = $request['entry'][0]['changes'][0]['value']['contacts'][0]['wa_id']??'';
        $msgTxt = $msg['text']['body']??'';
        $msgSimNao = $msg['interactive']['button_reply']['id']??null;
        $msgLista = $msg['interactive']['list_reply']['id']??null;
        $number = $msg['from']??0;

        
        try {
            $conversa = ConversasModel::where('numero','=',$number)->first();
            if(!$conversa){ // Nova conversa
                if($number != 0){
                    $link = '0.jpg';
                    $linkFotoPerfil = $this->getProfilePicture($wa_id);
                    if ($linkFotoPerfil) {
                        try {
                            // Baixa a imagem e salva no storage
                            $contents = file_get_contents($linkFotoPerfil);
                            Storage::put("whatsapp/perfil-".$number.".jpg", $contents);
                            $link = "perfil-".$number.".jpg"; // Atualiza o link
                        } catch (\Exception $e) {}
                    }
                    $conversa = ConversasModel::create([
                        'numero' => $number,
                        'nome' => $nome,
                        'foto' => $link,
                    ]);
                    //msg de boas vindas
                    $msgBoasVindas = "Olá, bem vindo ao chatbot do Evtu, antes de começar, seu nome é ".$nome."?";
                    $this->enviarMsgSimNao($business_phone_number_id, $number,$msgBoasVindas,'NomeCorreto');
                    MensagensModel::create([
                        'conversa_id_to' => $conversa->id, 
                        'conversa_id_from' => 0, // bot
                        'msg' => 'Msg de boas vindas',
                        'tipo' => 0, // boas vindas
                    ]);
                }
            }else{ // Tratamento das mensagens de texto
                $ultimaMsg = MensagensModel::where('conversa_id_to','=',$conversa->id)->orderBy('created_at', 'desc')->first();
                switch ($ultimaMsg->tipo) {
                    case '0':
                            if($msgSimNao == 'simNomeCorreto'){ // se o nome esta correto.
                            $msg = "Certo ".$conversa->nome.", vamos começar.";
                            $this->enviarMsg($business_phone_number_id, $number,$msg);
                            MensagensModel::create([
                                'conversa_id_to' => $conversa->id, 
                                'conversa_id_from' => 0, 
                                'msg' => 'Nome confirmado',
                                'tipo' => 1, // bot
                            ]);
                        }else{
                            $msg = "Qual é seu nome?";
                            $this->enviarMsg($business_phone_number_id, $number,$msg);
                            MensagensModel::create([
                                'conversa_id_to' => $conversa->id, 
                                'conversa_id_from' => 0,
                                'msg' => 'Alterar nome',
                                'tipo' => 3, // alterar nome
                            ]);
                            return response()->json([], 200);
                        }
                        break;
                    case '3':
                        $conversa->update(['nome' => $msgTxt]);
                        $msg = "Certo ".$conversa->nome.", vamos começar.";
                        $this->enviarMsg($business_phone_number_id, $number,$msg);
                        MensagensModel::create([
                            'conversa_id_to' => $conversa->id, 
                            'conversa_id_from' => 0,
                            'msg' => 'Nome alterado',
                            'tipo' => 1, // bot
                        ]);
                        break;
                    case '7':
                        $congrs = CongrsModel::where([['id','!=','0'],['id','!=','55'],['situacao','1'],['descCongr','like','%'.$msgTxt.'%']])->take(9)->get();
                        $lista =  [];
                        foreach ($congrs as $congr) {
                            $lista[] = [
                                'id' => 'congr'.$congr->id,
                                'title' => $congr->descCongr,
                            ];
                        }
                        $lista[] = [
                            'id' => 'congr0',
                            'title' => 'Nenhuma das opções',
                        ];
                        $this->enviarMsgLista($business_phone_number_id, $number, 'Escolha uma das congregações encontradas:', $lista);
                        MensagensModel::create([
                            'conversa_id_to' => $conversa->id, 
                            'conversa_id_from' => 0,
                            'msg' => 'Congregações encontradas',
                            'tipo' => 1, // bot
                        ]);
                        return response()->json([], 200);
                        break;
                }
                $this->rotaPadrao($request);
            }
            return response()->json([], 200);
        } catch (\Throwable $th) { // caso ocorra algum erro evita que a api fique reenviando as mensagens
            $this->enviarMsg($business_phone_number_id, $number, 'Ocorreu um erro, tente novamente mais tarde.');
            $this->enviarMsg($business_phone_number_id, $number, 'Erro: '.$th->getMessage());
            return response()->json([], 200);
        }
    }

    public function rotaPadrao(Request $request) {
        $business_phone_number_id = $request['entry'][0]['changes'][0]['value']['metadata']['phone_number_id']??0;
        $nome = $request['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name']??'';
        $msg = $request['entry'][0]['changes'][0]['value']['messages'][0]??'';
        $msgTxt = $msg['text']['body']??'';
        $msgSimNao = $msg['interactive']['button_reply']['id']??null;
        $msgLista = $msg['interactive']['list_reply']['id']??null;
        $number = $msg['from']??0;
        $conversa = ConversasModel::where('numero','=',$number)->first();
        $msgs = MensagensModel::where('conversa_id_to','=',$conversa->id)->orderBy('created_at', 'desc')->take(10)->get();

        switch ($conversa->status) {
            case 0:
                // Respostas de botoes sim ou nao
                if(!empty($msgSimNao)){
                    if($msgSimNao != 'simNomeCorreto'){// pula a primeira mensagem de boas vindas
                        switch ($msgSimNao) {
                            case 'simIniciarConversaTI':
                                $msg = "iniciando Conversa com dep TI, em breve um de nossos técnicos entrará em contato.";
                                $this->enviarMsg($business_phone_number_id, $number,$msg);
                                ConversasModel::find($conversa->id)->update(['status' => 1]);
                                MensagensModel::create([
                                    'conversa_id_to' => 2, 
                                    'conversa_id_from' => $conversa->id,
                                    'msg' => 'Nova solicitação para o departamento de TI',
                                    'tipo' => 4,
                                ]);
                                ChatEnviaMensagem::dispatch($conversa->id);
                                break;
                        }
                        return;    
                    }
                }

                // Respostas de listas
                if(!empty($msgLista)){
                    switch ($msgLista) {
                        // Troca do nome
                            case 'alterarNome':
                                $msg = "Qual é seu nome?";
                                $this->enviarMsg($business_phone_number_id, $number,$msg);
                                MensagensModel::create([
                                    'conversa_id_to' => $conversa->id, 
                                    'conversa_id_from' => 0,
                                    'msg' => $msg,
                                    'tipo' => 3, // alterar nome
                                ]);
                                break;
                        // Troca do nome

                        //Solicitações
                            case 'solicitacoes':
                                $lista =  [
                                    [
                                        'id' => 'ti',
                                        'title' => 'Departamento de TI',
                                        'description' =>'Som e mídia'
                                    ],
                                    [
                                        'id' => 'compras',
                                        'title' => 'Departamento de Compras',
                                        'description' =>'Produtos de limpesa, etc.'
                                    ],
                                ];
                                $this->enviarMsgLista($business_phone_number_id, $number, 'Escolha um departamento:', $lista);
                                break;
                                //TI
                                case 'ti':
                                    $msg = "Deseja iniciar uma conversa com o departamento de TI?";
                                    $this->enviarMsgSimNao($business_phone_number_id, $number, $msg, 'IniciarConversaTI');
                                    MensagensModel::create([
                                        'conversa_id_to' => $conversa->id, 
                                        'conversa_id_from' => 0,
                                        'msg' => 'Iniciar conversa com TI?',
                                        'tipo' => 1,
                                    ]);
                                    break;
                                    // $msg = "Qual é a sua congregação?";
                                    // $this->enviarMsg($business_phone_number_id, $number,$msg);
                                    // MensagensModel::create([
                                    //     'conversa_id_to' => $conversa->id, 
                                    //     'conversa_id_from' => 0,
                                    //     'msg' => 'Procurar congregação...',
                                    //     'tipo' => 7, // congr
                                    // ]);
                                    // break;
                        //Solicitações
                    }
                    return;
                }else{
                    // MENU INICIAL
                    $lista =  [
                        [
                            'id' => 'solicitacoes',
                            'title' => 'Solicitações',
                            'description' =>'Solicitações para departamentos da igreja.'
                        ],
                        [
                            'id' => 'alterarNome',
                            'title' => 'Alterar meu nome',
                            'description' =>'Nome atual: ' . $conversa->nome
                        ],
                    ];
                    $this->enviarMsgLista($business_phone_number_id, $number, 'Escolha uma opção:', $lista);
                }
                break;
            case 1: // TI
                MensagensModel::create([
                    'msg' => $msgTxt,
                    'conversa_id_from' => $conversa->id,
                    'conversa_id_to' => 2,
                    'tipo' => 4
                ]);
                ChatEnviaMensagem::dispatch($conversa->id);
                break;
        }
        return;
    }

    public function enviarMsgGemini($body) {
        // // Body
        // $body = '{"contents": [';
        // $msgs = MensagensModel::where('numero_id','=',$conversa->id)->where('created_at','>=',now()->subHours(1))->get();// pega as mensagens da ultimas duas horas da data atual
        // // Body
        // $body .= '{"role": "user", "parts": [{"text": "';

        // $body .= '[Instruções]';
        // $body .= 'Você é um assistente virtual que traduz palavras do ingles para o português ou do japones para o português, o cliente vai mandar as palavras ou perguntas voce deve sempre fornecer uma resposta de um dicionario com pelo menos um exemplo';
        // $body .= ' e sinonimos da palavra em ingles/japones, pode utilizar ideogramas;';
        // $body .= 'Responda somente sobre o assunto;';
        // $body .= 'Utilizar marcações de texto compativeis com whatsapp;';
        // $body .= 'Se não tiverem mensagens anteriores, responda com uma mensagem de boas vindas e explique que voce vai ajudar com dicionarios;';
        // $body .= 'Sempre utilizar o Idioma Portugês do Brasil;';
        // $body .= 'O nome do cliente é: '. $conversa->nome.';';
        // $body .= 'O formato da data é DD/MM/YYYY HH:II:SS;';
        // $body .= '[/Instruções]';
        // $body .= '[Contexto]';
        // $body .= '[Mensagens]';
        // foreach ($msgs as $msg) {
        //     if($msg->tipo == 1){
        //         $body .= ' [' . date('d/m/Y H:i:s', strtotime($msg->created_at)).'] Cliente ' . str_replace(array('"', "'"), '', $msg->msg);
        //     }else{
        //         $body .= ' [' . date('d/m/Y H:i:s', strtotime($msg->created_at)).'] Modelo ' . str_replace(array('"', "'"), '', $msg->msg);
        //     }
        // }
        // $body .= '[/Mensagens]';
        // $body .= '[/Contexto]';
        // $body .= 'Mensagem atual -> ' . $msgTxt . ';"}]},';

        // // Body
        // $body .= '],}';
        // // Body
        // MensagensModel::create(['numero_id' => $conversa->id, 'msg' => $msgTxt, 'tipo' => 1]);
        // $resposta = $this->enviarMsgGemini($body);
        // MensagensModel::create(['numero_id' => $conversa->id, 'msg' => $resposta, 'tipo' => 2]);
        // $this->enviarMsg($business_phone_number_id, $number, $resposta);

        try {
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=".env("API_GEMINI"), ['body' => $body]);
            return json_decode($response->getBody()->getContents(),true)['candidates'][0]['content']['parts'][0]['text'];
        } catch (\Throwable $th) {
            return 'Estou com problemas para reponder, aguarde alguns instantes e me pergunte novamente.'.$th->getMessage();
        }
    }

    public static function enviarMsg($business_phone_number_id, $numero, $msg) {
        $client = new \GuzzleHttp\Client();
        $client->request('POST', "https://graph.facebook.com/v23.0/".$business_phone_number_id."/messages", [
            'headers' => [
                'Authorization' => "Bearer " . env('GRAPH_API_TOKEN')
            ],
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $numero,
                'text' => [
                    'body' => $msg
                ],
            ]
        ]);
    }

    public static function enviarMsgSimNao($business_phone_number_id, $numero, $msg, $id = 0) {
        $client = new \GuzzleHttp\Client();
        $client->request('POST', "https://graph.facebook.com/v23.0/".$business_phone_number_id."/messages", [
            'headers' => [
                'Authorization' => "Bearer " . env('GRAPH_API_TOKEN')
            ],
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $numero,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => ['text' => $msg],
                    'action' => [
                        'buttons' => [
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'sim'.$id,
                                    'title' => 'Sim'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'nao'.$id,
                                    'title' => 'Não'
                                ]
                            ]
                        ]
                    ]
                ],
            ]
        ]);
    }

    public static function enviarMsgLista($business_phone_number_id, $numero, $msg, $lista = []) {
        // MODELO DE LISTA $lista
        // [
        //     [
        //         'id' => 'sim',
        //         'title' => 'Sim',
        //         'description' =>'Confirmo esta opção'
        //     ],
        // ]
        $client = new \GuzzleHttp\Client();
        $client->request('POST', "https://graph.facebook.com/v23.0/".$business_phone_number_id."/messages", [
            'headers' => [
                'Authorization' => "Bearer " . env('GRAPH_API_TOKEN')
            ],
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $numero,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'body' => ['text' => $msg],
                    'action' => [
                        'button' => 'Selecione uma opção:',
                        'sections' => [
                            [
                                'rows' => $lista
                            ]
                        ]
                    ]
                ],
            ]
        ]);
    }

    public static function enviarImg($business_phone_number_id, $numero, $link, $desc = '') {

        // //tratamento de imagens
        // if($request->all()['entry'][0]['changes'][0]['value']['messages'][0]['type'] == 'image'){// caso seja imegem ou arquivo
        //     try {
        //         $imgId = $request->all()['entry'][0]['changes'][0]['value']['messages'][0]['image']['id'];
        //         $imgMime = explode('/', $request->all()['entry'][0]['changes'][0]['value']['messages'][0]['image']['mime_type'])[1];
        //         $filename = "{$imgId}.{$imgMime}";
        //         $client = new \GuzzleHttp\Client();
        //         $response  = $client->request('GET', "https://graph.facebook.com/v23.0/{$imgId}", [
        //             'headers' => [
        //                 'Authorization' => "Bearer " . env('GRAPH_API_TOKEN')
        //             ]
        //         ]);
        //         $mediaData = json_decode($response->getBody(), true);
        //         $imagem = $client->get($mediaData['url'], [
        //             'headers' => [
        //                 'Authorization' => 'Bearer ' . env('GRAPH_API_TOKEN'),
        //             ],
        //         ]);
        //         Storage::disk('public')->put('whatsapp/'.$filename, $imagem->getBody());
        //         $this->enviarMsg($business_phone_number_id, $number, "Link para o arquivo: https://evertonrs.com.br/storage/whatsapp/{$filename}");
        //     } catch (\Throwable $th) {
        //         ChatEnviaMensagem::dispatch($th);
        //     }
        // }

        $client = new \GuzzleHttp\Client();
        $client->request('POST', "https://graph.facebook.com/v23.0/".$business_phone_number_id."/messages", [
            'headers' => [
                'Authorization' => "Bearer " . env('GRAPH_API_TOKEN')
            ],
            'json' => [
                'messaging_product' => 'whatsapp',
                'to' => $numero,
                'type' => 'image',
                'image' => [
                    'link' => $link,
                    "caption" => $desc
                ]
            ]
        ]);
    }

    public static function getProfilePicture($wa_id, $type = 'normal') {

        // só vai funcionar depois de ativar o recurso whatsapp_business_management no painel da api
        // requer um novo video do app funcionando nome correto e etc.

        $client = new \GuzzleHttp\Client();
        
        try {
            $response = $client->request('GET', "https://graph.facebook.com/v23.0/".$wa_id."/picture", [
                'headers' => [
                    'Authorization' => "Bearer " . env('GRAPH_API_TOKEN')
                ],
                'query' => [
                    'type' => $type,       // 'small', 'normal', or 'large'
                    'redirect' => 'false'   // Retorna JSON em vez de redirecionar
                ]
            ]);

            $data = json_decode($response->getBody(), true);

            if (isset($data['data'])) {
                return $data['data']['url']; // Expira em 5 minutos!
            }

        } catch (RequestException $e) {
            // Log do erro (opcional)
            return "Erro ao buscar foto do WhatsApp: " . $e->getMessage();
        }

        return null; // Caso não encontre a foto
    }
}
