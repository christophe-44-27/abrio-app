<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A ban targets an email, an IP address, or both: a visitor matching
        // either one is blocked.
        Schema::create('bans', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable()->unique();
            $table->string('ip_address', 45)->nullable()->unique();
            $table->text('reason')->nullable();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_ip', 45)->nullable()->after('province');
            $table->string('last_login_ip', 45)->nullable()->after('registration_ip');
            $table->timestamp('terms_accepted_at')->nullable()->after('last_login_ip');
            $table->timestamp('marketing_opt_in_at')->nullable()->after('terms_accepted_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['registration_ip', 'last_login_ip', 'terms_accepted_at', 'marketing_opt_in_at']);
        });

        Schema::dropIfExists('bans');
    }
};
