<?php

date_default_timezone_set('America/Sao_Paulo');

use App\Events\ChatEnviaMensagem;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\CommandController;
use App\Http\Controllers\WhatsappController;
use App\Models\ConversasModel;
use App\Models\MensagensModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Output\BufferedOutput;

require __DIR__.'/auth.php';

Route::get('/', function () {
    if(Auth::user()){
        return view('dashboard');
    }
    return view('manutencao');
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    Route::resource('chat', ChatController::class);
    Route::post('loadMsgs', [ChatController::class, 'loadMsgs'] )->name('loadMsgs');
    Route::post('enviaMsg', [ChatController::class, 'enviaMsg'] )->name('enviaMsg');
});

Route::get('politicabot', function () {
    return view('politicabot');
});

// WHATSAPP
Route::post('/whatsapp/webhook', [WhatsappController::class, 'index']); // RECEBE MENSAGENS
Route::get('/whatsapp/webhook', [WhatsappController::class, 'show']); // VALIDAÇÃO DO TOKEN
// WHATSAPP
