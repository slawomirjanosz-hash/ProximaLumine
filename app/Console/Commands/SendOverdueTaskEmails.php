<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CrmTask;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class SendOverdueTaskEmails extends Command
{
    protected $signature = 'tasks:send-overdue-emails';
    protected $description = 'Wysyła e-maile do użytkowników z zaległymi zadaniami CRM';

    public function handle(): int
    {
        if (!Schema::hasTable('crm_tasks')) {
            $this->error('Tabela crm_tasks nie istnieje.');
            return Command::FAILURE;
        }

        $overdueTasks = CrmTask::with('assignedTo')
            ->where('status', '!=', 'zakonczone')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now())
            ->whereNotNull('assigned_to')
            ->get();

        if ($overdueTasks->isEmpty()) {
            $this->info('Brak zaległych zadań.');
            return Command::SUCCESS;
        }

        // Grupuj po użytkowniku
        $grouped = $overdueTasks->groupBy('assigned_to');

        foreach ($grouped as $userId => $tasks) {
            $user = $tasks->first()->assignedTo;
            if (!$user || !$user->email) {
                continue;
            }

            $taskList = $tasks->map(fn($t) => '- ' . $t->title . ' (termin: ' . $t->due_date->format('d.m.Y') . ')')->implode("\n");

            Mail::raw(
                "Cześć {$user->name},\n\n"
                . "Masz " . $tasks->count() . " zaległe zadanie(a) do wykonania w systemie CRM ProximaLumine:\n\n"
                . $taskList . "\n\n"
                . "Zaloguj się i sprawdź: " . config('app.url') . "\n\n"
                . "— ProximaLumine",
                function ($message) use ($user, $tasks) {
                    $message->to($user->email, $user->name)
                        ->from(
                            config('mail.from.address', 'proximalumine@gmail.com'),
                            config('mail.from.name', 'ProximaLumine')
                        )
                        ->subject('⚠️ Masz ' . $tasks->count() . ' zaległe zadanie(a) w CRM');
                }
            );

            $this->info("Wysłano e-mail do: {$user->email} ({$tasks->count()} zadań)");
        }

        return Command::SUCCESS;
    }
}
