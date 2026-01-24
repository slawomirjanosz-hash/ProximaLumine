<?php

namespace App\Http\Controllers;

use App\Models\CrmCustomerType;
use Illuminate\Http\Request;

class CrmCustomerTypeController extends Controller
{
    public function index()
    {
        return view('parts.crm-customer-types', [
            'customerTypes' => CrmCustomerType::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:crm_customer_types,slug',
            'color' => 'nullable|string|max:7',
        ]);
        
        $data = $request->only(['name', 'slug']);
        
        // Automatycznie przypisz unikalny kolor jeśli nie podano
        if (!$request->filled('color')) {
            $data['color'] = $this->getUniqueColor('customer_type');
        } else {
            $data['color'] = $request->color;
        }
        
        CrmCustomerType::create($data);
        return redirect()->back()->with('success', 'Dodano nowy typ klienta.');
    }
    
    public function show($id)
    {
        $type = CrmCustomerType::findOrFail($id);
        return response()->json($type);
    }

    public function update(Request $request, $id)
    {
        $type = CrmCustomerType::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:crm_customer_types,slug,' . $id,
            'color' => 'required|string|max:7',
        ]);
        $type->update($request->only(['name', 'slug', 'color']));
        return redirect()->back()->with('success', 'Zaktualizowano typ klienta.');
    }

    public function destroy($id)
    {
        $type = CrmCustomerType::findOrFail($id);
        $type->delete();
        return redirect()->back()->with('success', 'Usunięto typ klienta.');
    }
    
    private function getUniqueColor($context = 'customer_type')
    {
        // Paleta kolorów do wyboru
        $colorPalette = [
            '#2563eb', '#3b82f6', '#10b981', '#14b8a6', '#06b6d4',
            '#8b5cf6', '#a855f7', '#ec4899', '#f43f5e', '#ef4444',
            '#f59e0b', '#f97316', '#84cc16', '#22c55e', '#6366f1',
            '#d946ef', '#f472b6', '#fb923c', '#fbbf24', '#a3e635'
        ];
        
        // Pobierz użyte kolory
        $usedColors = [];
        if ($context === 'customer_type') {
            $usedColors = CrmCustomerType::pluck('color')->toArray();
        } elseif ($context === 'stage') {
            $usedColors = \DB::table('crm_stages')->pluck('color')->toArray();
        }
        
        // Znajdź pierwszy nieużywany kolor
        foreach ($colorPalette as $color) {
            if (!in_array($color, $usedColors)) {
                return $color;
            }
        }
        
        // Jeśli wszystkie kolory są użyte, wygeneruj losowy
        return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
    }
}
