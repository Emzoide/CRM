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
        <h1 class="text-2xl md:text-3xl font-semibold text-gray-800">Reportes</h1>
    </div>

    <div class="admin-menu-grid">
        <a href="{{ route('admin.reportes.antispam') }}" class="admin-menu-card">
            <div class="admin-menu-icon">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h2 class="admin-menu-title">Consentimiento Antispam</h2>
        </a>
    </div>
</div>
@endsection