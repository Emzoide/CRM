<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
        }

        .content {
            padding: 20px;
        }

        .footer {
            text-align: center;
            padding: 20px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>

<body>
    <div class='container'>
        <div class='header'>
            <h2>¡Gracias por tu respuesta!</h2>
        </div>
        <div class='content'>
            <p>Hola {{ $data['nombre'] }} {{ $data['apellido'] }},</p>
            <p>Te agradecemos por proporcionarnos tus datos de contacto. Hemos registrado la siguiente información:</p>
            <ul>
                <li>DNI: {{ $data['dni'] }}</li>
                <li>Email: {{ $data['email'] }}</li>
                <li>Teléfono: {{ $data['telefono'] }}</li>
            </ul>
            <p>Nos pondremos en contacto contigo pronto para brindarte la mejor atención.</p>
            <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
        </div>
        <div class='footer'>
            <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
        </div>
    </div>
</body>

</html>