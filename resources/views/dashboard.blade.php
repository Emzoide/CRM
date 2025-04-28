   @extends('layouts.app')

    <title>Insac CRM - Dashboard</title>
    @section('content')
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        /* Estilos base */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
            line-height: 1.5;
        }
        
        /* Layout */
        .min-h-screen {
            min-height: 100vh;
        }
        
        .flex {
            display: flex;
        }
        
        .flex-col {
            flex-direction: column;
        }
        
        .flex-1 {
            flex: 1 1 0%;
        }
        
        .flex-shrink-0 {
            flex-shrink: 0;
        }
        
        .items-center {
            align-items: center;
        }
        
        .justify-center {
            justify-content: center;
        }
        
        .justify-between {
            justify-content: space-between;
        }
        
        .overflow-hidden {
            overflow: hidden;
        }
        
        .overflow-x-auto {
            overflow-x: auto;
        }
        
        .overflow-y-auto {
            overflow-y: auto;
        }
        
        /* Spacing */
        .p-2 {
            padding: 0.5rem;
        }
        
        .p-4 {
            padding: 1rem;
        }
        
        .p-6 {
            padding: 1.5rem;
        }
        
        .px-2 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .px-4 {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .px-6 {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
        }
        
        .py-2 {
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        
        .py-3 {
            padding-top: 0.75rem;
            padding-bottom: 0.75rem;
        }
        
        .py-4 {
            padding-top: 1rem;
            padding-bottom: 1rem;
        }
        
        .py-6 {
            padding-top: 1.5rem;
            padding-bottom: 1.5rem;
        }
        
        .pt-5 {
            padding-top: 1.25rem;
        }
        
        .pb-4 {
            padding-bottom: 1rem;
        }
        
        .mt-2 {
            margin-top: 0.5rem;
        }
        
        .mt-4 {
            margin-top: 1rem;
        }
        
        .mt-6 {
            margin-top: 1.5rem;
        }
        
        .mb-2 {
            margin-bottom: 0.5rem;
        }
        
        .mb-4 {
            margin-bottom: 1rem;
        }
        
        .mb-6 {
            margin-bottom: 1.5rem;
        }
        
        .ml-2 {
            margin-left: 0.5rem;
        }
        
        .ml-3 {
            margin-left: 0.75rem;
        }
        
        .ml-4 {
            margin-left: 1rem;
        }
        
        .ml-auto {
            margin-left: auto;
        }
        
        .mr-2 {
            margin-right: 0.5rem;
        }
        
        .mr-3 {
            margin-right: 0.75rem;
        }
        
        .mr-4 {
            margin-right: 1rem;
        }
        
        .space-y-2 > * + * {
            margin-top: 0.5rem;
        }
        
        .space-y-4 > * + * {
            margin-top: 1rem;
        }
        
        .space-x-2 > * + * {
            margin-left: 0.5rem;
        }
        
        .space-x-4 > * + * {
            margin-left: 1rem;
        }
        
        .gap-2 {
            gap: 0.5rem;
        }
        
        .gap-4 {
            gap: 1rem;
        }
        
        .gap-6 {
            gap: 1.5rem;
        }
        
        /* Typography */
        .text-xs {
            font-size: 0.75rem;
        }
        
        .text-sm {
            font-size: 0.875rem;
        }
        
        .text-base {
            font-size: 1rem;
        }
        
        .text-lg {
            font-size: 1.125rem;
        }
        
        .text-xl {
            font-size: 1.25rem;
        }
        
        .text-2xl {
            font-size: 1.5rem;
        }
        
        .font-medium {
            font-weight: 500;
        }
        
        .font-semibold {
            font-weight: 600;
        }
        
        .font-bold {
            font-weight: 700;
        }
        
        .uppercase {
            text-transform: uppercase;
        }
        
        .leading-tight {
            line-height: 1.25;
        }
        
        .tracking-wider {
            letter-spacing: 0.05em;
        }
        
        .text-left {
            text-align: left;
        }
        
        .text-center {
            text-align: center;
        }
        
        .text-right {
            text-align: right;
        }
        
        /* Colors */
        .text-white {
            color: #ffffff;
        }
        
        .text-gray-500 {
            color: #6b7280;
        }
        
        .text-gray-600 {
            color: #4b5563;
        }
        
        .text-gray-700 {
            color: #374151;
        }
        
        .text-gray-800 {
            color: #1f2937;
        }
        
        .text-gray-900 {
            color: #111827;
        }
        
        .text-blue-500 {
            color: #3b82f6;
        }
        
        .text-blue-600 {
            color: #2563eb;
        }
        
        .text-blue-700 {
            color: #1d4ed8;
        }
        
        .text-green-500 {
            color: #10b981;
        }
        
        .text-green-600 {
            color: #059669;
        }
        
        .text-red-500 {
            color: #ef4444;
        }
        
        .text-red-600 {
            color: #dc2626;
        }
        
        .text-yellow-500 {
            color: #f59e0b;
        }
        
        .text-yellow-600 {
            color: #d97706;
        }
        
        .text-purple-500 {
            color: #8b5cf6;
        }
        
        .text-purple-600 {
            color: #7c3aed;
        }
        
        .bg-white {
            background-color: #ffffff;
        }
        
        .bg-gray-50 {
            background-color: #f9fafb;
        }
        
        .bg-gray-100 {
            background-color: #f3f4f6;
        }
        
        .bg-gray-200 {
            background-color: #e5e7eb;
        }
        
        .bg-blue-50 {
            background-color: #eff6ff;
        }
        
        .bg-blue-100 {
            background-color: #dbeafe;
        }
        
        .bg-blue-500 {
            background-color: #3b82f6;
        }
        
        .bg-blue-600 {
            background-color: #2563eb;
        }
        
        .bg-blue-700 {
            background-color: #1d4ed8;
        }
        
        .bg-blue-800 {
            background-color: #1e40af;
        }
        
        .bg-blue-900 {
            background-color: #1e3a8a;
        }
        
        .bg-green-100 {
            background-color: #d1fae5;
        }
        
        .bg-green-500 {
            background-color: #10b981;
        }
        
        .bg-yellow-100 {
            background-color: #fef3c7;
        }
        
        .bg-red-100 {
            background-color: #fee2e2;
        }
        
        .bg-purple-100 {
            background-color: #ede9fe;
        }
        
        .hover\:bg-blue-700:hover {
            background-color: #1d4ed8;
        }
        
        .hover\:bg-gray-100:hover {
            background-color: #f3f4f6;
        }
        
        /* Borders */
        .border {
            border-width: 1px;
            border-style: solid;
        }
        
        .border-t {
            border-top-width: 1px;
            border-top-style: solid;
        }
        
        .border-b {
            border-bottom-width: 1px;
            border-bottom-style: solid;
        }
        
        .border-l {
            border-left-width: 1px;
            border-left-style: solid;
        }
        
        .border-r {
            border-right-width: 1px;
            border-right-style: solid;
        }
        
        .border-gray-200 {
            border-color: #e5e7eb;
        }
        
        .border-gray-300 {
            border-color: #d1d5db;
        }
        
        .border-blue-600 {
            border-color: #2563eb;
        }
        
        /* Rounded corners */
        .rounded {
            border-radius: 0.25rem;
        }
        
        .rounded-md {
            border-radius: 0.375rem;
        }
        
        .rounded-lg {
            border-radius: 0.5rem;
        }
        
        .rounded-xl {
            border-radius: 0.75rem;
        }
        
        .rounded-full {
            border-radius: 9999px;
        }
        
        .rounded-t-lg {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
        }
        
        .rounded-b-lg {
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
        }
        
        /* Shadows */
        .shadow {
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        }
        
        .shadow-md {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        
        .shadow-lg {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        /* Grid */
        .grid {
            display: grid;
        }
        
        .grid-cols-1 {
            grid-template-columns: repeat(1, minmax(0, 1fr));
        }
        
        .grid-cols-2 {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
        
        .grid-cols-3 {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
        
        .grid-cols-4 {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }
        
        /* Forms */
        .form-input {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }
        
        .form-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
        
        .form-select {
            display: block;
            width: 100%;
            padding: 0.5rem 2.5rem 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }
        
        .form-select:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }
        
        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 1rem;
            font-weight: 500;
            border-radius: 0.375rem;
            transition-property: background-color, border-color, color;
            transition-duration: 200ms;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-sm {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
        }
        
        .btn-primary {
            background-color: #2563eb;
            color: #ffffff;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
        }
        
        .btn-secondary {
            background-color: #6b7280;
            color: #ffffff;
        }
        
        .btn-secondary:hover {
            background-color: #4b5563;
        }
        
        .btn-success {
            background-color: #10b981;
            color: #ffffff;
        }
        
        .btn-success:hover {
            background-color: #059669;
        }
        
        .btn-danger {
            background-color: #ef4444;
            color: #ffffff;
        }
        
        .btn-danger:hover {
            background-color: #dc2626;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 1px solid currentColor;
        }
        
        .btn-outline-primary {
            color: #2563eb;
            border-color: #2563eb;
        }
        
        .btn-outline-primary:hover {
            background-color: #eff6ff;
        }
        
        /* Tables */
        .table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }
        
        .table th {
            padding: 0.75rem 1.5rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            background-color: #f9fafb;
            text-align: left;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table td {
            padding: 1rem 1.5rem;
            font-size: 0.875rem;
            color: #4b5563;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .table tr:last-child td {
            border-bottom: none;
        }
        
        .table-hover tr:hover td {
            background-color: #f9fafb;
        }
        
        /* Cards */
        .card {
            background-color: #ffffff;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            overflow: hidden;
        }
        
        .card-header {
            padding: 1rem 1.5rem;
            background-color: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
        }
        
        .card-body {
            padding: 1.5rem;
        }
        
        .card-footer {
            padding: 1rem 1.5rem;
            background-color: #f9fafb;
            border-top: 1px solid #e5e7eb;
        }
        
        /* Badges */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 0.125rem 0.625rem;
            font-size: 0.75rem;
            font-weight: 600;
            border-radius: 9999px;
        }
        
        .badge-success {
            background-color: #d1fae5;
            color: #059669;
        }
        
        .badge-warning {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .badge-danger {
            background-color: #fee2e2;
            color: #dc2626;
        }
        

        /* Responsive */
        @media (min-width: 640px) {
            .sm\:flex {
                display: flex;
            }
            
            .sm\:hidden {
                display: none;
            }
            
            .sm\:flex-row {
                flex-direction: row;
            }
            
            .sm\:w-auto {
                width: auto;
            }
            
            .sm\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        
        @media (min-width: 768px) {
            .md\:flex {
                display: flex;
            }
            
            .md\:hidden {
                display: none;
            }
            
            .md\:grid-cols-2 {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
            
            .md\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            
            .container {
                max-width: 768px;
            }
        }
        
        @media (min-width: 1024px) {
            .lg\:flex {
                display: flex;
            }
            
            .lg\:hidden {
                display: none;
            }
            
            .lg\:grid-cols-3 {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }
            
            .lg\:grid-cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
            
            .container {
                max-width: 1024px;
            }
        }
        
        @media (min-width: 1280px) {
            .xl\:grid-cols-4 {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
            
            .container {
                max-width: 1280px;
            }
        }
    </style>
    <div class="flex min-h-screen">
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Main Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100">
                <div class="container mx-auto px-6 py-8">
                    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Dashboard</h1>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                        <div class="bg-white rounded-lg shadow p-6 flex items-center">
                            <div class="rounded-full bg-blue-100 p-3 mr-4">
                                <i class="fas fa-users text-blue-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Total Clientes</p>
                                <p class="text-2xl font-bold text-gray-800">2,543</p>
                                <p class="text-green-500 text-sm"><i class="fas fa-arrow-up"></i> 12.5% desde el mes pasado</p>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6 flex items-center">
                            <div class="rounded-full bg-green-100 p-3 mr-4">
                                <i class="fas fa-file-invoice text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Proformas</p>
                                <p class="text-2xl font-bold text-gray-800">1,235</p>
                                <p class="text-green-500 text-sm"><i class="fas fa-arrow-up"></i> 8.3% desde el mes pasado</p>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6 flex items-center">
                            <div class="rounded-full bg-yellow-100 p-3 mr-4">
                                <i class="fas fa-dollar-sign text-yellow-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Ingresos</p>
                                <p class="text-2xl font-bold text-gray-800">$125,430</p>
                                <p class="text-red-500 text-sm"><i class="fas fa-arrow-down"></i> 2.4% desde el mes pasado</p>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-lg shadow p-6 flex items-center">
                            <div class="rounded-full bg-purple-100 p-3 mr-4">
                                <i class="fas fa-boxes text-purple-600 text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500 text-sm">Productos</p>
                                <p class="text-2xl font-bold text-gray-800">543</p>
                                <p class="text-green-500 text-sm"><i class="fas fa-arrow-up"></i> 5.1% desde el mes pasado</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts and Tables -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2">
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-chart-line text-blue-600 mr-3"></i>
                                    <h3 class="text-lg font-medium text-gray-800">Ventas Mensuales</h3>
                                </div>
                                <div class="card-body">
                                    <div class="h-80">
                                        <canvas id="salesChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <div class="card">
                                <div class="card-header">
                                    <i class="fas fa-users text-blue-600 mr-3"></i>
                                    <h3 class="text-lg font-medium text-gray-800">Clientes Recientes</h3>
                                </div>
                                <div class="card-body">
                                    <div class="space-y-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">Juan Pérez</p>
                                                <p class="text-sm text-gray-500">juan@ejemplo.com</p>
                                            </div>
                                            <div class="ml-auto text-sm text-gray-500">
                                                5 min
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">María García</p>
                                                <p class="text-sm text-gray-500">maria@ejemplo.com</p>
                                            </div>
                                            <div class="ml-auto text-sm text-gray-500">
                                                15 min
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">Carlos López</p>
                                                <p class="text-sm text-gray-500">carlos@ejemplo.com</p>
                                            </div>
                                            <div class="ml-auto text-sm text-gray-500">
                                                1 hora
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">Ana Martínez</p>
                                                <p class="text-sm text-gray-500">ana@ejemplo.com</p>
                                            </div>
                                            <div class="ml-auto text-sm text-gray-500">
                                                3 horas
                                            </div>
                                        </div>
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                                <i class="fas fa-user"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">Roberto Sánchez</p>
                                                <p class="text-sm text-gray-500">roberto@ejemplo.com</p>
                                            </div>
                                            <div class="ml-auto text-sm text-gray-500">
                                                5 horas
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Orders -->
                    <div class="mt-8">
                        <div class="card">
                            <div class="card-header">
                                <i class="fas fa-file-invoice text-blue-600 mr-3"></i>
                                <h3 class="text-lg font-medium text-gray-800">Proformas Recientes</h3>
                            </div>
                            <div class="card-body overflow-x-auto">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th># Proforma</th>
                                            <th>Cliente</th>
                                            <th>Fecha</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="font-medium text-gray-900">PRO-2023-1001</td>
                                            <td>
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">Juan Pérez</div>
                                                        <div class="text-sm text-gray-500">juan@ejemplo.com</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>15/04/2023</td>
                                            <td>$3,500.00</td>
                                            <td>
                                                <span class="badge badge-success">Aprobada</span>
                                            </td>
                                            <td>
                                                <div class="flex space-x-2">
                                                    <a href="#" class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="#" class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-medium text-gray-900">PRO-2023-1002</td>
                                            <td>
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">María García</div>
                                                        <div class="text-sm text-gray-500">maria@ejemplo.com</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>14/04/2023</td>
                                            <td>$2,100.00</td>
                                            <td>
                                                <span class="badge badge-warning">Pendiente</span>
                                            </td>
                                            <td>
                                                <div class="flex space-x-2">
                                                    <a href="#" class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="#" class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-medium text-gray-900">PRO-2023-1003</td>
                                            <td>
                                                <div class="flex items-center">
                                                    <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900">Carlos López</div>
                                                        <div class="text-sm text-gray-500">carlos@ejemplo.com</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>13/04/2023</td>
                                            <td>$5,200.00</td>
                                            <td>
                                                <span class="badge badge-danger">Rechazada</span>
                                            </td>
                                            <td>
                                                <div class="flex space-x-2">
                                                    <a href="#" class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="#" class="text-blue-600 hover:text-blue-900">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="#" class="text-red-600 hover:text-red-900">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="card-footer">
                                <a href="#" class="text-blue-600 hover:text-blue-800 font-medium">Ver todas las proformas →</a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
@endsection