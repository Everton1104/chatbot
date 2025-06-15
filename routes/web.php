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

Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard')->middleware('auth');


Route::get('politicabot', function () {
    return view('politicabot');
});

Route::post('/whatsapp/webhook', [WhatsappController::class, 'index']);

Route::get('/whatsapp/webhook', [WhatsappController::class, 'show']);

// Route::any('/whatsapp/webhook', [WhatsappController::class, 'teste']);

Route::middleware('auth')->group(function () {
    Route::get('chat', [ChatController::class, 'index'])->name('chat.index');
    Route::post('chat-send', [ChatController::class, 'EnviaMensagem'])->name('chat.send');
});
