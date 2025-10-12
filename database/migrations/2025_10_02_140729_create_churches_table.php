<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('churches', function (Blueprint $table) {
            $table->id()->comment('Primary key');
            $table->string('name')->comment('Name of the church');
            $table->string('abbreviation')->nullable()->comment('Abbreviation of the church name');
            $table->text('logo_url')->nullable()->comment('URL of the church logo');
            $table->text('description')->nullable()->comment('Description of the church');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade')->comment('Foreign key to users table');
            $table->timestamps();
            // Contrainte unique : un utilisateur ne peut créer qu'une seule église
            $table->unique('created_by')->comment('Unique constraint: a user can create only one church');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('churches');
    }
};
