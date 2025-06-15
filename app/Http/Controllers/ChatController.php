<?php

namespace App\Http\Controllers;

use App\Events\ChatEnviaMensagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat.index');
    }

    public function EnviaMensagem(Request $request)
    {
        $numero = '5511997646569';
        if(isset(Auth::user()->id)){
            WhatsappController::enviarMsg(env('PHONE_NUMBER_ID'), $numero, $request->message);
            ChatEnviaMensagem::dispatch($request->message);
        }
        return response()->json(['status' => 'Message sent!']);
    }
}
