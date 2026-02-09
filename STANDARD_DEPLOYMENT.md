# Standardy Deploymentu dla ProximaLumine

Aby uniknąć problemów ze strukturą bazy danych i migracjami na Railway oraz innych środowiskach, stosuj poniższe zasady:

## 1. Wymuszaj migracje przy każdym deployu
- W pliku `Procfile` lub w Railway ustaw:
  ```
  web: php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=8080
  ```
- Dzięki temu każda migracja zostanie uruchomiona automatycznie.

## 2. Testuj strukturę bazy po deployu
- Po każdym deployu uruchom stronę `database-compare.html` i sprawdź czy wszystkie tabele i kolumny istnieją.
- Jeśli są różnice, uruchom migracje ręcznie lub napraw bazę zgodnie z podpowiedziami.

## 3. Dodaj testy integracyjne
- Dodaj testy sprawdzające obecność kluczowych kolumn i tabel (np. w katalogu `tests/Feature`).

## 4. Monitoruj logi migracji
- Sprawdzaj logi po deployu (`storage/logs/laravel.log`) pod kątem błędów migracji.
- Dodaj powiadomienia (np. e-mail lub Slack) o nieudanych migracjach.

## 5. Przed uruchomieniem aplikacji sprawdzaj wersję migracji
- Możesz dodać middleware lub bootstrap, który sprawdza czy baza ma najnowszą wersję migracji.

## 6. Dokumentuj zmiany w migracjach
- Każda zmiana w migracjach powinna być opisana w commit message i w tym pliku.

---
**Stosowanie tych zasad minimalizuje ryzyko problemów z bazą danych i zapewnia bezpieczny deployment na wszystkich środowiskach.**