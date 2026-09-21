<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Esta aplicacao e apenas API (json), entao nunca redirecionamos para uma
     * tela de login: o Handler de excecoes do Laravel transforma a
     * AuthenticationException em uma resposta 401 JSON automaticamente
     * quando nao ha para onde redirecionar.
     */
    protected function redirectTo(Request $request): ?string
    {
        return null;
    }
}
