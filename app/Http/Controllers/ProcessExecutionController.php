<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;

class ProcessExecutionController extends Controller
{
    public function execute(Process $process)
    {
        // Załaduj kroki procesu i recepturę ze składnikami
        $process->load('steps', 'recipe.steps.ingredient');
        
        // Upewnij się, że wszystkie ingredients_data są tablicami
        foreach ($process->steps as $step) {
            if (is_string($step->ingredients_data)) {
                $step->ingredients_data = json_decode($step->ingredients_data, true) ?? [];
            }
        }
        
        // Sprawdź czy proces jest w trakcie realizacji
        if ($process->status !== 'in_progress') {
            return redirect()->route('processes.show', $process)->with('error', 'Proces nie jest w trakcie realizacji.');
        }
        
        // Oblicz przeskalowane składniki
        $scaledIngredients = $this->scaleIngredients($process->recipe, $process->quantity);
        
        return view('processes.execute', compact('process', 'scaledIngredients'));
    }
    
    private function scaleIngredients($recipe, int $quantity)
    {
        $scaleFactor = $quantity / ($recipe->output_quantity ?: 1);
        
        $flourSteps = $recipe->steps()
            ->where('is_flour', true)
            ->with('ingredient')
            ->get();
            
        $ingredientSteps = $recipe->steps()
            ->where('is_flour', false)
            ->where('type', 'ingredient')
            ->with('ingredient')
            ->get();
        
        $flour = $flourSteps->map(function($step) use ($scaleFactor) {
            return [
                'name' => $step->ingredient?->name ?? 'Nieznany',
                'original_weight' => $step->quantity,
                'scaled_weight' => $step->quantity * $scaleFactor,
                'percentage' => $step->percentage,
                'unit' => $step->ingredient?->unit ?? 'kg',
                'cost' => ($step->ingredient?->price ?? 0) * $step->quantity * $scaleFactor,
            ];
        });
        
        $ingredients = $ingredientSteps->map(function($step) use ($scaleFactor) {
            return [
                'name' => $step->ingredient?->name ?? 'Nieznany',
                'original_quantity' => $step->quantity,
                'scaled_quantity' => $step->quantity * $scaleFactor,
                'percentage' => $step->percentage,
                'unit' => $step->ingredient?->unit ?? '',
                'cost' => ($step->ingredient?->price ?? 0) * $step->quantity * $scaleFactor,
            ];
        });
        
        return [
            'flour' => $flour,
            'ingredients' => $ingredients,
            'scaleFactor' => $scaleFactor,
        ];
    }
}
