# 🚨 PILNE: Problem z CRM na Railway - INSTRUKCJA

## 🎯 JAK ZDIAGNOZOWAĆ PROBLEM

### Krok 1: Otwórz stronę diagnostyczną

Na Railway otwórz w przeglądarce:
```
https://prinz.up.railway.app/crm-diagnostics
```

Na stronie zobaczysz **DOKŁADNIE** co jest nie tak z bazą danych.

### Krok 2: Sprawdź sekcję "Struktura Tabeli crm_stages"

**JEŚLI WIDZISZ:**
```
✗ BRAK kolumny is_closed!
```

**TO JEST PROBLEM!** Przejdź do Krok 3.

**JEŚLI WIDZISZ:**
```
✓ Kolumna is_closed istnieje
```

To problem jest gdzieś indziej - zobacz **CAŁY** raport diagnostyczny i prześlij screenshot.

---

## 🔧 JAK NAPRAWIĆ

### Opcja A: Railway CLI (Najlepsza)

```bash
# Zaloguj się
railway login

# Połącz z projektem
railway link

# Uruchom migrację
railway run php artisan migrate --force
```

### Opcja B: Railway Database Query

1. Otwórz **Railway Dashboard**
2. Przejdź do **Database** → **Query**
3. Wklej i wykonaj:

```sql
-- Dodaj kolumnę is_closed
ALTER TABLE crm_stages 
ADD COLUMN is_closed TINYINT(1) DEFAULT 0 
AFTER is_active;

-- Ustaw is_closed dla domyślnych etapów
UPDATE crm_stages 
SET is_closed = 1 
WHERE slug IN ('wygrana', 'przegrana');

-- Sprawdź wynik
SELECT id, name, slug, is_active, is_closed 
FROM crm_stages 
ORDER BY `order`;
```

### Opcja C: Skrypt naprawczy

```bash
railway run php scripts/fix-crm-stages-railway.php
```

---

## ✅ WERYFIKACJA PO NAPRAWIE

1. **Odśwież** `/crm-diagnostics`
2. **Sprawdź** czy widzisz:
   ```
   ✓ Kolumna is_closed istnieje
   ✓ Migracja is_closed została uruchomiona
   ```
3. **Przejdź do CRM** → **Ustawienia CRM**
4. **Spróbuj dodać nowy etap** (np. "Test")
5. **Jeśli działa** - usuń testowy etap i gotowe!

---

## 📊 SZYBKI STATUS (bez przeglądarki)

```bash
railway run bash scripts/quick-crm-check.sh
```

Pokaże w konsoli czy `is_closed` istnieje.

---

## 📁 WSZYSTKIE PLIKI POMOCNICZE

1. **`/crm-diagnostics`** - pełna strona diagnostyczna (otwórz w przeglądarce)
2. **`CRM_DIAGNOSTICS_GUIDE.md`** - jak używać strony diagnostycznej
3. **`RAILWAY_CRM_FIX.md`** - szczegółowy opis problemu i wszystkie opcje naprawy
4. **`scripts/fix-crm-stages-railway.php`** - automatyczna naprawa (PHP)
5. **`scripts/fix-crm-stages.sql`** - zapytania SQL do ręcznego wykonania
6. **`scripts/quick-crm-check.sh`** - szybkie sprawdzenie statusu (CLI)
7. **`scripts/diagnose-crm.ps1`** - lokalna diagnostyka (PowerShell)

---

## ❓ CO SPRAWDZA STRONA DIAGNOSTYCZNA?

1. ✅ Czy tabela `crm_stages` istnieje
2. ✅ Czy kolumna `is_closed` istnieje
3. ✅ Czy migracja się wykonała
4. ✅ Czy `Schema::hasColumn()` działa poprawnie
5. ✅ Jak wyglądałby INSERT nowego etapu
6. ✅ Wszystkie etapy z wartościami `is_closed`
7. ✅ Czy kod kontrolera poprawnie obsługuje kolumnę
8. ✅ **Konkretne rekomendacje co zrobić**

---

## 🆘 JEŚLI NADAL NIE DZIAŁA

1. **Otwórz** `/crm-diagnostics` na Railway
2. **Zrób screenshot** CAŁEJ strony (przewiń do końca)
3. **Wyślij mi** screenshot + błąd z Railway Logs

Strona diagnostyczna pokaże **DOKŁADNIE** czego brakuje.

---

## 📌 TLDR

```
1. Otwórz: https://prinz.up.railway.app/crm-diagnostics
2. Zobacz co jest nie tak
3. Uruchom: railway run php artisan migrate --force
4. Gotowe!
```

Jeśli migracja nie pomoże, użyj SQL z **Opcji B** powyżej.
