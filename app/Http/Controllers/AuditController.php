<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\CrmCompany;
use App\Models\User;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    private function ensureAuditAccess(): void
    {
        $user = auth()->user();

        if (!$user) {
            abort(403);
        }

        $isSuperAdmin = strtolower((string) $user->email) === 'proximalumine@gmail.com';
        $isAdmin = (bool) $user->is_admin;

        if (!$isSuperAdmin && !$isAdmin && !(bool) $user->can_audits) {
            abort(403, 'Brak uprawnień do modułu Audyty.');
        }
    }

    public function index()
    {
        $this->ensureAuditAccess();

        $auditsInProgress = Audit::with(['company', 'responsibleUser', 'involvedUsers'])
            ->where('status', 'w_toku')
            ->orderByDesc('created_at')
            ->get();

        $auditsCompleted = Audit::with(['company', 'responsibleUser', 'involvedUsers'])
            ->where('status', 'zakonczony')
            ->orderByDesc('created_at')
            ->get();

        return view('audits', [
            'companies' => CrmCompany::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
            'auditsInProgress' => $auditsInProgress,
            'auditsCompleted' => $auditsCompleted,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureAuditAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'company_id' => 'nullable|exists:crm_companies,id',
            'responsible_user_id' => 'required|exists:users,id',
            'involved_user_ids' => 'nullable|array',
            'involved_user_ids.*' => 'exists:users,id',
        ]);

        $audit = Audit::create([
            'name' => $validated['name'],
            'start_date' => $validated['start_date'],
            'company_id' => $validated['company_id'] ?? null,
            'responsible_user_id' => $validated['responsible_user_id'],
            'status' => 'w_toku',
            'created_by' => auth()->id(),
        ]);

        $audit->involvedUsers()->sync($validated['involved_user_ids'] ?? []);

        return redirect()->route('audits')->with('success', 'Audyt został dodany.');
    }

    public function updateStatus(Request $request, Audit $audit)
    {
        $this->ensureAuditAccess();

        $validated = $request->validate([
            'status' => 'required|in:w_toku,zakonczony',
        ]);

        $audit->update([
            'status' => $validated['status'],
        ]);

        return redirect()->route('audits')->with('success', 'Status audytu został zaktualizowany.');
    }
}
