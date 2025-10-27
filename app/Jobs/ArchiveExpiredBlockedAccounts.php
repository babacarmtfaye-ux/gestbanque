<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ArchiveExpiredBlockedAccounts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting ArchiveExpiredBlockedAccounts job');

        // Trouver tous les comptes bloqués dont la date de déblocage prévue est dépassée
        $expiredBlockedAccounts = Compte::where('statut', 'bloque')
            ->where('dateDeblocagePrevue', '<', now())
            ->get();

        $archivedCount = 0;

        foreach ($expiredBlockedAccounts as $compte) {
            // Archiver le compte
            $compte->update([
                'statut' => 'archive',
                'metadata' => array_merge($compte->metadata ?? [], [
                    'archivedAt' => now(),
                    'archivedReason' => 'Blocage expiré automatiquement',
                    'version' => ($compte->metadata['version'] ?? 1) + 1,
                ])
            ]);

            // Archiver les transactions associées
            $compte->transactions()->update([
                'metadata' => \DB::raw("JSON_SET(COALESCE(metadata, '{}'), '$.archived', true, '$.archivedAt', '" . now() . "')")
            ]);

            $archivedCount++;
            Log::info("Archived account: {$compte->numeroCompte}");
        }

        Log::info("ArchiveExpiredBlockedAccounts job completed. Archived {$archivedCount} accounts.");
    }
}
