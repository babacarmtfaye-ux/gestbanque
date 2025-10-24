<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comptes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('numeroCompte')->unique();
            $table->string('titulaire');
            $table->enum('type', ['epargne', 'cheque']);
            $table->decimal('solde', 15, 2)->default(0);
            $table->string('devise');
            $table->timestamp('dateCreation');
            $table->enum('statut', ['actif', 'bloque', 'ferme'])->default('actif');
            $table->text('motifBlocage')->nullable();
            $table->uuid('user_id');
            $table->json('metadata')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('user_id');
            $table->index('type');
            $table->index('statut');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comptes');
    }
};
