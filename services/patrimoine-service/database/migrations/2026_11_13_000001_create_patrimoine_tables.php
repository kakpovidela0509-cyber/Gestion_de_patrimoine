<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('nom')->unique();
            $table->timestamps();
        });

        Schema::create('biens', function (Blueprint $table) {
            $table->id();
            $table->string('code')->nullable()->unique();
            $table->string('designation');
            $table->string('type');
            $table->string('adresse');
            $table->decimal('superficie', 10, 2)->nullable();
            $table->date('date_acquisition')->nullable();
            $table->decimal('valeur', 15, 2)->nullable();
            $table->string('titre_propriete')->nullable();
            $table->string('etat')->default('bon');
            $table->string('statut')->default('actif');
            $table->foreignId('service_id')->nullable()->constrained('services')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('biens')->cascadeOnDelete();
            $table->string('type');
            $table->foreignId('origine_id')->nullable()->constrained('services')->nullOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained('services')->nullOnDelete();
            $table->text('motif')->nullable();
            $table->string('statut')->default('en_attente');
            $table->unsignedBigInteger('demandeur_id');
            $table->unsignedBigInteger('valide_par')->nullable();
            $table->timestamps();
        });

        Schema::create('maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bien_id')->constrained('biens')->cascadeOnDelete();
            $table->string('nature');
            $table->date('date_intervention')->nullable();
            $table->string('prestataire')->nullable();
            $table->decimal('cout', 15, 2)->nullable();
            $table->string('statut')->default('en_cours');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenances');
        Schema::dropIfExists('mouvements');
        Schema::dropIfExists('biens');
        Schema::dropIfExists('services');
    }
};
