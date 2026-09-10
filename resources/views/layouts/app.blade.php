<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    <link rel="dns-prefetch" href="//fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        <nav class="navbar navbar-dark fixed-top" style="background: linear-gradient(135deg, #1e3a5f 0%, #0f172a 100%);">
            <div class="container-fluid">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn btn-sm btn-outline-light d-lg-none" id="sidebar-toggle" type="button">
                        <i class="bi bi-list"></i>
                    </button>
                    <a class="navbar-brand d-flex align-items-center gap-2" href="{{ url('/') }}">
                        <span style="font-size:1.3rem;">&#9670;</span>
                        {{ config('app.name', 'Laravel') }}
                    </a>
                </div>

                <ul class="navbar-nav ms-auto flex-row align-items-center gap-2">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('login') }}">Iniciar sesion</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item d-none d-md-flex align-items-center gap-2">
                            <span class="text-light small">{{ Auth::user()->name }}</span>
                            <span class="badge" style="background:rgba(255,255,255,0.15);color:#fff;">{{ ucfirst(Auth::user()->role) }}</span>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-sm" style="background:rgba(255,255,255,0.1);color:#fff;border:1px solid rgba(255,255,255,0.2);"
                               href="{{ route('logout') }}"
                               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                Salir
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </li>
                    @endguest
                </ul>
            </div>
        </nav>

        @auth
        <div class="sidebar-overlay" id="sidebar-overlay"></div>
        <aside class="sidebar" id="sidebar">
            <ul class="sidebar-nav">
                @if (Auth::user()->isAdmin())
                    <li class="sidebar-section">Principal</li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                            <span class="sidebar-icon">&#9632;</span> Dashboard
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('ventas'))
                    <li class="sidebar-section">Operaciones</li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}" href="{{ route('ventas.pedidos') }}">
                            <span class="sidebar-icon">&#9993;</span> Pedidos
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('almacen'))
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('almacen.*') ? 'active' : '' }}" href="{{ route('almacen.pendientes') }}">
                            <span class="sidebar-icon">&#9881;</span> Almacen
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('logistica'))
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('embarques.*') ? 'active' : '' }}" href="{{ route('embarques.index') }}">
                            <span class="sidebar-icon">&#9654;</span> Embarques
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('transporte'))
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('viajes.*') ? 'active' : '' }}" href="{{ route('viajes.index') }}">
                            <span class="sidebar-icon">&#9992;</span> Viajes
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('tienda'))
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('tienda.*') ? 'active' : '' }}" href="{{ route('tienda.recepciones') }}">
                            <span class="sidebar-icon">&#10003;</span> Recepcion
                        </a>
                    </li>
                @endif

                @if (Auth::user()->hasRole('tienda') || Auth::user()->hasRole('almacen'))
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('devoluciones.*') ? 'active' : '' }}" href="{{ route('devoluciones.index') }}">
                            <span class="sidebar-icon">&#8634;</span> Devoluciones
                        </a>
                    </li>
                @endif

                @if (in_array(Auth::user()->role, ['admin', 'ventas', 'almacen', 'logistica']))
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('trazabilidad.*') ? 'active' : '' }}" href="{{ route('trazabilidad.index') }}">
                            <span class="sidebar-icon">&#8982;</span> Trazabilidad
                        </a>
                    </li>
                @endif

                @if (Auth::user()->isAdmin())
                    <li class="sidebar-section">Catalogo</li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('presentaciones.*') ? 'active' : '' }}" href="{{ route('presentaciones.index') }}">
                            <span class="sidebar-icon">&#9733;</span> Presentaciones
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('tiendas.*') ? 'active' : '' }}" href="{{ route('tiendas.index') }}">
                            <span class="sidebar-icon">&#9962;</span> Tiendas
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('almacenes.*') ? 'active' : '' }}" href="{{ route('almacenes.index') }}">
                            <span class="sidebar-icon">&#9878;</span> Almacenes
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('vehiculos.*') ? 'active' : '' }}" href="{{ route('vehiculos.index') }}">
                            <span class="sidebar-icon">&#9650;</span> Vehiculos
                        </a>
                    </li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">
                            <span class="sidebar-icon">&#9823;</span> Clientes
                        </a>
                    </li>

                    <li class="sidebar-section">Reportes</li>
                    <li class="sidebar-item">
                        <a class="sidebar-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}">
                            <span class="sidebar-icon">&#9783;</span> Reportes
                        </a>
                    </li>
                @endif
            </ul>
        </aside>
        @endauth

        <main class="main-content pt-5 pb-4" style="padding-top: 70px !important;">
            @yield('content')
        </main>
    </div>
    @yield('scripts')
</body>
</html>
