<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

// Canal privado padrao das Notifications com o canal "broadcast". So o proprio
// usuario assina o seu canal: e isso que garante que a notificacao chega so
// para o destinatario.
Broadcast::channel('App.Models.User.{id}', function (User $user, int $id) {
    return (int) $user->id === $id;
});

// Canal privado do chat/atualizacoes de um chamado: participantes e admins.
// Mesma regra usada pela API de mensagens.
Broadcast::channel('tickets.chat.{ticketId}', function (User $user, int $ticketId) {
    $ticket = Ticket::with('responsavel')->find($ticketId);

    return $ticket !== null && $ticket->podeSerAcessadoPor($user);
});