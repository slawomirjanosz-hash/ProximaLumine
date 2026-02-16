<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Logowanie - Magazyn</title>
    @vite('resources/css/app.css')
    <link rel="icon" type="image/png" href="{{ asset('logo_proxima_male.png') }}">
    <style>
        .debug-info { position: fixed; bottom: 10px; right: 10px; background: #f3f4f6; padding: 8px; border-radius: 4px; font-size: 10px; opacity: 0.7; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800">📦 Magazyn</h1>
                <p class="text-gray-600 mt-2">System zarządzania zapasami</p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6">
                    @foreach ($errors->all() as $error)
                        <p class="text-red-700 text-sm">{{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-gray-700 font-medium mb-2">Email</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email"
                        value="{{ old('email') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="twoj@email.com"
                        required
                    >
                </div>

                <div>
                    <label for="password" class="block text-gray-700 font-medium mb-2">Hasło (opcjonalne)</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Twoje hasło lub pozostaw puste"
                    >
                </div>

                <div class="flex items-center">
                    <input 
                        type="checkbox" 
                        id="remember" 
                        name="remember"
                        class="h-4 w-4 text-blue-600 border-gray-300 rounded"
                    >
                    <label for="remember" class="ml-2 text-gray-700 text-sm">
                        Zapamiętaj mnie
                    </label>
                </div>

                <button 
                    type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-200"
                >
                    Zaloguj się
                </button>
            </form>
            
            @if(config('app.debug'))
            <div class="mt-4 text-xs text-gray-500 text-center">
                Session: {{ session()->getId() ? 'OK' : 'BRAK' }} | 
                CSRF: {{ csrf_token() ? 'OK' : 'BRAK' }} | 
                Env: {{ app()->environment() }}
            </div>
            @endif
        </div>
    </div>
    
    <script>
        // Automatyczna regeneracja tokenu CSRF przed wysłaniem formularza
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            // Sprawdź czy token istnieje
            let csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF Token na starcie:', csrfToken ? 'OK' : 'BRAK');
            
            form.addEventListener('submit', function(e) {
                // Odśwież token przed wysłaniem
                const tokenInput = form.querySelector('input[name="_token"]');
                const metaToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                
                if (tokenInput && metaToken) {
                    tokenInput.value = metaToken;
                    console.log('Token CSRF odświeżony przed wysłaniem');
                }
            });
            
            // Nasłuchuj błędu 419 i automatycznie przeładuj stronę
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (response.status === 419) {
                        console.warn('Błąd 419 - sesja wygasła, przeładowuję stronę...');
                        // Przeładuj stronę aby uzyskać nowy token
                        window.location.reload();
                        return;
                    }
                    
                    // Jeśli sukces, przekieruj manualnie
                    if (response.ok || response.redirected) {
                        window.location.href = response.url || '/magazyn';
                    } else {
                        // Dla innych błędów, pozwól formie działać normalnie
                        form.submit();
                    }
                } catch (error) {
                    console.error('Błąd logowania:', error);
                    // Fallback - normalnie wyślij formularz
                    form.submit();
                }
            });
        });
    </script>
</body>
</html>
