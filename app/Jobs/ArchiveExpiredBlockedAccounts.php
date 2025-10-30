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

        $now = now();
        $processedCount = 0;

        // 1. Activer les blocages programmés dont la date de début est atteinte
        $scheduledBlocks = Compte::where('statut', 'actif')
            ->whereNotNull('metadata->dateDebutBlocageProgramme')
            ->where('metadata->dateDebutBlocageProgramme', '<=', $now)
            ->where('metadata->blocageProgramme', true)
            ->get();

        foreach ($scheduledBlocks as $compte) {
            $compte->update([
                'statut' => 'bloque',
                'dateBlocage' => $now,
                'metadata' => array_merge($compte->metadata ?? [], [
                    'derniereModification' => $now,
                    'version' => ($compte->metadata['version'] ?? 1) + 1,
                    'blocageActive' => true,
                ])
            ]);

            $processedCount++;
            Log::info("Activated scheduled block for account: {$compte->numeroCompte}");
        }

        // 2. Archiver les comptes bloqués dont la date de déblocage prévue est dépassée
        $expiredBlockedAccounts = Compte::where('statut', 'bloque')
            ->where('dateDeblocagePrevue', '<', $now)
            ->get();

        $archivedCount = 0;

        foreach ($expiredBlockedAccounts as $compte) {
            // Archiver le compte
            $compte->update([
                'statut' => 'archive',
                'metadata' => array_merge($compte->metadata ?? [], [
                    'archivedAt' => $now,
                    'archivedReason' => 'Blocage expiré automatiquement',
                    'version' => ($compte->metadata['version'] ?? 1) + 1,
                ])
            ]);

            // Archiver les transactions associées
            $compte->transactions()->update([
                'metadata' => \DB::raw("jsonb_set(COALESCE(metadata, '{}'), '{archived}', 'true', true) || jsonb_set(COALESCE(metadata, '{}'), '{archivedAt}', '\"' || '" . $now . "' || '\"', true)")
            ]);

            $archivedCount++;
            Log::info("Archived expired blocked account: {$compte->numeroCompte}");
        }

        Log::info("ArchiveExpiredBlockedAccounts job completed. Activated {$processedCount} scheduled blocks, archived {$archivedCount} expired accounts.");
    }
}
