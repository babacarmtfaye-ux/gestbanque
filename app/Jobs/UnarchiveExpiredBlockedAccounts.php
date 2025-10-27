<?php

namespace App\Jobs;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UnarchiveExpiredBlockedAccounts implements ShouldQueue
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
        Log::info('Starting UnarchiveExpiredBlockedAccounts job');

        // Trouver tous les comptes archivés dont la date de déblocage prévue est dépassée
        // Ces comptes étaient bloqués et ont été archivés automatiquement
        $expiredArchivedAccounts = Compte::where('statut', 'archive')
            ->whereNotNull('dateDeblocagePrevue')
            ->where('dateDeblocagePrevue', '<', now())
            ->where(function ($query) {
                $query->where('metadata->archivedReason', 'Blocage expiré automatiquement')
                      ->orWhere('metadata->archivedReason', 'like', '%expiré%');
            })
            ->get();

        $unarchivedCount = 0;

        foreach ($expiredArchivedAccounts as $compte) {
            // Désarchiver le compte - le remettre en statut actif
            $compte->update([
                'statut' => 'actif',
                'dateDeblocage' => now(),
                'metadata' => array_merge($compte->metadata ?? [], [
                    'unarchivedAt' => now(),
                    'unarchivedReason' => 'Blocage expiré - désarchivage automatique',
                    'version' => ($compte->metadata['version'] ?? 1) + 1,
                ])
            ]);

            // Désarchiver les transactions associées
            $compte->transactions()->update([
                'metadata' => \DB::raw("JSON_REMOVE(JSON_SET(COALESCE(metadata, '{}'), '$.unarchivedAt', '" . now() . "'), '$.archived', '$.archivedAt')")
            ]);

            $unarchivedCount++;
            Log::info("Unarchived account: {$compte->numeroCompte}");
        }

        Log::info("UnarchiveExpiredBlockedAccounts job completed. Unarchived {$unarchivedCount} accounts.");
    }
}
