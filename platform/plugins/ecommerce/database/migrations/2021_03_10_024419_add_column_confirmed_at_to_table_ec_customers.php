<?php

use Botble\Ecommerce\Models\Customer;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('ec_customers', function (Blueprint $table) {
            $table->dateTime('confirmed_at')->nullable();
            $table->string('email_verify_token', 120)->nullable();
        });

        Customer::query()->whereNull('confirmed_at')->update(['confirmed_at' => Carbon::now()]);
    }

    public function down(): void
    {
        Schema::table('ec_customers', function (Blueprint $table) {
            $table->dropColumn(['confirmed_at', 'email_verify_token']);
        });
    }
};
