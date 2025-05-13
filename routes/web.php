<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('manutencao');
});

require __DIR__.'/auth.php';


