-- Skrypt SQL do naprawy tabeli crm_stages na Railway
-- Uruchom to bezpośrednio w Railway Database Query

-- 1. Sprawdź aktualny stan tabeli
SELECT * FROM crm_stages ORDER BY `order`;

-- 2. Dodaj kolumnę is_closed jeśli nie istnieje
-- Jeśli pojawi się błąd "Duplicate column name", oznacza to że kolumna już istnieje - to jest OK
ALTER TABLE crm_stages ADD COLUMN is_closed TINYINT(1) DEFAULT 0 AFTER is_active;

-- 3. Ustaw is_closed = 1 dla domyślnych etapów zamykających
UPDATE crm_stages SET is_closed = 1 WHERE slug IN ('wygrana', 'przegrana');

-- 4. Sprawdź końcowy stan
SELECT 
    id,
    name,
    slug,
    `order`,
    is_active,
    is_closed,
    CASE 
        WHEN is_closed = 1 THEN '✓ Zakończenie lejka'
        ELSE 'Aktywny etap'
    END as status
FROM crm_stages 
ORDER BY `order`;

-- 5. Jeśli masz niestandardowe etapy które powinny kończyć lejek, dodaj je tutaj:
-- UPDATE crm_stages SET is_closed = 1 WHERE slug = 'rezygnacja';
-- UPDATE crm_stages SET is_closed = 1 WHERE slug = 'anulowana';
