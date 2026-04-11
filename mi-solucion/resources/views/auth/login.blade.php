<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión – AXSS</title>
    <script src="https://unpkg.com/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gradient-to-br from-blue-900 to-blue-700 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        {{-- Logo / Header --}}
        <div class="text-center mb-8">
            <div class="inline-block bg-white rounded-2xl px-6 py-4 shadow-xl mb-4">
                <span class="text-blue-900 font-extrabold text-4xl tracking-widest">AXSS</span>
            </div>
            <p class="text-blue-200 text-sm mt-1">Plataforma Educativa</p>
        </div>

        {{-- Card --}}
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <h2 class="text-blue-900 text-2xl font-bold mb-6 text-center">Iniciar Sesión</h2>

            {{-- Errors --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-300 text-red-700 text-sm rounded-lg px-4 py-3 mb-5">
                    @foreach ($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Correo electrónico
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email') }}"
                        required
                        autofocus
                        placeholder="correo@ejemplo.com"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        placeholder="••••••••"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                    >
                </div>

                {{-- Remember --}}
                <div class="flex items-center gap-2">
                    <input type="checkbox" id="remember" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <label for="remember" class="text-sm text-gray-600">Recordar sesión</label>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-3 rounded-lg transition text-sm tracking-wide"
                >
                    Entrar
                </button>
            </form>

            {{-- Roles info --}}
            <div class="mt-6 border-t pt-5">
                <p class="text-xs text-gray-500 text-center font-medium mb-3">Cuentas de prueba</p>
                <div class="grid grid-cols-3 gap-3 text-xs text-gray-600">
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="font-semibold text-red-800 mb-1">🛡️ Admin</p>
                        <p>admin@axss.edu</p>
                        <p class="text-gray-400">password</p>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-3">
                        <p class="font-semibold text-blue-800 mb-1">👨‍🏫 Maestro</p>
                        <p>maestro@axss.edu</p>
                        <p class="text-gray-400">password</p>
                    </div>
                    <div class="bg-indigo-50 rounded-lg p-3">
                        <p class="font-semibold text-indigo-800 mb-1">👨‍🎓 Alumno</p>
                        <p>alumno1@axss.edu</p>
                        <p class="text-gray-400">password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
