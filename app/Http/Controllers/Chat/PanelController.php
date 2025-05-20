<?php
// app/Http/Controllers/Chat/PanelController.php

namespace App\Http\Controllers\Chat;

use App\Http\Controllers\Controller;

class PanelController extends Controller
{
    /**
     * Muestra la vista del panel de chat.
     * La lógica de carga de datos se hará via AJAX en el frontend.
     */
    public function index()
    {
        return view('chat.index');
    }
}
