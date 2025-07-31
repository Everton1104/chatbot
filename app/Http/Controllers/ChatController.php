<?php

namespace App\Http\Controllers;

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
        $user_id = ConversasModel::where('user_id', Auth::user()->id)->first();
        if($id > 0){
            $conversa = ConversasModel::find($id);
            $conversa['msgs'] = MensagensModel::when(function ($query) use ($user_id, $id) {
                $query->where(function ($query) use ($user_id , $id) {
                    $query->where([['conversa_id_from', '=', $id], ['conversa_id_to', '=', $user_id->id]]);
                })->orWhere(function ($query) use ($user_id, $id) {
                    $query->where([['conversa_id_from', '=', $user_id->id], ['conversa_id_to', '=', $id]]);
                });
                $query->where('tipo', '>', 3);
            })->orderBy('created_at', 'DESC')->get();
            return $conversa;
        }
        if($user_id){
            $conversas = ConversasModel::find($user_id->id)
                ->leftJoin('users', 'conversas.user_id', '=', 'users.id')
                ->select('users.*', 'conversas.*')
                ->orderBy('conversas.created_at', 'desc')
                ->get();
                return $conversas;
        }
        return response()->json(['error' => 'Conversas não encontradas'], 400);
    }

    public function enviaMsg(Request $request)
    {
        $user_id = ConversasModel::where('user_id', Auth::user()->id)->first();
        // depois colocar tipo 5 e 6
        $request->validate([
            'msg' => 'required',
            'id' => 'required'
        ]);
        MensagensModel::create([
            'msg' => $request->msg,
            'conversa_id_from' => $user_id->id,
            'conversa_id_to' => $request->id,
            'tipo' => 4
        ]);
        return $this->getMsgs($request->id);
    }

    public function loadMsgs(Request $request)
    {
        $request->validate([
            'id' => 'required'
        ]);
        return $this->getMsgs($request->id);
    }
}
