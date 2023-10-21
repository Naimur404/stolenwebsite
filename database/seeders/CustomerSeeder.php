<?php

namespace Database\Seeders;

use Botble\Base\Supports\BaseSeeder;
use Botble\Ecommerce\Models\Address;
use Botble\Ecommerce\Models\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class CustomerSeeder extends BaseSeeder
{
    public function run(): void
    {
        $this->uploadFiles('customers');

        $faker = fake();

        Customer::query()->truncate();
        Address::query()->truncate();

        $customers = [
            'customer@botble.com',
            'vendor@botble.com',
        ];

        $now = Carbon::now();

        foreach ($customers as $item) {
            $customer = Customer::query()->forceCreate([
                'name' => $faker->name(),
                'email' => $item,
                'password' => Hash::make('12345678'),
                'phone' => $faker->e164PhoneNumber(),
                'avatar' => 'customers/' . $faker->numberBetween(1, 10) . '.jpg',
                'dob' => Carbon::now()->subYears(rand(20, 50))->subDays(rand(1, 30)),
                'confirmed_at' => $now,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => $faker->e164PhoneNumber(),
                'email' => $customer->email,
                'country' => $faker->countryCode(),
                'state' => $faker->state(),
                'city' => $faker->city(),
                'address' => $faker->streetAddress(),
                'zip_code' => $faker->postcode(),
                'customer_id' => $customer->getKey(),
                'is_default' => true,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => $faker->e164PhoneNumber(),
                'email' => $customer->email,
                'country' => $faker->countryCode(),
                'state' => $faker->state(),
                'city' => $faker->city(),
                'address' => $faker->streetAddress(),
                'zip_code' => $faker->postcode(),
                'customer_id' => $customer->getKey(),
                'is_default' => false,
            ]);
        }

        for ($i = 0; $i < 8; $i++) {
            $customer = Customer::query()->forceCreate([
                'name' => $faker->name(),
                'email' => $faker->unique()->safeEmail(),
                'password' => Hash::make('12345678'),
                'phone' => $faker->e164PhoneNumber(),
                'avatar' => 'customers/' . ($i + 1) . '.jpg',
                'dob' => Carbon::now()->subYears(rand(20, 50))->subDays(rand(1, 30)),
                'confirmed_at' => $now,
            ]);

            Address::query()->create([
                'name' => $customer->name,
                'phone' => $faker->e164PhoneNumber(),
                'email' => $customer->email,
                'country' => $faker->countryCode(),
                'state' => $faker->state(),
                'city' => $faker->city(),
                'address' => $faker->streetAddress(),
                'zip_code' => $faker->postcode(),
                'customer_id' => $customer->getKey(),
                'is_default' => true,
            ]);
        }
    }
}
