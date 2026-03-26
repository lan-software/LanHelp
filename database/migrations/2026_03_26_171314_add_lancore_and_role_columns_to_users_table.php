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
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('lancore_user_id')->nullable()->unique()->after('id');
            $table->string('display_name')->nullable()->after('name');
            $table->string('avatar_url')->nullable()->after('email');
            $table->timestamp('lancore_synced_at')->nullable()->after('avatar_url');
            $table->string('role')->default('user')->after('lancore_synced_at');

            // Allow nullable for LanCore shadow users (no local password or email)
            $table->string('password')->nullable()->change();
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['lancore_user_id', 'display_name', 'avatar_url', 'lancore_synced_at', 'role']);
            $table->string('password')->nullable(false)->change();
            $table->string('email')->nullable(false)->change();
        });
    }
};
