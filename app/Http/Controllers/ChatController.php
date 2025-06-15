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
        if(isset(Auth::user()->id)){
            ChatEnviaMensagem::dispatch($request->message);
        }
        return response()->json(['status' => 'Message sent!']);
    }
}
