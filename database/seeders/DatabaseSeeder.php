<?php

namespace Database\Seeders;

use App\Models\BuyerProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        User::updateOrCreate(
            ['email' => 'admin@vexporter.test'],
            [
                'name' => 'VEXPORTER Admin',
                'password' => Hash::make('password'),
                'type' => User::TYPE_ADMIN,
                'email_verified_at' => now(),
            ],
        )->syncRoles(RoleSeeder::ROLE_ADMIN);

        $buyer = User::updateOrCreate(
            ['email' => 'buyer@vexporter.test'],
            [
                'name' => 'Demo Buyer',
                'password' => Hash::make('password'),
                'type' => User::TYPE_BUYER,
                'email_verified_at' => now(),
            ],
        );

        $buyer->syncRoles(RoleSeeder::ROLE_BUYER);

        BuyerProfile::updateOrCreate(['user_id' => $buyer->id], [
            'company_name' => 'GlobalTrade LLC',
            'business_type' => 'Importer / Distributor',
            'country_code' => 'AE',
            'iec_code' => '0912345678',
            'annual_volume' => '$1M – $5M',
            'verified_at' => now(),
        ]);

        $this->call([
            CatalogSeeder::class,
            VendorSeeder::class,
            ProductSeeder::class,
            OrderSeeder::class,
            ContentSeeder::class,
        ]);
    }
}
