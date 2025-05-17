<?php

namespace App\Http\Controllers;

use App\Events\ChatEnviaMensagem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function index()
    {
        return view('chat');
    }

    public function EnviaMensagem(Request $request)
    {
        if(isset(Auth::user()->id)){
            ChatEnviaMensagem::dispatch($request->message);
            $client = new \GuzzleHttp\Client();
            $client->request('POST', "https://graph.facebook.com/v18.0/".env('PHONE_NUMBER_ID')."/messages", [
                'headers' => [
                    'Authorization' => "Bearer " . env('GRAPH_API_TOKEN')
                ],
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => '11997646569',
                    'text' => [
                        'body' => $request->message
                    ],
                ]
            ]);
        }
        return response()->json(['status' => 'Message sent!']);
    }
}
