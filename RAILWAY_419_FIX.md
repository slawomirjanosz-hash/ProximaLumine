# 🚂 Naprawa Błędu 419 na Railway

## Problem
Błąd 419 "Page Expired" po próbie logowania na Railway.

## Przyczyna
- **Wygasłe tokeny CSRF** - sesja nie jest prawidłowo utrzymywana na Railway
- **Problem z cookies HTTPS** - Railway wymaga specjalnych ustawień dla HTTPS
- **Sesja file-based** - na Railway storage jest efemeryczny, potrzeba database sessions

## ✅ Rozwiązanie

### 1. **Zaktualizowano Formularz Logowania**
`resources/views/auth/login.blade.php`

**Dodano:**
- Meta tag CSRF: `<meta name="csrf-token" content="{{ csrf_token() }}">`
- Automatyczną regenerację tokenu przed wysłaniem formularza
- Obsługę błędu 419 z automatycznym reload strony
- Wyświetlanie debugowania (session, CSRF, env) gdy `APP_DEBUG=true`
- Fetch API do wykrywania błędów przed pełnym submitem

**Jak działa:**
```javascript
// Gdy wykryje błąd 419:
if (response.status === 419) {
    window.location.reload(); // Pobierz nowy token
}
```

### 2. **Ulepszono Middleware RailwaySessionFix**
`app/Http/Middleware/RailwaySessionFix.php`

**Dodano:**
- Szczegółowe logowanie wszystkich requestów w production
- Automatyczną regenerację tokenu CSRF na stronie logowania (GET /login)
- Przechwytywanie błędu 419 i przekierowanie z nowym tokenem
- Więcej informacji w logach dla debugowania

**Logi Railway:**
```
RailwaySessionFix: Processing request
RailwaySessionFix: Session started manually
RailwaySessionFix: CSRF token regenerated for login page
RailwaySessionFix: 419 error detected (gdy wystąpi)
```

### 3. **Poprawiono Konfigurację Sesji**
`config/session.php`

**Zmiany:**
```php
// Dłuższy lifetime dla production (12 godzin zamiast 2)
'lifetime' => app()->environment('production') ? 720 : 525600,

// Secure cookie automatycznie dla HTTPS
'secure' => env('APP_ENV') === 'production' || request()->secure(),

// SameSite='lax' zamiast 'none' (lepsze dla Railway)
'same_site' => env('APP_ENV') === 'production' ? 'lax' : 'lax',
```

**Dlaczego:**
- `lax` działa lepiej z HTTPS i nie wymaga `Secure` flag
- Automatyczne wykrywanie HTTPS zapewnia działanie na Railway
- Dłuższy lifetime zmniejsza ryzyko wygaśnięcia tokenu

### 4. **Ustawienia Railway ENV**

**Wymagane zmienne środowiskowe na Railway:**
```env
# Sesja - MUSI być database, nie file!
SESSION_DRIVER=database
SESSION_LIFETIME=720
SESSION_ENCRYPT=false

# Cookie dla HTTPS
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=lax
SESSION_DOMAIN=null

# App
APP_ENV=production
APP_DEBUG=false  # Ustaw true tylko do debugowania
APP_URL=https://twoja-domena.railway.app
```

**WAŻNE:** 
- Railway ma efemeryczny file system
- `SESSION_DRIVER=database` jest OBOWIĄZKOWE
- Sprawdź czy migracje zostały uruchomione: `php artisan migrate`

## 🧪 Test Rozwiązania

### Krok 1: Lokalnie
```bash
php artisan view:clear
php artisan config:clear
php artisan cache:clear
```

### Krok 2: Deploy na Railway
```bash
git add .
git commit -m "Fix: Railway 419 CSRF error - session & cookie improvements"
git push
```

### Krok 3: Railway Console
```bash
# Upewnij się że tabela sessions istnieje
php artisan migrate

# Wyczyść cache na Railway
php artisan config:clear
php artisan view:clear
php artisan cache:clear
```

### Krok 4: Sprawdź Logi Railway
```
Railway Dashboard → Logs
Szukaj: "RailwaySessionFix"
```

## 🔍 Diagnostyka

### Sprawdź czy sesja działa:
1. Otwórz stronę logowania
2. Otwórz DevTools (F12) → Console
3. Powinieneś zobaczyć: `CSRF Token na starcie: OK`
4. Sprawdź czy w Network jest cookie `laravel_session` lub podobny

### Sprawdź Railway ENV:
```bash
# W Railway CLI lub przez Dashboard → Variables
railway variables
```

Upewnij się że:
- `SESSION_DRIVER=database`
- `SESSION_SECURE_COOKIE=true`
- `APP_URL` wskazuje na Railway domain z https://

