<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventaires', function (Blueprint $table) {
            $table->id();
            $table->string('libelle');
            $table->unsignedSmallInteger('annee');
            $table->string('statut')->default('en_cours');
            $table->unsignedBigInteger('cree_par');
            $table->timestamps();
        });

        Schema::create('inventaire_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventaire_id')->constrained('inventaires')->cascadeOnDelete();
            $table->unsignedBigInteger('bien_id');
            $table->string('bien_code')->nullable();
            $table->string('bien_designation')->nullable();
            $table->string('constat');
            $table->string('resultat');
            $table->text('commentaire')->nullable();
            $table->timestamps();
            $table->unique(['inventaire_id', 'bien_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventaire_lignes');
        Schema::dropIfExists('inventaires');
    }
};