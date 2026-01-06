# Instrukcje aktualizacji na Railway

## Aktualizacja użytkownika admin

Na Railway należy uruchomić skrypt aktualizacji użytkownika admin.

### Metoda 1: Przez Railway CLI

```bash
# Uruchom skrypt PHP
php scripts/update_admin_user.php
```

### Metoda 2: Przez bazę danych MySQL (Railway Dashboard)

1. Otwórz Railway Dashboard
2. Przejdź do bazy danych MySQL
3. Otwórz Query Editor
4. Skopiuj i wykonaj zawartość pliku: `scripts/update_admin_to_proximalumine.sql`

### Nowe dane logowania

Po wykonaniu aktualizacji:

**Email:** proximalumine@gmail.com  
**Hasło:** Lumine1!

### Dlaczego przycisk "Pobierz dane" nie działa na Railway?

Przycisk "Pobierz dane" w sekcji dostawców wymaga:

1. **JavaScript został dodany** - teraz powinien działać
2. **Dostęp do API GUS** - sprawdź czy Railway ma dostęp do zewnętrznych API
3. **HTTPS/CORS** - upewnij się że aplikacja działa na HTTPS

### Troubleshooting

Jeśli przycisk nadal nie działa:

1. Otwórz Console w przeglądarce (F12)
2. Kliknij "Pobierz dane"
3. Sprawdź błędy w Console
4. Sprawdź zakładkę Network czy request jest wysyłany

Możliwe przyczyny:
- Blokada CORS
- Brak dostępu do API GUS z Railway
- Problem z SSL/HTTPS
