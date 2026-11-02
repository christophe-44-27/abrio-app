<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A household belongs to its owner (households.user_id); other people
        // join it through this column. One household per user, at most.
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('joined_household_id')->nullable()->after('province')
                ->constrained('households')->nullOnDelete();
        });

        // Bumped on every save, so two editors cannot silently overwrite each other.
        // Public verdicts link: random token, null while sharing is off.
        Schema::table('households', function (Blueprint $table) {
            $table->unsignedInteger('revision')->default(0)->after('housings');
            $table->string('share_token', 64)->nullable()->unique()->after('revision');
        });

        Schema::create('household_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('household_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invited_by')->constrained('users')->cascadeOnDelete();
            $table->string('email');
            $table->string('token', 64)->unique();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['household_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('household_invitations');

        Schema::table('households', function (Blueprint $table) {
            $table->dropColumn(['revision', 'share_token']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('joined_household_id');
        });
    }
};
