<?php

namespace App\Http\Controllers;

use App\Models\Process;
use App\Models\ProcessStep;
use App\Models\Recipe;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public function index()
    {
        $processes = Process::with('recipe')->latest()->get();
        return view('processes.index', compact('processes'));
    }

    public function create()
    {
        $recipes = Recipe::all();
        return view('processes.create', compact('recipes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'steps.*.action_name' => 'required|string',
            'steps.*.action_description' => 'nullable|string',
            'steps.*.duration' => 'nullable|integer|min:0',
        ]);

        $recipe = Recipe::findOrFail($request->recipe_id);
        
        // Oblicz koszt na podstawie składników przeskalowanych do quantity
        $totalCost = $this->calculateCost($recipe, $request->quantity);

        $process = Process::create([
            'recipe_id' => $request->recipe_id,
            'name' => $request->name,
            'quantity' => $request->quantity,
            'total_cost' => $totalCost,
            'notes' => $request->notes,
        ]);

        // Zapisz kroki
        if ($request->has('steps')) {
            foreach ($request->steps as $order => $stepData) {
                ProcessStep::create([
                    'process_id' => $process->id,
                    'action_name' => $stepData['action_name'],
                    'action_description' => $stepData['action_description'] ?? null,
                    'duration' => $stepData['duration'] ?? null,
                    'order' => $order,
                    'ingredients' => isset($stepData['ingredients']) ? $stepData['ingredients'] : null,
                    'ingredients_data' => isset($stepData['ingredients_data']) ? $stepData['ingredients_data'] : null,
                ]);
            }
        }

        return redirect()->route('processes.show', $process)->with('success', 'Proces został utworzony!');
    }

    public function show(Process $process)
    {
        $process->load('recipe.steps.ingredient', 'steps');
        
        // Upewnij się, że wszystkie ingredients_data są tablicami
        foreach ($process->steps as $step) {
            if (is_string($step->ingredients_data)) {
                $step->ingredients_data = json_decode($step->ingredients_data, true) ?? [];
            }
        }
        
        // Przelicz składniki dla zadanej ilości
        $scaledIngredients = $this->scaleIngredients($process->recipe, $process->quantity);
        
        return view('processes.show', compact('process', 'scaledIngredients'));
    }

    public function edit(Process $process)
    {
        $process->load('recipe.steps.ingredient', 'steps');
        $recipes = Recipe::all();
        
        // Upewnij się, że wszystkie ingredients_data są tablicami
        foreach ($process->steps as $step) {
            if (is_string($step->ingredients_data)) {
                $step->ingredients_data = json_decode($step->ingredients_data, true) ?? [];
            }
        }
        
        // Przelicz składniki dla zadanej ilości
        $scaledIngredients = $this->scaleIngredientsWithRemaining($process);
        
        return view('processes.edit', compact('process', 'recipes', 'scaledIngredients'));
    }

    public function update(Request $request, Process $process)
    {
        $request->validate([
            'recipe_id' => 'required|exists:recipes,id',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'steps.*.action_name' => 'required|string',
            'steps.*.action_description' => 'nullable|string',
            'steps.*.duration' => 'nullable|integer|min:0',
        ]);

        $recipe = Recipe::findOrFail($request->recipe_id);
        $totalCost = $this->calculateCost($recipe, $request->quantity);

        $process->update([
            'recipe_id' => $request->recipe_id,
            'name' => $request->name,
            'quantity' => $request->quantity,
            'total_cost' => $totalCost,
            'notes' => $request->notes,
        ]);

        // Usuń stare kroki i dodaj nowe
        $process->steps()->delete();
        if ($request->has('steps')) {
            foreach ($request->steps as $order => $stepData) {
                ProcessStep::create([
                    'process_id' => $process->id,
                    'action_name' => $stepData['action_name'],
                    'action_description' => $stepData['action_description'] ?? null,
                    'duration' => $stepData['duration'] ?? null,
                    'order' => $order,
                    'ingredients' => isset($stepData['ingredients']) ? $stepData['ingredients'] : null,
                    'ingredients_data' => isset($stepData['ingredients_data']) ? $stepData['ingredients_data'] : null,
                ]);
            }
        }

        return redirect()->route('processes.show', $process)->with('success', 'Proces został zaktualizowany!');
    }

    public function destroy(Process $process)
    {
        $process->delete();
        return redirect()->route('processes.index')->with('success', 'Proces został usunięty!');
    }

    public function start(Process $process)
    {
        // Tu można dodać logikę uruchamiania procesu, np. zmianę statusu, logowanie czasu startu, itp.
        $process->update(['status' => 'in_progress', 'started_at' => now()]);
        return response()->json(['url' => route('processes.execute', $process)]);
    }

    private function scaleIngredients(Recipe $recipe, int $quantity)
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

    private function calculateCost(Recipe $recipe, int $quantity)
    {
        $scaleFactor = $quantity / ($recipe->output_quantity ?: 1);
        $totalCost = 0;

        foreach ($recipe->steps()->with('ingredient')->get() as $step) {
            if ($step->ingredient && $step->ingredient->price) {
                $totalCost += $step->ingredient->price * $step->quantity * $scaleFactor;
            }
        }

        return $totalCost;
    }

    private function scaleIngredientsWithRemaining(Process $process)
    {
        $recipe = $process->recipe;
        $scaleFactor = $process->quantity / ($recipe->output_quantity ?: 1);
        
        // Oblicz ile zostało każdego składnika
        $usedIngredients = [];
        foreach ($process->steps as $step) {
            if ($step->ingredients_data) {
                $ingredientsData = $step->ingredients_data;
                // Upewnij się, że ingredients_data jest tablicą
                if (is_string($ingredientsData)) {
                    $ingredientsData = json_decode($ingredientsData, true) ?? [];
                }
                foreach ($ingredientsData as $item) {
                    $ingredientId = $item['ingredient_id'] ?? null;
                    if ($ingredientId) {
                        if (!isset($usedIngredients[$ingredientId])) {
                            $usedIngredients[$ingredientId] = 0;
                        }
                        $usedIngredients[$ingredientId] += $item['quantity_added'] ?? 0;
                    }
                }
            }
        }
        
        $flourSteps = $recipe->steps()
            ->where('is_flour', true)
            ->with('ingredient')
            ->get();
            
        $ingredientSteps = $recipe->steps()
            ->where('is_flour', false)
            ->where('type', 'ingredient')
            ->with('ingredient')
            ->get();
        
        $flour = $flourSteps->map(function($step) use ($scaleFactor, $usedIngredients) {
            $ingredientId = $step->ingredient->id ?? null;
            $scaledQuantity = $step->quantity * $scaleFactor;
            $usedQuantity = $usedIngredients[$ingredientId] ?? 0;
            $remaining = $scaledQuantity - $usedQuantity;
            
            return [
                'ingredient_id' => $ingredientId,
                'name' => $step->ingredient?->name ?? 'Nieznany',
                'original_weight' => $step->quantity,
                'scaled_weight' => $scaledQuantity,
                'used' => $usedQuantity,
                'remaining' => max(0, $remaining),
                'percentage' => $step->percentage,
                'unit' => $step->ingredient?->unit ?? 'kg',
                'cost' => ($step->ingredient?->price ?? 0) * $step->quantity * $scaleFactor,
                'is_flour' => true,
            ];
        });
        
        $ingredients = $ingredientSteps->map(function($step) use ($scaleFactor, $usedIngredients) {
            $ingredientId = $step->ingredient->id ?? null;
            $scaledQuantity = $step->quantity * $scaleFactor;
            $usedQuantity = $usedIngredients[$ingredientId] ?? 0;
            $remaining = $scaledQuantity - $usedQuantity;
            
            return [
                'ingredient_id' => $ingredientId,
                'name' => $step->ingredient?->name ?? 'Nieznany',
                'original_quantity' => $step->quantity,
                'scaled_quantity' => $scaledQuantity,
                'used' => $usedQuantity,
                'remaining' => max(0, $remaining),
                'percentage' => $step->percentage,
                'unit' => $step->ingredient?->unit ?? '',
                'cost' => ($step->ingredient?->price ?? 0) * $step->quantity * $scaleFactor,
                'is_flour' => false,
            ];
        });
        
        return [
            'flour' => $flour,
            'ingredients' => $ingredients,
            'scaleFactor' => $scaleFactor,
        ];
    }
}

