<?php

use Botble\ACL\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->foreignId('created_by_id')->nullable()->default(0);
            $table->string('created_by_type', 255)->default(addslashes(User::class));
        });
    }

    public function down(): void
    {
        Schema::table('ec_products', function (Blueprint $table) {
            $table->dropColumn(['created_by_id', 'created_by_type']);
        });
    }
};
