@extends('layouts.app')

@section('content')
<style>
    .admin-menu {
        --card-bg: #ffffff;
        --card-hover-bg: #f9fafb;
        --card-border: #e5e7eb;
        --card-hover-border: #3b82f6;
        --card-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        --card-hover-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        --icon-color: #3b82f6;
        --icon-hover-color: #2563eb;
        --text-color: #1f2937;
    }

    .admin-menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1.5rem;
        max-width: 1200px;
        margin: 0 auto;
        padding: 2rem 1rem;
    }

    .admin-menu-card {
        background: var(--card-bg);
        border-radius: 0.75rem;
        border: 1px solid var(--card-border);
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 2rem 1rem;
        text-decoration: none;
        color: var(--text-color);
        transition: all 0.2s ease-in-out;
        aspect-ratio: 1 / 1;
    }

    .admin-menu-card:hover,
    .admin-menu-card:focus {
        background: var(--card-hover-bg);
        border-color: var(--card-hover-border);
        box-shadow: var(--card-hover-shadow);
        transform: translateY(-4px);
    }

    .admin-menu-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 3.5rem;
        height: 3.5rem;
        margin-bottom: 1rem;
        border-radius: 50%;
        background-color: rgba(59, 130, 246, 0.1);
        color: var(--icon-color);
        font-size: 1.5rem;
        transition: all 0.2s ease;
    }

    .admin-menu-card:hover .admin-menu-icon {
        background-color: rgba(59, 130, 246, 0.2);
        color: var(--icon-hover-color);
        transform: scale(1.1);
    }

    .admin-menu-title {
        font-size: 1rem;
        font-weight: 600;
        text-align: center;
        margin: 0;
    }

    @media (max-width: 640px) {
        .admin-menu-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="container mx-auto py-6 admin-menu">
    <div class="flex justify-between items-center mb-8 px-4">
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-800">Menú de Administración</h1>
    </div>

    <div class="admin-menu-grid">
        {{-- Mostrar opción de Sucursales --}}
        @if(Auth::user()->tieneAlgunPermiso(['gestionar_sucursales', 'gestionar_tiendas']))
        <a href="{{ route('admin.sucursales.index') }}" class="admin-menu-card">
            <div class="admin-menu-icon">
                <i class="fas fa-store"></i>
            </div>
            <h2 class="admin-menu-title">Sucursales</h2>
        </a>
        @endif

        {{-- Mostrar opción de Usuarios - Cualquier usuario con permisos de gestión de usuarios puede ver esta opción --}}
        @if(Auth::user()->tieneAlgunPermiso(['gestionar_usuarios', 'gestionar_usuarios_tienda', 'gestionar_usuarios_rol']))
        <a href="{{ route('admin.usuarios.index') }}" class="admin-menu-card">
            <div class="admin-menu-icon">
                <i class="fas fa-users"></i>
            </div>
            <h2 class="admin-menu-title">Usuarios</h2>
        </a>
        @endif

        {{-- Mostrar opción de Roles - Solo administradores pueden gestionar roles --}}
        @if(Auth::user()->tienePermiso('gestionar_roles'))
        <a href="{{ route('admin.roles.index') }}" class="admin-menu-card">
            <div class="admin-menu-icon">
                <i class="fas fa-user-tag"></i>
            </div>
            <h2 class="admin-menu-title">Roles</h2>
        </a>
        @endif

        {{-- Mostrar opción de Tiendas --}}
        @if(Auth::user()->tieneAlgunPermiso(['gestionar_tiendas', 'gestionar_usuarios_tienda']))
        <a href="{{ route('admin.tiendas.index') }}" class="admin-menu-card">
            <div class="admin-menu-icon">
                <i class="fas fa-store-alt"></i>
            </div>
            <h2 class="admin-menu-title">Tiendas</h2>
        </a>
        @endif

        {{-- Mostrar opción de Reportes --}}
        @if(Auth::user()->tieneAlgunPermiso(['ver_reportes', 'gestionar_reportes']))
        <a href="{{ route('admin.reportes.index') }}" class="admin-menu-card">
            <div class="admin-menu-icon">
                <i class="fas fa-chart-bar"></i>
            </div>
            <h2 class="admin-menu-title">Reportes</h2>
        </a>
        @endif

        {{-- Mostrar opción de Vehículos --}}
        @if(Auth::user()->tieneAlgunPermiso(['gestionar_vehiculos', 'ver_vehiculos']))
        <a href="{{ url('/admin/vehiculos') }}" class="admin-menu-card">
            <div class="admin-menu-icon">
                <i class="fas fa-car"></i>
            </div>
            <h2 class="admin-menu-title">Vehículos</h2>
        </a>
        @endif
        
        {{-- Mostrar opción de Chat --}}
        @if(Auth::user()->tienePermiso('acceder_chat'))
        <a href="{{ url('chat') }}" class="admin-menu-card">
            <div class="admin-menu-icon">
                <i class="fas fa-comments"></i>
            </div>
            <h2 class="admin-menu-title">Chat</h2>
        </a>
        @endif
    </div>
</div>
@endsection