<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center"
         style="background-color: var(--rf-bg);">

        <div class="w-full max-w-md">

            <!-- Card -->
            <div class="rounded-xl shadow-sm border p-8"
                 style="background: var(--rf-white); border-color: var(--rf-border);">

                <!-- Logo -->
                <div class="flex justify-center mb-6">
                    <img src="{{ asset('images/refoodlogo.png') }}"
                         alt="REFOOD"
                         class="h-16">
                </div>

                <!-- Session Status -->
                <x-auth-session-status class="mb-4 text-sm"
                    :status="session('status')" />

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email -->
                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input
                            id="email"
                            class="block mt-1 w-full"
                            type="email"
                            name="email"
                            :value="old('email')"
                            required
                            autofocus
                            autocomplete="username" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <!-- Password -->
                    <div class="mt-4">
                        <x-input-label for="password" value="Contraseña" />
                        <x-text-input
                            id="password"
                            class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <!-- Remember -->
                    <div class="flex items-center justify-between mt-4 text-sm">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me"
                                   type="checkbox"
                                   class="rounded border-gray-300 shadow-sm"
                                   name="remember">
                            <span class="ms-2" style="color: var(--rf-text);">
                                Recordarme
                            </span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}"
                               class="underline"
                               style="color: var(--rf-primary);">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <!-- Button -->
                    <div class="mt-6">
                        <button type="submit"
                                class="w-full py-2 px-4 rounded-lg text-white font-medium transition"
                                style="background: var(--rf-primary);">
                            Ingresar
                        </button>
                    </div>
                </form>

            </div>

            <!-- Footer -->
            <p class="text-center text-xs mt-4" style="color: var(--rf-text);">
                © {{ date('Y') }} REFOOD · Sistema de Gestión Gastronómica
            </p>

        </div>
    </div>
</x-guest-layout>
