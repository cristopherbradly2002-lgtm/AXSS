<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AXSS')</title>
    {{-- Estilos: CDN para desarrollo; reemplazar con @vite para producción --}}
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

    {{-- Navbar --}}
    <nav class="bg-blue-900 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center justify-between h-16">
            <div class="flex items-center gap-4">
                <a href="/" class="text-yellow-400 font-extrabold text-xl tracking-widest">AXSS</a>
                @auth
                    @if(Auth::user()->isAdmin())
                        <div class="hidden sm:flex items-center gap-2 text-sm">
                            <a href="{{ route('admin.dashboard') }}" class="text-white/80 hover:text-white transition">Panel</a>
                            <span class="text-white/30">|</span>
                            <a href="{{ route('admin.users') }}" class="text-white/80 hover:text-white transition">Usuarios</a>
                            <span class="text-white/30">|</span>
                            <a href="{{ route('admin.courses') }}" class="text-white/80 hover:text-white transition">Cursos</a>
                            <span class="text-white/30">|</span>
                            <a href="{{ route('admin.classrooms') }}" class="text-white/80 hover:text-white transition">Salones</a>
                            <span class="text-white/30">|</span>
                            <a href="{{ route('admin.schedules') }}" class="text-white/80 hover:text-white transition">Horarios</a>
                            <span class="text-white/30">|</span>
                            <a href="{{ route('admin.attendance-report') }}" class="text-white/80 hover:text-white transition">Asistencia</a>
                        </div>
                    @endif
                @endauth
            </div>
            <div class="flex items-center gap-4">
                <span class="text-white/80 text-sm hidden sm:block">
                    {{ Auth::user()->name }}
                    @php
                        $roleBg = match(Auth::user()->role) {
                            'admin' => 'bg-red-600 text-white',
                            'maestro' => 'bg-blue-700 text-yellow-300',
                            default => 'bg-green-700 text-white',
                        };
                    @endphp
                    <span class="ml-1 text-xs {{ $roleBg }} px-2 py-0.5 rounded-full uppercase font-semibold">
                        {{ Auth::user()->role }}
                    </span>
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="text-sm bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded transition">
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- Impersonation banner --}}
    @if(session('admin_impersonator_id'))
        <div class="bg-yellow-400 text-blue-900 text-center text-sm font-bold py-2">
            Estás viendo como <strong>{{ Auth::user()->name }}</strong> ({{ Auth::user()->role }}).
            <a href="{{ route('admin.stop-impersonate') }}" class="underline ml-2 hover:text-blue-700">Volver a Admin</a>
        </div>
    @endif

    {{-- Flash messages --}}
    <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4 flex items-center gap-2">
                <svg class="w-5 h-5 text-green-600 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
                <ul class="list-disc list-inside space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Page content --}}
    <main class="flex-1 max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    {{-- Footer --}}
    <footer class="bg-blue-900 text-white/50 text-center text-xs py-3 mt-auto">
        © {{ date('Y') }} AXSS. Todos los derechos reservados.
    </footer>

    {{-- Scripts stack for pages --}}
    @stack('scripts')

</body>
</html>
