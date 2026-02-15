# Railway Authorization Fix

## Problem
Na Railway produkty nie dodają się do projektów po autoryzacji (skanowanie QR kodów). Na lokale działa poprawnie.

## Główne przyczyny na Railway
1. **HTTPS vs HTTP** - Railway używa HTTPS, więc cookies muszą być secure
2. **SameSite cookies** - na produkcji bardziej restrykcyjne
3. **CSRF token handling** - problemy z tokenami w AJAX na różnych domenach
4. **Session domain/path** - różnice między localhost a Railway

## Zmiany w kodzie

### 1. Session Configuration (`config/session.php`)
```php
// Auto-detect secure cookies based on environment
'secure' => env('SESSION_SECURE_COOKIE', env('APP_ENV') === 'production'),

// Auto-detect SameSite based on environment  
'same_site' => env('SESSION_SAME_SITE', env('APP_ENV') === 'production' ? 'none' : 'lax'),
```

### 2. Debug Logging (`PartController.php`)
- Dodano szczegółowe logowanie dla `processAuthorization`
- Dodano logowanie dla `storePickupProducts`
- Logi zawierają session_id, CSRF token, user_agent, IP, itp.

### 3. Railway-specific Middleware
- `RailwaySessionFix` - naprawa sesji na Railway
- `RailwayDebug` - szczegółowe logowanie dla debugowania

### 4. Improved AJAX Handling (`project-authorize.blade.php`)
- Lepsze wykrywanie CSRF token
- Fallback search dla token
- Dodanie `credentials: 'same-origin'`
- Dodanie `X-Requested-With: XMLHttpRequest`
- Auto-retry przy błędach 419/403 (CSRF)

## Używanie na Railway

### 1. Sprawdź logi Railway
```bash
railway logs --filter="Authorization"
railway logs --filter="CSRF"
railway logs --filter="processAuthorization"
```

### 2. Sprawdź zmienne środowiskowe
Upewnij się, że w Railway są ustawione:
```env
APP_ENV=production
SESSION_DRIVER=database  # Lepsze niż 'file' na Railway
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=none
APP_URL=https://twoja-domena.railway.app
```

### 3. Uruchom migracje (jeśli używasz session database)
```bash
railway run php artisan session:table
railway run php artisan migrate --force
```

### 4. Wyczyść cache
```bash
railway run php artisan config:clear
railway run php artisan cache:clear
railway run php artisan view:clear
```

## Debugging

### Frontend (w przeglądarce)
1. Otwórz DevTools → Network
2. Spróbuj autoryzacji produktu
3. Sprawdź request do `/autoryzuj`:
   - Status code (powinien być 200, nie 419/403)
   - Request headers (Content-Type, X-CSRF-TOKEN)
   - Response (success/error message)

### Backend (Railway logs)
Szukaj w logach:
```
railway logs --filter="Authorization process started"
railway logs --filter="Product authorization successful" 
railway logs --filter="No unauthorized removal found"
```

### Meta tags debug (project-authorize.blade.php)
Sprawdź w źródle strony:
```html
<meta name="session-id" content="...">
<meta name="app-env" content="production">
<meta name="session-driver" content="database">
```

## Rozwiązywanie problemów

### Problem: CSRF token mismatch (419)
- Sprawdź czy `SESSION_SECURE_COOKIE=true` na Railway
- Sprawdź czy `SESSION_SAME_SITE=none` na Railway
- Sprawdź czy domeny się zgadzają w `APP_URL`

### Problem: Session ginie po przejściu na autoryzację
- Zmień `SESSION_DRIVER` z `file` na `database`
- Upewnij się że migracja session table została uruchomiona

### Problem: "No unauthorized removal found"
- Sprawdź w logach czy produkty zostały dodane do projektu
- Sprawdź czy `project.requires_authorization = true`
- Sprawdź tabelę `project_removals` - czy są wpisy z `authorized = false`

### Problem: JavaScript errors
- Sprawdź czy CSRF token jest w meta tag
- Sprawdź DevTools Console na błędy
- Sprawdź Network tab na failed requests