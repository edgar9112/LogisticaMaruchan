@auth
    <ul class="navbar-nav me-auto">
        @if (Auth::user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
            </li>
        @endif
        @if (Auth::user()->hasRole('ventas'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('ventas.*') ? 'active' : '' }}" href="{{ route('ventas.pedidos') }}">Pedidos</a>
            </li>
        @endif
        @if (Auth::user()->hasRole('almacen'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('almacen.*') ? 'active' : '' }}" href="{{ route('almacen.pendientes') }}">Almacén</a>
            </li>
        @endif
        @if (Auth::user()->hasRole('logistica'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('embarques.*') ? 'active' : '' }}" href="{{ route('embarques.index') }}">Embarques</a>
            </li>
        @endif
        @if (Auth::user()->hasRole('transporte'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('viajes.*') ? 'active' : '' }}" href="{{ route('viajes.index') }}">Viajes</a>
            </li>
        @endif
        @if (Auth::user()->hasRole('tienda'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('tienda.*') ? 'active' : '' }}" href="{{ route('tienda.recepciones') }}">Recepción</a>
            </li>
        @endif
        @if (Auth::user()->hasRole('tienda') || Auth::user()->hasRole('almacen'))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('devoluciones.*') ? 'active' : '' }}" href="{{ route('devoluciones.index') }}">Devoluciones</a>
            </li>
        @endif
        @if (in_array(Auth::user()->role, ['admin', 'ventas', 'almacen', 'logistica']))
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('trazabilidad.*') ? 'active' : '' }}" href="{{ route('trazabilidad.index') }}">Trazabilidad</a>
            </li>
        @endif
        @if (Auth::user()->isAdmin())
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('presentaciones.*') ? 'active' : '' }}" href="{{ route('presentaciones.index') }}">Catálogo</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('tiendas.*') ? 'active' : '' }}" href="{{ route('tiendas.index') }}">Tiendas</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('almacenes.*') ? 'active' : '' }}" href="{{ route('almacenes.index') }}">Almacenes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('vehiculos.*') ? 'active' : '' }}" href="{{ route('vehiculos.index') }}">Vehículos</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('clientes.*') ? 'active' : '' }}" href="{{ route('clientes.index') }}">Clientes</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('reportes.*') ? 'active' : '' }}" href="{{ route('reportes.index') }}">Reportes</a>
            </li>
        @endif
    </ul>
@endauth