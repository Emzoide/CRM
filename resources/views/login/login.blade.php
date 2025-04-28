<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>INSAC CRM - Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #6366f1;
            --sidebar-bg-gradient-start: #1e40af;
            --sidebar-bg-gradient-end: #1e1e38;
            --text-color: #1f2937;
            --light-text: #6b7280;
            --white: #ffffff;
            --light-bg: #f3f4f6;
            --border-color: #e5e7eb;
            --danger: #ef4444;
            --success: #10b981;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--sidebar-bg-gradient-start), var(--sidebar-bg-gradient-end));
            color: var(--text-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            padding: 2.5rem;
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
        }

        .logo-container {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 2rem;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
            margin-right: 1rem;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .login-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: var(--light-text);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            background-color: var(--white);
            border: 2px solid var(--border-color);
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .form-check {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
        }

        .form-check-input {
            width: 1.2rem;
            height: 1.2rem;
            margin-right: 0.5rem;
            border-radius: 4px;
            border: 2px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-check-label {
            font-size: 0.95rem;
            color: var(--light-text);
        }

        .forgot-password {
            font-size: 0.95rem;
            color: var(--primary-color);
            text-decoration: none;
            float: right;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: var(--primary-dark);
        }

        .btn {
            display: inline-block;
            font-weight: 600;
            text-align: center;
            white-space: nowrap;
            vertical-align: middle;
            user-select: none;
            border: 2px solid transparent;
            padding: 0.75rem 1.5rem;
            font-size: 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            width: 100%;
        }

        .btn-primary {
            color: white;
            background: linear-gradient(135deg, var(--primary-color), var(--primary-light));
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
            font-size: 0.95rem;
        }

        .alert-danger {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        @media (max-width: 640px) {
            body {
                padding: 1rem;
            }

            .login-container {
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                    <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                </svg>
            </div>
            <div class="logo-text">INSAC CRM</div>
        </div>

        <div class="login-header">
            <h1>Bienvenido de nuevo</h1>
            <p>Ingresa tus credenciales para acceder al sistema</p>
        </div>

        @if (isset($errors) && $errors->any())
        <div class="alert alert-danger">
            <ul style="margin: 0; padding-left: 1rem;">
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input id="email" type="email" class="form-control" name="email" required autocomplete="email" autofocus>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <a class="forgot-password">¿Olvidaste tu contraseña?</a>
                <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
            </div>

            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="remember" id="remember">
                <label class="form-check-label" for="remember">
                    Recordar sesión
                </label>
            </div>

            <button type="submit" class="btn btn-primary">
                Iniciar Sesión
            </button>
        </form>
    </div>
</body>

</html>