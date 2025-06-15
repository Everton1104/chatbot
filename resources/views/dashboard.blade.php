@extends('layouts.main.app')

@section('main')
    <div class="container">
        <div class="text-center my-3">
            <h1>Bem-Vindo {{Auth::user()->name}}</h1>
        </div>
    </div>
@endsection