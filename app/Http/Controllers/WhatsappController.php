<?php

namespace App\Http\Controllers;

use App\Events\ChatEnviaMensagem;
use App\Models\ConversasModel;
use App\Models\MensagensModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class WhatsappController extends Controller
{
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
        $business_phone_number_id = Request::capture()['entry'][0]['changes'][0]['value']['metadata']['phone_number_id']??0;
        $nome = Request::capture()['entry'][0]['changes'][0]['value']['contacts'][0]['profile']['name']??'';
        $msg = Request::capture()['entry'][0]['changes'][0]['value']['messages'][0]??'';
        $msgTxt = $msg['text']['body']??'';
        $msgSimNao = $msg['interactive']['button_reply']['id']??null;
        $msgLista = $msg['interactive']['list_reply']['id']??null;
        $number = $msg['from']??0;

        if($msg['type'] == 'text' || $msg['type'] == 'interactive'){
            $conversa = ConversasModel::where('numero','=',$number)->first();
            // ROTA NOVAS CONVERSAS
            if(!$conversa){ // Nova conversa
                $conversa = ConversasModel::create([
                    'numero' => $number,
                    'nome' => $nome
                ]);
                //msg de boas vindas
                $msgBoasVindas = "Olá, bem vindo ao chatbot do Evtu, antes de começar, seu nome é ".$nome."?\n";
                $this->enviarMsgSimNao($business_phone_number_id, $number,$msgBoasVindas);
                MensagensModel::create([
                    'conversa_id' => $conversa->id, 
                    'msg' => $msgBoasVindas,
                    'tipo' => 0, // boas vindas
                ]);
            }else{
                $msgs = MensagensModel::where('conversa_id','=',$conversa->id)->orderBy('created_at', 'desc')->get();
                if($msgs[0]->tipo == 0){ // se a ultima mensagem é a de boas vindas
                    if($msgSimNao == 'sim'){ // se o nome esta correto.
                        $msg = "Certo ".$conversa->nome.", como posso te ajudar?";
                        $this->enviarMsg($business_phone_number_id, $number,$msg);
                        MensagensModel::create([
                            'conversa_id' => $conversa->id, 
                            'msg' => $msg,
                            'tipo' => 1, // bot
                        ]);
                    }else{
                        $msg = "Qual seu nome?";
                        $this->enviarMsg($business_phone_number_id, $number,$msg);
                        MensagensModel::create([
                            'conversa_id' => $conversa->id, 
                            'msg' => $msg,
                            'tipo' => 3, // alterar nome
                        ]);
                    }
                }elseif($msgs[0]->tipo == 3){// pedido de alteracao de nome.
                    $conversa->update(['nome' => $msgTxt]);
                    $msg = "Certo ".$conversa->nome.", como posso te ajudar?";
                    $this->enviarMsg($business_phone_number_id, $number,$msg);
                    MensagensModel::create([
                        'conversa_id' => $conversa->id, 
                        'msg' => $msg,
                        'tipo' => 1, // bot
                    ]);
                }else{
                    //ROTA PADRÃO

                    

                }
            }
        }
        return response('ok', 200);
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

    public static function enviarMsgSimNao($business_phone_number_id, $numero, $msg) {
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
                                    'id' => 'sim',
                                    'title' => 'Sim'
                                ]
                            ],
                            [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => 'nao',
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
}
