<?php

namespace App\Http\Requests\Api\V1\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Encapsula a validacao do login E a regra de rate limiting de tentativas,
 * mantendo o AuthController enxuto (Single Responsibility: o controller
 * orquestra, o Request sabe como validar e autenticar).
 */
class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Tenta autenticar o usuario. Nao inicia sessao (API stateless):
     * apenas confirma que as credenciais sao validas para que o
     * controller possa emitir um token Sanctum.
     */
    public function authenticate(): \App\Models\User
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::once($this->only('email', 'password'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'As credenciais informadas nao conferem.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());

        /** @var \App\Models\User $user */
        $user = Auth::user();

        return $user;
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Muitas tentativas. Tente novamente em {$seconds} segundos.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
