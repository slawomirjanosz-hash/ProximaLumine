-- Skrypt do aktualizacji użytkownika admin na Railway
-- Zmiana admina slawomir.janosz@gmail.com na proximalumine@gmail.com

-- Krok 1: Zaktualizuj istniejącego użytkownika slawomir.janosz@gmail.com na proximalumine@gmail.com
UPDATE users 
SET 
    email = 'proximalumine@gmail.com',
    name = 'ProximaLumine',
    first_name = 'Proxima',
    last_name = 'Lumine',
    short_name = 'ProLum',
    password = '$2y$10$i37Sq1W3KgiA57PwFB5R5.brCXCPY9ZeKDlC3HfFrKK/8hK3t9lv2', -- Hasło: Lumine1!
    is_admin = 1,
    can_view_catalog = 1,
    can_add = 1,
    can_remove = 1,
    can_orders = 1,
    can_settings = 1,
    can_settings_categories = 1,
    can_settings_suppliers = 1,
    can_settings_company = 1,
    can_settings_users = 1,
    can_settings_export = 1,
    can_settings_other = 1,
    can_delete_orders = 1,
    show_action_column = 1
WHERE email = 'slawomir.janosz@gmail.com';

-- Krok 2: Jeśli użytkownik proximalumine@gmail.com nie istnieje, utwórz go
INSERT INTO users (
    email, 
    name, 
    first_name, 
    last_name, 
    short_name, 
    password, 
    is_admin, 
    can_view_catalog, 
    can_add, 
    can_remove, 
    can_orders, 
    can_settings,
    can_settings_categories,
    can_settings_suppliers,
    can_settings_company,
    can_settings_users,
    can_settings_export,
    can_settings_other,
    can_delete_orders,
    show_action_column,
    created_at,
    updated_at
)
SELECT 
    'proximalumine@gmail.com',
    'ProximaLumine',
    'Proxima',
    'Lumine',
    'ProLum',
    '$2y$10$i37Sq1W3KgiA57PwFB5R5.brCXCPY9ZeKDlC3HfFrKK/8hK3t9lv2', -- Hasło: Lumine1!
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    1,
    NOW(),
    NOW()
WHERE NOT EXISTS (
    SELECT 1 FROM users WHERE email = 'proximalumine@gmail.com'
);
