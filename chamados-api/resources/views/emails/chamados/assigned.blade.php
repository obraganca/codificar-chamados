@extends('emails.layouts.ticket')

@section('title', 'Chamado atribuído #' . $chamado->id)

@section('content')

<h2>Chamado atribuído a você</h2>

<p>Olá, {{ $usuario->name }}! O chamado abaixo foi atribuído a você.</p>

@include('emails.components.chamados.info', ['chamado' => $chamado])

@include('emails.components.chamados.button', [
    'url' => url('/chamados/' . $chamado->id),
    'text' => 'Visualizar chamado'
])

@endsection