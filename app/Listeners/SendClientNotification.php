<?php

namespace App\Listeners;

use App\Events\CompteCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendClientNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(CompteCreated $event): void
    {
        $compte = $event->compte;
        $user = $compte->user;

        // Générer le mot de passe temporaire (si pas déjà fait)
        $password = $this->generatePassword();

        // Envoyer l'email avec le mot de passe
        $this->sendEmailNotification($user, $compte, $password);

        // Envoyer le SMS avec le code
        $this->sendSmsNotification($user, $compte);

        // Logger l'opération
        Log::info('Notifications envoyées pour le nouveau compte', [
            'compte_id' => $compte->id,
            'user_id' => $user->id,
            'numero_compte' => $compte->numeroCompte,
        ]);
    }

    /**
     * Envoyer la notification par email
     */
    private function sendEmailNotification($user, $compte, $password): void
    {
        try {
            // Ici vous pouvez utiliser un service d'email comme Mailgun, SendGrid, etc.
            // Pour l'exemple, on log seulement
            Log::info('Email envoyé au client', [
                'email' => $user->email,
                'subject' => 'Création de votre compte bancaire',
                'body' => "Votre compte bancaire a été créé avec succès.\n\n" .
                         "Numéro de compte: {$compte->numeroCompte}\n" .
                         "Mot de passe temporaire: {$password}\n\n" .
                         "Veuillez changer votre mot de passe lors de votre première connexion."
            ]);

            // Exemple avec Laravel Mail (à implémenter)
            // Mail::to($user->email)->send(new CompteCreatedMail($compte, $password));

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi de l\'email', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Envoyer la notification par SMS
     */
    private function sendSmsNotification($user, $compte): void
    {
        try {
            // Ici vous pouvez utiliser un service SMS comme Twilio, Africa's Talking, etc.
            // Pour l'exemple, on log seulement
            Log::info('SMS envoyé au client', [
                'telephone' => $user->telephone,
                'message' => "Votre compte bancaire {$compte->numeroCompte} a été créé. " .
                           "Code de vérification: {$user->code}. " .
                           "Conservez ce code pour votre première connexion."
            ]);

            // Exemple avec un service SMS (à implémenter)
            // $this->smsService->send($user->telephone, $message);

        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'envoi du SMS', [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Générer un mot de passe temporaire
     */
    private function generatePassword(): string
    {
        return 'Temp' . mt_rand(1000, 9999) . '!';
    }
}
