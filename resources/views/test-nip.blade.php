<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test wyszukiwania NIP</title>
</head>
<body>
    <h1>Test wyszukiwania firmy po NIP</h1>
    
    <div>
        <label>Wpisz NIP (10 cyfr):</label>
        <input type="text" id="nip-input" placeholder="1234567890">
        <button onclick="testSearch()">Szukaj</button>
    </div>
    
    <div id="result" style="margin-top: 20px; padding: 10px; border: 1px solid #ccc;"></div>
    
    <script>
        async function testSearch() {
            const nip = document.getElementById('nip-input').value;
            const resultDiv = document.getElementById('result');
            
            resultDiv.innerHTML = 'Wysyłam zapytanie...';
            
            try {
                const url = '/crm/company/search-by-nip?nip=' + encodeURIComponent(nip);
                console.log('URL:', url);
                
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                console.log('Status:', response.status);
                const data = await response.json();
                console.log('Data:', data);
                
                resultDiv.innerHTML = '<h3>Odpowiedź:</h3><pre>' + JSON.stringify(data, null, 2) + '</pre>';
                
            } catch (error) {
                console.error('Błąd:', error);
                resultDiv.innerHTML = '<span style="color: red;">Błąd: ' + error.message + '</span>';
            }
        }
    </script>
</body>
</html>
