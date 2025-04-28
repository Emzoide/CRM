<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    // A dónde redirigir tras el login
    protected $redirectTo = '/';

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // Nombre del campo de formulario (seguimos usando 'login')
    public function username(): string
    {
        return 'login';
    }

    // Validación del formulario
    protected function validateLogin(Request $request)
    {
        $request->validate([
            $this->username() => 'required|string',
            'password'        => 'required|string',
        ]);
    }

    /**
     * Determina qué campo usar en la consulta: email o login,
     * y exige activo = 1.
     */
    protected function credentials(Request $request): array
    {
        $login = $request->input($this->username());
        $password = $request->input('password');

        // Si es un email válido, busca por email; sino, por login
        $field = filter_var($login, FILTER_VALIDATE_EMAIL)
            ? 'email'
            : 'login';

        return [
            $field => $login,
            'password_hash' => $password,
            'activo' => 1,
        ];
    }

    // Después de autenticarse, guardamos last_login
    protected function authenticated(Request $request, $usuario)
    {
        $usuario->last_login = Carbon::now();
        $usuario->save();

        return redirect()->intended($this->redirectPath());
    }
}
