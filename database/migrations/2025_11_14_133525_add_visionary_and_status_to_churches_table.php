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
        Schema::table('churches', function (Blueprint $table) {
            $table->string('visionary_name')->nullable()->after('abbreviation')->comment('Nom du visionnaire de l\'église');
            $table->boolean('is_active')->default(false)->after('created_by')->comment('Statut d\'activation de l\'église');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('churches', function (Blueprint $table) {
            $table->dropColumn(['visionary_name', 'is_active']);
        });
    }
};
