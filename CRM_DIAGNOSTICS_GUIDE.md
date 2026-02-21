# 🔧 Jak używać strony diagnostycznej CRM

## Dostęp

Po wdrożeniu na Railway, otwórz w przeglądarce:

```
https://twoja-domena.railway.app/crm-diagnostics
```

Lub lokalnie:
```
http://localhost:8000/crm-diagnostics
```

Możesz też kliknąć **🔧 Diagnostyka** w CRM lub Ustawieniach CRM.

## Co sprawdza ta strona?

### 1️⃣ **Środowisko**
- Wersja Laravel i PHP
- Typ bazy danych
- Nazwa bazy danych
- App environment (production/local)

### 2️⃣ **Struktura Tabeli crm_stages**
- ✅ Czy tabela istnieje
- ✅ Czy kolumna `is_closed` istnieje  
- 📋 Pełna lista wszystkich kolumn z typami

**🔍 WAŻNE:** Jeśli `is_closed` **NIE istnieje**, to jest przyczyna błędów 500!

### 3️⃣ **Status Migracji**
- Czy migracja `*is_closed*` została uruchomiona
- Lista wszystkich migracji CRM
- Batch number każdej migracji

### 4️⃣ **Dane w Tabeli**
- Wszystkie etapy z bazy
- Status kolumny `is_closed` dla każdego etapu
- Kolory, kolejność, aktywność

### 5️⃣ **Test INSERT**
- Symulacja dodawania nowego etapu
- Sprawdza czy `is_closed` byłby dodany do INSERT
- Pokazuje dokładne dane które byłyby wstawione

### 6️⃣ **Test Schema::hasColumn()**
- Testuje czy Laravel poprawnie wykrywa kolumny
- Sprawdza każdą kolumnę osobno
- Weryfikuje czy `is_closed` jest wykrywany

### 7️⃣ **Weryfikacja Kodu Kontrolera**
- Czy używa `Schema::hasColumn()`
- Czy ma try-catch
- Czy obsługuje `is_closed`

### 8️⃣ **Rekomendacje**
- Automatyczna diagnoza problemów
- Konkretne kroki naprawcze
- Polecenia do uruchomienia

## Jak interpretować wyniki?

### ✅ Wszystko OK
```
✓ Tabela crm_stages istnieje
✓ Kolumna is_closed istnieje
✓ Migracja is_closed została uruchomiona
```
→ **Wszystko działa poprawnie**

### ❌ Brak kolumny is_closed
```
✗ BRAK kolumny is_closed!
✗ Migracja is_closed NIE została uruchomiona!
```
→ **TO JEST PROBLEM!** Uruchom migrację:

```bash
railway run php artisan migrate --force
```

### ⚠️ Migracja wykonana ale kolumna nie istnieje
```
✓ Migracja is_closed została uruchomiona
✗ BRAK kolumny is_closed!
```
→ **Migracja crashnęła** - użyj skryptu SQL:

```sql
ALTER TABLE crm_stages ADD COLUMN is_closed TINYINT(1) DEFAULT 0 AFTER is_active;
UPDATE crm_stages SET is_closed = 1 WHERE slug IN ('wygrana', 'przegrana');
```

## Częste problemy i rozwiązania

### Problem: Błąd 500 przy dodawaniu nowych etapów

**Przyczyna:** Brak kolumny `is_closed`

**Rozwiązanie:**
1. Otwórz `/crm-diagnostics`
2. Sprawdź sekcję "Struktura Tabeli"
3. Jeśli brak `is_closed`, uruchom:
   ```bash
   railway run php artisan migrate --force
   ```

### Problem: Schema::hasColumn() zwraca FALSE mimo że kolumna istnieje

**Przyczyna:** Cache Laravel

**Rozwiązanie:**
```bash
railway run php artisan config:clear
railway run php artisan cache:clear
```

### Problem: Wszystkie etapy powyżej 6 powodują błąd 500

**Przyczyna:** Pierwsze 6 etapów zostało utworzone PRZED dodaniem kolumny `is_closed`

**Rozwiązanie:**
1. Sprawdź w diagnostyce czy kolumna istnieje
2. Jeśli istnieje, sprawdź czy wszystkie etapy mają wartość w `is_closed` (0 lub 1)
3. Jeśli niektóre mają NULL, uruchom:
   ```sql
   UPDATE crm_stages SET is_closed = 0 WHERE is_closed IS NULL;
   ```

## Debug workflow

1. **Otwórz `/crm-diagnostics`** na Railway
2. **Sprawdź "Struktura Tabeli"** - czy `is_closed` istnieje?
3. **Sprawdź "Status Migracji"** - czy migracja się wykonała?
4. **Sprawdź "Test INSERT"** - czy zawiera `is_closed`?
5. **Przeczytaj "Rekomendacje"** - konkretne kroki do wykonania

## Zrzut ekranu dla wsparcia

Jeśli problem nie znika:
1. Otwórz `/crm-diagnostics`
2. Zrób screenshot CAŁEJ strony (przewiń do dołu)
3. Wyślij screenshot + błąd z konsoli Railway

## Automatyczne naprawy

Strona **NIE wykonuje** żadnych zmian w bazie - tylko **pokazuje** stan.

Aby naprawić automatycznie, użyj:
```bash
railway run php scripts/fix-crm-stages-railway.php
```

Lub zobacz: `RAILWAY_CRM_FIX.md`
