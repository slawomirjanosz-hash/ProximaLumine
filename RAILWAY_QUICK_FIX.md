# RAILWAY QUICK FIX - Dodaj brakujący etap "Rezygnacja"

## Problem
Próbujesz zmienić etap szansy na "rezygnacja", ale tego etapu **NIE MA** w bazie danych Railway!

## Rozwiązanie

### Opcja 1: Dodaj przez UI
1. Otwórz: https://prinz.up.railway.app/crm-settings
2. Dodaj nowy etap:
   - Nazwa: `Rezygnacja`
   - Slug: `rezygnacja` (małymi literami!)
   - Kolor: `#dc2626` (czerwony)
   - Kolejność: `7`
   - ✅ Zaznacz "Zakończenie Lejka" (is_closed)

### Opcja 2: SQL Fix (Railway Console)
```sql
INSERT INTO crm_stages (name, slug, color, `order`, is_active, is_closed, created_at, updated_at)
VALUES ('Rezygnacja', 'rezygnacja', '#dc2626', 7, 1, 1, NOW(), NOW());
```

## Weryfikacja
Sprawdź czy etap istnieje:
```sql
SELECT id, name, slug, is_closed FROM crm_stages ORDER BY `order`;
```

Powinno być:
1. Nowy Lead (nowy_lead) - is_closed=0
2. Kontakt (kontakt) - is_closed=0
3. Wycena (wycena) - is_closed=0
4. Negocjacje (negocjacje) - is_closed=0
5. Wygrana (wygrana) - is_closed=1
6. Przegrana (przegrana) - is_closed=1
7. **Rezygnacja (rezygnacja) - is_closed=1** ← Powinien być!
