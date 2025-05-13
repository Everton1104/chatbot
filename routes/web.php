<?php

use App\Models\ConversasModel;
use App\Models\MensagensModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('manutencao');
});

require __DIR__.'/auth.php';

Route::post('/whatsapp/webhook', function () {
    $business_phone_number_id = Request::capture()['entry'][0]['changes'][0]['value']['metadata']['phone_number_id'];
    $msg = Request::capture()['entry'][0]['changes'][0]['value']['messages'][0];
    $msgTxt = $msg['text']['body'];
    $number = $msg['from'];
    if($msg['type'] == 'text'){// caso seja msg de texto
        // tratar msg
        $conversa = ConversasModel::where('numero','=',$number)->first();
        if(!$conversa){ // Nova conversa
            ConversasModel::create(['numero' => $number]);
            //msg de boas vindas
            enviarMsg($business_phone_number_id, $number,"Olá, bem vindo ao chatbot do Evtu, qual é o seu nome?");
        }elseif(!MensagensModel::where('numero_id','=',$conversa->id)->first()){ // pegar nome 
            $conversa->update(['nome' => $msgTxt]);
            MensagensModel::create(['numero_id' => $conversa->id, 'msg' => "Nome do Cliente:".$msgTxt]);
            enviarMsg($business_phone_number_id, $number,"Muito bem ".$msgTxt. " vamos começar. Em que posso ajudar?");
        }else{
            // Body
            $body = '{"contents": [';
            $data = now()->subHours(2); // ultimas duas horas da data atual
            $msgs = MensagensModel::where('numero_id','=',$conversa->id)->where('created_at','>=',$data)->get();// pega as mensagens da ultimas duas horas da data atual
            // Body

            $body .= '{"role": "user", "parts": [{"text": "';

            $body .= '[Inicio das mensagens anteriores] \n\n';
            foreach ($msgs as $msg) {
                $body .= '[' . $msg->$msg . ']\n\n';
            }
            $body .= '[Fim das mensagens anteriores] \n\n';
            
            $body .= 'Ultima mensagem do cliente: \n\n' . $msgTxt . '"}]},';

            // Body
            $body .= '],}';
            // Body
            MensagensModel::create(['numero_id' => $conversa->id, 'msg' => $msgTxt, 'tipo' => 1]);
            $resposta = enviarMsgGemini($body);
            MensagensModel::create(['numero_id' => $conversa->id, 'msg' => $resposta, 'tipo' => 2]);
            enviarMsg($business_phone_number_id, $number, $resposta);
        }
    }else{
        enviarMsg($business_phone_number_id, $number, "Desculpe, apenas mensagens de texto são aceitas.");
    }
});

function enviarMsgGemini($body) {
    try {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=".env("API_GEMINI"), ['body' => $body]);
        return json_decode($response->getBody()->getContents(),true)['candidates'][0]['content']['parts'][0]['text'];
    } catch (\Throwable $th) {
        return 'Estou com problemas para reponder, aguarde alguns instantes e me pergunte novamente.'.$th->getMessage();
    }
}

function enviarMsg($business_phone_number_id, $numero, $msg) {
    $client = new \GuzzleHttp\Client();
    $client->request('POST', "https://graph.facebook.com/v18.0/".$business_phone_number_id."/messages", [
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

Route::get('/whatsapp/webhook', function () {
    $request = Request::capture();
    $verifyToken = env('WEBHOOK_VERIFY_TOKEN');
    $challenge = $request['hub_challenge'];
    $token = $request['hub_verify_token'];
    if ($token === $verifyToken) {
        return response($challenge, 200);
    }
    return response('Token de verificação inválido', 403);
});
