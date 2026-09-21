@extends('emails.layouts.ticket')

@section('title', 'Novo chamado #' . $chamado->id)

@section('content')

<h2>
    Novo chamado aberto
</h2>

<p>
    Olá, {{ $usuario->name }}!
</p>

<p>
    Um novo chamado foi aberto no sistema.
</p>

@include(
    'emails.components.chamados.info',
    ['chamado' => $chamado]
)

@include(
    'emails.components.chamados.button',
    [
        'url' => url('/chamados/' . $chamado->id),
        'text' => 'Visualizar chamado'
    ]
)

@endsection