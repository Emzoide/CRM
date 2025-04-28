@extends('layouts.app')

@section('content')
<style>
    .menu-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 2rem;
        max-width: 900px;
        margin: 0 auto;
        padding: 3rem 0;
    }

    .menu-card {
        background: #f8fafc;
        border-radius: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 2.5rem 1rem;
        text-decoration: none;
        color: #222;
        transition: box-shadow 0.2s, transform 0.2s;
        cursor: pointer;
        border: 2px solid transparent;
    }

    .menu-card:hover {
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.13);
        transform: translateY(-4px) scale(1.03);
        border-color: #2563eb;
    }

    .menu-card i {
        margin-bottom: 1.2rem;
        color: #2563eb;
        font-size: 3rem;
    }

    .menu-card span {
        font-size: 1.3rem;
        font-weight: 600;
        letter-spacing: 0.5px;
    }
</style>

<div class="container mx-auto py-6">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-semibold">Menú de Administración</h1>
    </div>
    <div class="menu-grid">
        <a href="#" class="menu-card">
            <i class="fas fa-store"></i>
            <span>Sucursales</span>
        </a>
        <a href="#" class="menu-card">
            <i class="fas fa-car"></i>
            <span>Vehículos</span>
        </a>
        <a href="#" class="menu-card">
            <i class="fas fa-users"></i>
            <span>Usuarios</span>
        </a>
        <a href="#" class="menu-card">
            <i class="fas fa-university"></i>
            <span>Bancos</span>
        </a>
        <a href="#" class="menu-card">
            <i class="fas fa-address-book"></i>
            <span>Medios de contacto</span>
        </a>
    </div>
</div>
@endsection