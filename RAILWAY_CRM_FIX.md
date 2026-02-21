# 🔧 Naprawa CRM na Railway - Problem z kolumną is_closed

## Problem
Po dodaniu nowych etapów CRM (powyżej domyślnych 6) Railway zgłasza błąd 500. Problem występuje, ponieważ baza danych na Railway nie ma kolumny `is_closed` w tabeli `crm_stages`.

## Rozwiązanie

### Opcja 1: Uruchom migrację na Railway (Zalecane)

1. Zaloguj się do Railway CLI:
```bash
railway login
```

2. Wybierz projekt:
```bash
railway link
```

3. Uruchom migrację:
```bash
railway run php artisan migrate
```

### Opcja 2: Uruchom skrypt naprawczy PHP

W konsoli Railway uruchom:
```bash
railway run php scripts/fix-crm-stages-railway.php
```

Skrypt:
- Sprawdzi czy kolumna `is_closed` istnieje
- Doda ją jeśli nie istnieje
- Ustawi `is_closed = 1` dla domyślnych etapów "wygrana" i "przegrana"
- Wyświetli aktualny stan wszystkich etapów

### Opcja 3: Wykonaj zapytania SQL bezpośrednio w Railway Database

1. Otwórz Railway Dashboard
2. Przejdź do zakładki Database → Query
3. Wykonaj poniższe zapytania:

```sql
-- Dodaj kolumnę is_closed
ALTER TABLE crm_stages ADD COLUMN is_closed TINYINT(1) DEFAULT 0 AFTER is_active;

-- Ustaw is_closed = 1 dla domyślnych etapów
UPDATE crm_stages SET is_closed = 1 WHERE slug IN ('wygrana', 'przegrana');

-- Jeśli masz już utworzony etap "rezygnacja", ustaw też dla niego:
UPDATE crm_stages SET is_closed = 1 WHERE slug = 'rezygnacja';

-- Sprawdź wynik
SELECT id, name, slug, is_active, is_closed FROM crm_stages ORDER BY `order`;
```

### Opcja 4: Użyj gotowego pliku SQL

Plik `scripts/fix-crm-stages.sql` zawiera wszystkie potrzebne zapytania. Możesz:
1. Skopiować zawartość pliku
2. Wkleić do Railway Database Query
3. Wykonać po kolei

## Co robi kolumna is_closed?

- `is_closed = 0` - Normalny etap procesu sprzedaży (lead, kontakt, wycena, negocjacje)
- `is_closed = 1` - Etap kończący lejek sprzedażowy (wygrana, przegrana, rezygnacja, anulowana)

Etapy z `is_closed = 1`:
- Nie są pokazywane przy tworzeniu nowej szansy (nie ma sensu tworzyć już zamkniętej szansy)
- Są pokazywane przy edycji istniejącej szansy (można przenieść szansę do zamknięcia)
- Automatycznie ustawiają datę zamknięcia (`actual_close_date`)
- Nie są uwzględniane w statystykach aktywnych szans

## Weryfikacja

Po wykonaniu naprawy:

1. **Sprawdź w Railway Database:**
```sql
SELECT * FROM crm_stages ORDER BY `order`;
```
Wszystkie etapy powinny mieć kolumnę `is_closed`.

2. **Sprawdź w aplikacji:**
- Przejdź do CRM → Ustawienia CRM
- Sprawdź czy wszystkie etapy się wyświetlają
- Spróbuj dodać nowy etap
- Spróbuj edytować istniejącą szansę i zmienić jej etap

3. **Sprawdź logi Railway:**
```bash
railway logs
```
Nie powinno być błędów 500 przy zmianie etapu szansy.

## Zabezpieczenia w kodzie

Kod jest zabezpieczony przed brakiem kolumny `is_closed`:
- ✅ `Schema::hasColumn()` sprawdza czy kolumna istnieje przed użyciem
- ✅ Fallback do starych nazw etapów `['wygrana', 'przegrana']` jeśli kolumny nie ma
- ✅ `isset()` w widokach Blade przed sprawdzeniem wartości
- ✅ Filter w widokach który działa nawet jeśli kolumny nie ma

Mimo to **zalecane jest dodanie kolumny** aby móc używać niestandardowych etapów zamykających (takich jak "rezygnacja").

## Pytania?

Jeśli naprawa nie działa:
1. Sprawdź logi Railway: `railway logs`
2. Sprawdź strukturę tabeli: `DESCRIBE crm_stages;`
3. Sprawdź czy migracja się wykonała: `SELECT * FROM migrations WHERE migration LIKE '%is_closed%';`
