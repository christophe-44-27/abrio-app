<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->json('housings')->nullable()->after('personal_expenses');
        });

        // Set once the one-time purchase succeeds; lifts the 3-housing limit.
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('unlocked_at')->nullable()->after('email_verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn('housings');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('unlocked_at');
        });
    }
};
