<!DOCTYPE html>
<html>
<head>
    <title>Test Offers New</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #000; color: #0f0; }
        .error { color: #f00; background: #300; padding: 10px; margin: 10px 0; }
        .ok { color: #0f0; }
        pre { background: #111; padding: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>Testing /wyceny/nowa</h1>
    
    @php
        echo '<h2>Step 1: Load Companies</h2>';
        try {
            $companies = \App\Models\CrmCompany::with('supplier')->orderBy('name')->get();
            echo '<div class="ok">✓ Companies loaded: ' . $companies->count() . '</div>';
        } catch (\Exception $e) {
            echo '<div class="error">ERROR: ' . $e->getMessage() . '</div>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        }
        
        echo '<h2>Step 2: Check View File</h2>';
        if (file_exists(resource_path('views/offers-new.blade.php'))) {
            echo '<div class="ok">✓ View file exists</div>';
        } else {
            echo '<div class="error">✗ View file NOT found at: ' . resource_path('views/offers-new.blade.php') . '</div>';
        }
        
        echo '<h2>Step 3: Try to Render View</h2>';
        try {
            $deal = null;
            $companies = \App\Models\CrmCompany::with('supplier')->orderBy('name')->get();
            $html = view('offers-new', ['deal' => $deal, 'companies' => $companies])->render();
            echo '<div class="ok">✓ View rendered successfully!</div>';
            echo '<div>HTML length: ' . strlen($html) . ' bytes</div>';
        } catch (\Exception $e) {
            echo '<div class="error">✗ RENDER FAILED</div>';
            echo '<div class="error">Message: ' . $e->getMessage() . '</div>';
            echo '<div class="error">File: ' . $e->getFile() . '</div>';
            echo '<div class="error">Line: ' . $e->getLine() . '</div>';
            echo '<pre class="error">' . $e->getTraceAsString() . '</pre>';
        }
        
        echo '<h2>Step 4: Direct Route Call</h2>';
        try {
            $request = Request::create('/wyceny/nowa', 'GET');
            $response = app()->handle($request);
            echo '<div class="ok">✓ Route executed</div>';
            echo '<div>Status: ' . $response->getStatusCode() . '</div>';
            if ($response->getStatusCode() !== 200) {
                echo '<div class="error">Response content:</div>';
                echo '<pre>' . $response->getContent() . '</pre>';
            }
        } catch (\Exception $e) {
            echo '<div class="error">✗ ROUTE FAILED</div>';
            echo '<div class="error">' . $e->getMessage() . '</div>';
            echo '<pre>' . $e->getTraceAsString() . '</pre>';
        }
    @endphp
    
    <hr>
    <p><a href="/wyceny/nowa" style="color: #0f0;">Click to test /wyceny/nowa</a></p>
</body>
</html>
