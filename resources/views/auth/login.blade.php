<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-10"
         style="background: linear-gradient(135deg, #fff7ed 0%, #f0fdf4 100%);">

        <div class="w-full max-w-md">

            <!-- Header -->
            <div class="text-center mb-6">
                <img src="{{ asset('images/refoodlogo.png') }}"
                     alt="REFOOD"
                     class="h-16 mx-auto">

                <h1 class="mt-4 text-2xl font-bold text-gray-800">
                    Iniciar sesión
                </h1>

                <p class="text-sm text-gray-500">
                    Sistema de Gestión Gastronómica
                </p>
            </div>

            <!-- Card -->
            <div class="rounded-2xl shadow-xl border bg-white p-8">

                <x-auth-session-status class="mb-4 text-sm"
                    :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input
                            id="email"
                            class="block mt-1 w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex justify-between items-center">
                            <x-input-label for="password" value="Contraseña" />

                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}"
                                   class="text-xs text-orange-600 hover:text-orange-700 font-medium">
                                    ¿Olvidaste tu contraseña?
                                </a>
                            @endif
                        </div>

                        <x-text-input
                            id="password"
                            class="block mt-1 w-full rounded-lg border-gray-300 focus:border-orange-500 focus:ring-orange-500"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember -->
                    <div class="flex items-center text-sm">
                        <input id="remember_me"
                               type="checkbox"
                               class="rounded border-gray-300 text-orange-600 focus:ring-orange-500"
                               name="remember">
                        <label for="remember_me" class="ml-2 text-gray-600">
                            Recordarme
                        </label>
                    </div>

                    <!-- Button -->
                    <div>
                        <button type="submit"
                                class="w-full py-3 rounded-lg font-semibold text-white text-base transition duration-200 shadow-md hover:shadow-lg hover:scale-[1.01]"
                                style="background-color: #f97316;">
                            Ingresar
                        </button>
                    </div>

                </form>
            </div>

            <!-- Footer -->
            <p class="text-center text-xs mt-6 text-gray-500">
                © {{ date('Y') }} REFOOD · Gestión Gastronómica
            </p>

        </div>
    </div>
</x-guest-layout>
