<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketsTable extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('description');
            $table->enum('statut', ['Ouvert', 'En cours', 'Résolu', 'Fermé'])->default('Ouvert');
            $table->enum('priorite', ['Faible', 'Moyenne', 'Élevée', 'Critique'])->default('Moyenne');
            $table->timestamp('date_creation')->useCurrent();
            $table->timestamp('date_mise_a_jour')->useCurrent()->useCurrentOnUpdate();
            $table->foreignId('id_employe')->constrained('users');
            $table->foreignId('id_technicien')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
}
