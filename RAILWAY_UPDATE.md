# Instrukcje aktualizacji na Railway

## PILNE: Aktualizacja bazy danych - Błąd 500 przy wycenach

### Problem
Błąd 500 przy tworzeniu/edycji wyceny na Railway - brak nowych kolumn w tabeli `offers`.

### Rozwiązanie (BEZPIECZNE - nie usuwa danych)

**Uruchom migracje na Railway:**

```bash
# Przez Railway CLI
railway run php artisan migrate --force
```

Lub w Railway Dashboard → Deployments → Shell:
```bash
php artisan migrate --force
```

**Migracje, które zostaną uruchomione (BEZPIECZNE):**
1. `add_custom_sections_to_offers_table` - dodaje kolumnę `custom_sections`
2. `add_crm_deal_id_to_offers_table` - dodaje kolumnę `crm_deal_id`
3. `add_customer_info_to_offers_table` - dodaje kolumny danych klienta

**UWAGA:** Te migracje używają `ALTER TABLE ADD COLUMN`, co NIE usuwa istniejących danych.

---

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