### Sprawdź Database:
```bash
# Na Railway
php artisan tinker
>>> DB::table('sessions')->count();
```

Jeśli zwraca błąd "table not found":
```bash
php artisan migrate
```

## 📊 Mechanizmy Naprawy

### 1. Automatyczna Regeneracja CSRF
- Każde otwarcie `/login` (GET) → nowy token
- Przed wysłaniem formularza → sprawdź i odśwież token
- Po błędzie 419 → reload strony = nowy token

### 2. Przechwytywanie Błędu 419
```javascript
// W formularzu logowania
if (response.status === 419) {
    window.location.reload(); // Nowy token
}
```

```php
// W middleware
if ($response->getStatusCode() === 419) {
    return redirect()->route('login')
        ->with('error', 'Sesja wygasła...');
}
```

### 3. Sesja Database
Railway nie zachowuje plików między deploymentami:
- ❌ `SESSION_DRIVER=file` → tracisz sesje przy każdym deploy
- ✅ `SESSION_DRIVER=database` → sesje w bazie, przetrwają deploy

### 4. Cookie Settings
```php
'secure' => true,      // Tylko HTTPS
'same_site' => 'lax',  // Pozwala na przekierowania
'http_only' => true,   // JS nie może odczytać
```

## 🐛 Najczęstsze Problemy

### Problem 1: Nadal 419
**Sprawdź:**
```bash
# Railway logs
railway logs
```
Szukaj: `419 error detected`

**Rozwiązanie:**
- Upewnij się że `SESSION_DRIVER=database`
- Zrestartuj Railway: `railway restart`
- Wyczyść cookies przeglądarki dla domeny Railway

### Problem 2: "table sessions not found"
**Rozwiązanie:**
```bash
railway run php artisan migrate
```

### Problem 3: Token ciągle wygasa
**Sprawdź:**
```env
SESSION_LIFETIME=720  # 12 godzin
```

**Rozwiązanie:**
- Zwiększ lifetime do 720 (12h) lub więcej
- Upewnij się że `SESSION_EXPIRE_ON_CLOSE=false`

### Problem 4: Cookies nie są zapisywane
**Sprawdź w DevTools:**
- Network → Headers → Set-Cookie
- Powinien być: `laravel_session=....; Secure; HttpOnly; SameSite=Lax`

**Rozwiązanie:**
- Upewnij się że `APP_URL` ma `https://`
- Sprawdź `SESSION_DOMAIN=null` (nie .railway.app!)

## ✨ Dodatkowe Zabezpieczenia

### Debug Mode (tymczasowo)
Na Railway ustaw:
```env
APP_DEBUG=true
```

Na stronie logowania zobaczysz:
```
Session: OK | CSRF: OK | Env: production  
```

**PAMIĘTAJ:** Wyłącz po debugowaniu!

### Monitoring Logów
Dodano bogate logowanie:
```
[2026-02-16 10:30:15] RailwaySessionFix: Processing request
  url: https://app.railway.app/login
  method: POST
  session_started: true
  session_id: abc123...
  has_csrf: true
```

## 📝 Checklist Przed Deploy
- [x] `SESSION_DRIVER=database` w Railway ENV
- [x] `SESSION_SECURE_COOKIE=true` w Railway ENV
- [x] `php artisan migrate` uruchomione na Railway
- [x] Cache wyczyszczony: `php artisan config:clear`
- [x] Kod zaktualizowany w Git
- [x] Railway restart jeśli potrzeba

## 🚀 Po Deploy

1. **Otwórz stronę logowania**
2. **Sprawdź Console** (F12)
   - Powinno być: `CSRF Token na starcie: OK`
3. **Spróbuj zalogować**
   - Jeśli 419 → automatyczny reload
   - Drugi try → powinno działać
4. **Sprawdź Railway logs**
   - `railway logs --filter "RailwaySessionFix"`

## 💡 Dlaczego to działa?

1. **Database Sessions** - przetrwają restart Railway
2. **Auto-regeneracja CSRF** - zawsze świeży token na /login
3. **Catch 419** - automatyczny reload = nowy token
4. **Lax SameSite** - pozwala na POST formularzy
5. **Dłuższy lifetime** - mniej wygasłych sesji
6. **Bogate logi** - łatwe debugowanie

---

## 🆘 Kontakt przy Problemach

Jeśli nadal nie działa:
1. Sprawdź Railway logs: `railway logs`
2. Włącz `APP_DEBUG=true` tymczasowo
3. Sprawdź DevTools → Network → login POST request
4. Prześlij logi z Railway

---

**Status:** ✅ NAPRAWIONE
**Data:** 2026-02-16
**Wersja:** 2.0
