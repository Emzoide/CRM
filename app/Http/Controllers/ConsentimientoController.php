<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Consentimiento;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class ConsentimientoController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'nullable|string|max:100',
            'apellido' => 'nullable|string|max:100',
            'dni' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:20',
            'acepta_politicas' => 'required|boolean',
            'acepta_comunicaciones' => 'required|boolean',
            'fuente_origen' => 'nullable|string|max:100',
            'foto_dni_url' => 'nullable|url|max:255',
            'firma_digital_url' => 'nullable|url|max:255'
        ]);

        $data['ip'] = $request->ip();
        $data['user_agent'] = $request->header('User-Agent');
        $data['fecha_aceptacion'] = Carbon::now();
        $this->enviar_email($data);
        $consentimiento = Consentimiento::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Consentimiento registrado correctamente.',
            'data' => $consentimiento
        ], 201);
    }
    public function enviar_email($data)
    {
        try {
            \Mail::send('emails.consentimiento', ['data' => $data], function ($message) use ($data) {
                $message->from('comunica@interamericana.shop', 'Interamericana Comunica')
                    ->to($data['email'])
                    ->subject('Gracias por tu consentimiento - Interamericana Norte');
            });
            return true;
        } catch (\Exception $e) {
            \Log::error("Error al enviar el correo: " . $e->getMessage());
            return false;
        }
    }
    public function show($dni)
    {
        $consentimiento = Consentimiento::where('dni', $dni)->first();

        if (!$consentimiento) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ningún consentimiento con el DNI proporcionado'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'nombre' => $consentimiento->nombre,
                'apellido' => $consentimiento->apellido,
                'dni' => $consentimiento->dni,
                'email' => $consentimiento->email,
                'telefono' => $consentimiento->telefono,
                'acepta_politicas' => $consentimiento->acepta_politicas,
                'acepta_comunicaciones' => $consentimiento->acepta_comunicaciones,
                'fuente_origen' => $consentimiento->fuente_origen,
                'fecha_aceptacion' => $consentimiento->fecha_aceptacion,
                'foto_dni_url' => $consentimiento->foto_dni_url,
                'firma_digital_url' => $consentimiento->firma_digital_url
            ]
        ]);
    }
}
