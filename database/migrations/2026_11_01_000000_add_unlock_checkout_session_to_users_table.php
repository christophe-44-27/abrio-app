<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // The Stripe Checkout Session that unlocked the account, to find its invoice.
        Schema::table('users', function (Blueprint $table) {
            $table->string('unlock_checkout_session_id')->nullable()->after('unlocked_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('unlock_checkout_session_id');
        });
    }
};
