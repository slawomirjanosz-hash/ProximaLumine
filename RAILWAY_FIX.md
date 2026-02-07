# Instrukcje naprawy Railway
# Wykonaj te kroki ręcznie:

# 1. Przełącz się na serwis aplikacji webowej (nie MySQL):
railway service
# WYBIERZ: serwis który NIE jest MySQL (prawdopodobnie pierwszy na liście)

# 2. Uruchom migracje:
railway run php artisan migrate --force

# 3. Wyczyść cache:
railway run php artisan cache:clear
railway run php artisan config:clear
railway run php artisan view:clear
railway run php artisan route:clear

# 4. Sprawdź logi aby zobaczyć dokładny błąd:
railway logs

# Jeśli nadal są problemy, możliwe przyczyny:
# - Brak tabeli product_lists (migracja 2026_02_07_092737)
# - Brak tabeli product_list_items (migracja 2026_02_07_092741)
# - Brak kolumny loaded_list_id w projects (migracja 2026_02_07_094737)
