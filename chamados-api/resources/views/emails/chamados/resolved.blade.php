@extends('emails.layouts.ticket')

@section('title', 'Chamado resolvido #' . $chamado->id)

@section('content')

<h2>Chamado resolvido</h2>

<p>
    Olá, {{ $usuario->name }}! O chamado abaixo foi marcado como resolvido
    @if ($chamado->responsavel)
        por <strong>{{ $chamado->responsavel->nome }}</strong>
    @endif
    .
</p>

@include('emails.components.chamados.info', ['chamado' => $chamado])

@include('emails.components.chamados.button', [
    'url' => url('/chamados/' . $chamado->id),
    'text' => 'Visualizar chamado'
])

@endsection