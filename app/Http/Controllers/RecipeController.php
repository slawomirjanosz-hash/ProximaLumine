<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recipe;
use App\Models\RecipeStep;
use App\Models\Ingredient;
use App\Models\RecipeExecution;
use Illuminate\Support\Facades\DB;

class RecipeController extends Controller
{
    // Helper method to check recipe permissions
    private function checkRecipeAccess()
    {
        if (!auth()->user()->can_view_recipes && auth()->user()->email !== 'proximalumine@gmail.com') {
            abort(403, 'Brak dostępu do receptur');
        }
    }

    // KATALOG SKŁADNIKÓW
    public function ingredientsIndex()
    {
        $this->checkRecipeAccess();
        $ingredients = Ingredient::orderBy('name')->get();
        return view('recipes.ingredients', compact('ingredients'));
    }

    public function ingredientStore(Request $request)
    {
        $this->checkRecipeAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'price' => 'nullable|numeric|min:0'
        ]);

        Ingredient::create($request->all());
        return redirect()->route('recipes.ingredients')->with('success', 'Składnik został dodany!');
    }

    public function ingredientUpdate(Request $request, Ingredient $ingredient)
    {
        $this->checkRecipeAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'quantity' => 'required|numeric|min:0',
            'price' => 'nullable|numeric|min:0'
        ]);

        $ingredient->update($request->all());
        return redirect()->route('recipes.ingredients')->with('success', 'Składnik został zaktualizowany!');
    }

    public function ingredientDestroy(Ingredient $ingredient)
    {
        $this->checkRecipeAccess();
        $ingredient->delete();
        return redirect()->route('recipes.ingredients')->with('success', 'Składnik został usunięty!');
    }

    // LISTA RECEPTUR
    public function index()
    {
        $this->checkRecipeAccess();
        $recipes = Recipe::withCount('steps')->orderBy('name')->get();
        return view('recipes.index', compact('recipes'));
    }

    // TWORZENIE RECEPTURY
    public function create()
    {
        $this->checkRecipeAccess();
        $ingredients = Ingredient::orderBy('name')->get();
        return view('recipes.create', compact('ingredients'));
    }

    public function store(Request $request)
    {
        $this->checkRecipeAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'output_quantity' => 'required|integer|min:1',
            'flour' => 'required|array|min:1',
            'flour.*.ingredient_id' => 'required|exists:ingredients,id',
            'flour.*.weight' => 'required|numeric|min:0.01',
            'flour.*.percentage' => 'required|numeric|min:0.01',
            'ingredient.*.ingredient_id' => 'nullable|exists:ingredients,id',
            'ingredient.*.quantity' => 'nullable|numeric|min:0.01',
            'ingredient.*.percentage' => 'nullable|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Sprawdź czy suma procentów mąki = 100%
            $flourPercentageSum = collect($request->flour)->sum('percentage');
            if (abs($flourPercentageSum - 100) > 0.01) {
                return back()->with('error', 'Suma procentów mąki musi wynosić 100%!');
            }
            
            $recipe = Recipe::create([
                'name' => $request->name,
                'description' => $request->description,
                'output_quantity' => $request->output_quantity,
                'total_steps' => 0,
                'estimated_time' => 0
            ]);

            $order = 1;
            
            // Dodaj mąkę
            foreach ($request->flour as $flour) {
                RecipeStep::create([
                    'recipe_id' => $recipe->id,
                    'order' => $order++,
                    'type' => 'ingredient',
                    'ingredient_id' => $flour['ingredient_id'],
                    'quantity' => $flour['weight'],
                    'percentage' => $flour['percentage'] ?? 0,
                    'is_flour' => true,
                ]);
            }
            
            // Dodaj pozostałe składniki
            if ($request->has('ingredient')) {
                foreach ($request->ingredient as $ingredient) {
                    if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity']) && isset($ingredient['percentage'])) {
                        RecipeStep::create([
                            'recipe_id' => $recipe->id,
                            'order' => $order++,
                            'type' => 'ingredient',
                            'ingredient_id' => $ingredient['ingredient_id'],
                            'quantity' => $ingredient['quantity'],
                            'percentage' => $ingredient['percentage'] ?? 0,
                            'is_flour' => false,
                        ]);
                    }
                }
            }
            
            // Dodaj kroki (akcje)
            if ($request->has('steps')) {
                foreach ($request->steps as $index => $step) {
                    RecipeStep::create([
                        'recipe_id' => $recipe->id,
                        'order' => $order++,
                        'type' => $step['type'],
                        'action_name' => $step['action_name'] ?? null,
                        'action_description' => $step['action_description'] ?? null,
                        'duration' => $step['duration'] ?? null,
                        'ingredient_id' => null,
                        'quantity' => null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('recipes.index')->with('success', 'Receptura została utworzona!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Błąd podczas tworzenia receptury: ' . $e->getMessage());
        }
    }

    // EDYCJA RECEPTURY
    public function edit(Recipe $recipe)
    {
        $this->checkRecipeAccess();
        $recipe->load('steps.ingredient');
        $ingredients = Ingredient::orderBy('name')->get();
        return view('recipes.edit', compact('recipe', 'ingredients'));
    }

    public function update(Request $request, Recipe $recipe)
    {
        $this->checkRecipeAccess();
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'output_quantity' => 'required|integer|min:1',
            'flour' => 'required|array|min:1',
            'flour.*.ingredient_id' => 'required|exists:ingredients,id',
            'flour.*.weight' => 'required|numeric|min:0.01',
            'flour.*.percentage' => 'required|numeric|min:0.01',
            'ingredient.*.ingredient_id' => 'nullable|exists:ingredients,id',
            'ingredient.*.quantity' => 'nullable|numeric|min:0.01',
            'ingredient.*.percentage' => 'nullable|numeric|min:0.01',
        ]);

        DB::beginTransaction();
        try {
            // Sprawdź czy suma procentów mąki = 100%
            $flourPercentageSum = collect($request->flour)->sum('percentage');
            if (abs($flourPercentageSum - 100) > 0.01) {
                return back()->with('error', 'Suma procentów mąki musi wynosić 100%!');
            }
            
            $recipe->update([
                'name' => $request->name,
                'description' => $request->description,
                'output_quantity' => $request->output_quantity,
                'total_steps' => 0,
                'estimated_time' => 0
            ]);

            // Usuń stare kroki i utwórz nowe
            $recipe->steps()->delete();
            
            $order = 1;
            
            // Dodaj mąkę
            foreach ($request->flour as $flour) {
                RecipeStep::create([
                    'recipe_id' => $recipe->id,
                    'order' => $order++,
                    'type' => 'ingredient',
                    'ingredient_id' => $flour['ingredient_id'],
                    'quantity' => $flour['weight'],
                    'percentage' => $flour['percentage'] ?? 0,
                    'is_flour' => true,
                ]);
            }
            
            // Dodaj pozostałe składniki
            if ($request->has('ingredient')) {
                foreach ($request->ingredient as $ingredient) {
                    if (isset($ingredient['ingredient_id']) && isset($ingredient['quantity']) && isset($ingredient['percentage'])) {
                        RecipeStep::create([
                            'recipe_id' => $recipe->id,
                            'order' => $order++,
                            'type' => 'ingredient',
                            'ingredient_id' => $ingredient['ingredient_id'],
                            'quantity' => $ingredient['quantity'],
                            'percentage' => $ingredient['percentage'] ?? 0,
                            'is_flour' => false,
                        ]);
                    }
                }
            }
            
            // Dodaj kroki (akcje)
            if ($request->has('steps')) {
                foreach ($request->steps as $index => $step) {
                    RecipeStep::create([
                        'recipe_id' => $recipe->id,
                        'order' => $order++,
                        'type' => $step['type'],
                        'action_name' => $step['action_name'] ?? null,
                        'action_description' => $step['action_description'] ?? null,
                        'duration' => $step['duration'] ?? null,
                        'ingredient_id' => null,
                        'quantity' => null,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('recipes.index')->with('success', 'Receptura została zaktualizowana!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Błąd podczas aktualizacji: ' . $e->getMessage());
        }
    }

    public function destroy(Recipe $recipe)
    {
        $this->checkRecipeAccess();
        $recipe->delete();
        return redirect()->route('recipes.index')->with('success', 'Receptura została usunięta!');
    }
}
