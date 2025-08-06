<?php

namespace App\Http\Controllers;

use App\Events\ChatEnviaMensagem;
use App\Models\ConversasModel;
use App\Models\MensagensModel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $conversas = $this->getMsgs();
        return view('chat.index', compact('conversas'));
    }

    public function getMsgs($id = 0)
    {
        // carregar msgs de conversa selecionada
        if($id > 0){
            $conversa = ConversasModel::find($id);
            $mensagens = MensagensModel::where(function($query) use ($id) {
                $query->where([
                    ['conversa_id_from', '=', $id],
                    ['conversa_id_to', '=', Auth::user()->departamento_id],
                    ['tipo', '>=', 4],
                    ['tipo', '<=', 8]
                ])->orWhere(function($query2) use ($id) { // usei o query2 para colocar o or entre os dois where
                    $query2->where([
                        ['conversa_id_from', '=', Auth::user()->departamento_id],
                        ['conversa_id_to', '=', $id],
                        ['tipo', '>=', 4],
                        ['tipo', '<=', 8]
                    ]);
                });
            })->get();

            foreach ($mensagens as $msg) {
                $msg->update(['status' => 1]);
            }

            $response = $conversa->toArray();
            $response['msgs'] = $mensagens->toArray();

            return $response;
        }
        // carregar todas as conversas sem as msgs
        $conversas = MensagensModel::where([['conversa_id_to', '=', Auth::user()->departamento_id],['tipo', '>=', 4],['tipo', '<=', 8]])
            ->leftJoin('conversas', 'conversas.id', '=', 'mensagens.conversa_id_from')
            ->select('conversas.id', 'conversas.numero', 'conversas.nome', 'conversas.foto')
            ->groupBy('conversas.id', 'conversas.numero', 'conversas.nome', 'conversas.foto')
            ->orderBy('conversas.created_at', 'desc')
            ->get();
        return $conversas;
    }

    public function enviaMsg(Request $request)
    {
        $request->validate([
            'msg' => 'required',
            'id' => 'required'
        ]);
        MensagensModel::create([
            'msg' => 'Mensagem de '.Auth::user()->name.':<br><br>'.$request->msg,
            'conversa_id_from' => Auth::user()->departamento_id,
            'conversa_id_to' => $request->id,
            'tipo' => 4
        ]);
        WhatsappController::enviarMsg(env('PHONE_NUMBER_ID'), ConversasModel::find($request->id)->numero, 'Mensagem de '.Auth::user()->name.":\r\n\r\n".$request->msg);
        ChatEnviaMensagem::dispatch($request->id);
        return $this->getMsgs($request->id);
    }

    public function loadMsgs(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);
        return $this->getMsgs($request->id);
    }

    public function getConversas()
    {
        $conversas = MensagensModel::where([
                ['conversa_id_to', '=', Auth::user()->departamento_id],
                ['tipo', '>=', 4],
                ['tipo', '<=', 8],
            ])
            ->leftJoin('conversas', 'conversas.id', '=', 'mensagens.conversa_id_from')
            ->select('conversas.id', 'conversas.numero', 'conversas.nome', 'conversas.foto')
            ->groupBy('conversas.id', 'conversas.numero', 'conversas.nome', 'conversas.foto')
            ->orderBy('conversas.created_at', 'desc')
            ->get();
        foreach ($conversas as $conversa) {
            $conversa['nao_lidas'] = MensagensModel::where([
                ['conversa_id_from', '=', $conversa->id],
                ['conversa_id_to', '=', Auth::user()->departamento_id],
                ['status', '=', 0]
            ])->count();
        }
        $conversas = $conversas->sortByDesc('nao_lidas');
        return $conversas;
    }

    public function procurarConversa(Request $request)
    {
        $conversas = MensagensModel::where([
                ['conversa_id_to', '=', Auth::user()->departamento_id],
                ['tipo', '>=', 4],
                ['tipo', '<=', 8],
                ['nome', 'like', '%'.$request->busca.'%'],
            ])
            ->leftJoin('conversas', 'conversas.id', '=', 'mensagens.conversa_id_from')
            ->select('conversas.id', 'conversas.numero', 'conversas.nome', 'conversas.foto')
            ->groupBy('conversas.id', 'conversas.numero', 'conversas.nome', 'conversas.foto')
            ->orderBy('conversas.created_at', 'desc')
            ->get();
        return $conversas;
    }

    public function enviaDoc(Request $request)
    {
        $request->validate([
            'file' => 'required',
        ]);
        $mimeType = $request->file('file')->getMimeType();
        $randomId = substr(str_shuffle('0123456789'), 0, 15);
        $request->file('file')->storeAs('whatsapp', $randomId.".".$request->file('file')->getClientOriginalExtension(), ['disk' => 'public']);
        $link = $randomId.".".$request->file('file')->getClientOriginalExtension();
        switch (explode('/',$mimeType)[0]) {
            case 'image':
                MensagensModel::create([
                    'msg' => '',
                    'conversa_id_from' => Auth::user()->departamento_id,
                    'conversa_id_to' => $request->id,
                    'link' => $link,
                    'tipo' => 6
                ]);
                WhatsappController::enviarImg(env('PHONE_NUMBER_ID'), ConversasModel::find($request->id)->numero, url('/').'/storage/whatsapp/'.$link, 'Imagem enviada por '.Auth::user()->name);
                break;
            case 'audio':
                MensagensModel::create([
                    'msg' => '',
                    'conversa_id_from' => Auth::user()->departamento_id,
                    'conversa_id_to' => $request->id,
                    'link' => $link,
                    'tipo' => 5
                ]);
                WhatsappController::enviarAudio(env('PHONE_NUMBER_ID'), ConversasModel::find($request->id)->numero, url('/').'/storage/whatsapp/'.$link, 'Audio enviado por '.Auth::user()->name);
                break;
            case 'video':
                MensagensModel::create([
                    'msg' => '',
                    'conversa_id_from' => Auth::user()->departamento_id,
                    'conversa_id_to' => $request->id,
                    'link' => $link,
                    'tipo' => 7
                ]);
                WhatsappController::enviarVideo(env('PHONE_NUMBER_ID'), ConversasModel::find($request->id)->numero, url('/').'/storage/whatsapp/'.$link, 'Video enviado por '.Auth::user()->name);
                break;
            default:
                MensagensModel::create([
                    'msg' => '',
                    'conversa_id_from' => Auth::user()->departamento_id,
                    'conversa_id_to' => $request->id,
                    'link' => $link,
                    'tipo' => 8
                ]);
                WhatsappController::enviarDoc(env('PHONE_NUMBER_ID'), ConversasModel::find($request->id)->numero, url('/').'/storage/whatsapp/'.$link, 'Arquivo enviado por '.Auth::user()->name);
                break;
        }
        ChatEnviaMensagem::dispatch($request->id);
        return $this->getMsgs($request->id);
    }
}
