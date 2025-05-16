<?php

namespace App\Http\Controllers;

use App\Events\ChatEnviaMensagem;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function EnviaMensagem(Request $request)
    {
        ChatEnviaMensagem::dispatch($request->message);
        return response()->json(['status' => 'Message sent!']);
    }
}
