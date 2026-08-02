<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorDocument;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VendorSeeder extends Seeder
{
    /** The four vendors shown in the approved design, plus a wider bench. */
    public const VENDORS = [
        ['MediChem Labs', 'Mumbai', 'Maharashtra', 'from-blue-500 to-blue-600', 'blue', 4.9, 1240, ['FDA', 'WHO-GMP', 'EU-GMP'], 8],
        ['SunPower India', 'Hyderabad', 'Telangana', 'from-yellow-500 to-orange-500', 'yellow', 4.8, 856, ['MNRE', 'ALMM', 'IEC'], 6],
        ['TexGlobal Inc', 'Surat', 'Gujarat', 'from-purple-500 to-pink-500', 'purple', 4.7, 3420, ['OEKO-TEX', 'GOTS'], 12],
        ['ElectroMax Corp', 'Bangalore', 'Karnataka', 'from-green-500 to-emerald-600', 'green', 4.6, 2150, ['ISO 9001', 'RoHS'], 10],
        ['SurgiTech India', 'Chennai', 'Tamil Nadu', 'from-emerald-500 to-teal-600', 'green', 4.8, 640, ['ISO', 'CE'], 9],
        ['LabPro Solutions', 'Pune', 'Maharashtra', 'from-violet-500 to-purple-600', 'purple', 4.4, 380, ['CE', 'ISO 9001'], 14],
        ['CureAll Pharma', 'Ahmedabad', 'Gujarat', 'from-pink-500 to-rose-500', 'pink', 4.7, 920, ['WHO-GMP', 'FDA'], 7],
        ['VoltStorage Co.', 'Noida', 'Uttar Pradesh', 'from-cyan-500 to-blue-500', 'cyan', 4.6, 410, ['IEC', 'BIS'], 11],
        ['InvertTech Solar', 'Jaipur', 'Rajasthan', 'from-indigo-500 to-purple-500', 'indigo', 5.0, 295, ['BIS', 'IEC'], 5],
        ['GreenField Solar', 'Kochi', 'Kerala', 'from-teal-500 to-green-600', 'teal', 4.8, 180, ['ALMM', 'MNRE'], 16],
    ];

    public function run(): void
    {
        foreach (self::VENDORS as [$name, $city, $state, $gradient, $tone, $rating, $products, $certifications, $responseHours]) {
            $slug = Str::slug($name);

            $user = User::updateOrCreate(
                ['email' => $slug.'@vexporter.test'],
                [
                    'name' => $name.' Admin',
                    'password' => Hash::make('password'),
                    'type' => User::TYPE_VENDOR,
                    'email_verified_at' => now(),
                ],
            );

            $user->syncRoles(RoleSeeder::ROLE_VENDOR_OWNER);

            $vendor = Vendor::updateOrCreate(
                ['slug' => $slug],
                [
                    'user_id' => $user->id,
                    'name' => $name,
                    'legal_name' => $name.' Pvt Ltd',
                    'about' => "{$name} is a verified VEXPORTER manufacturer and exporter based in {$city}, India.",
                    'city' => $city,
                    'state' => $state,
                    'country_code' => 'IN',
                    'gst_number' => strtoupper(fake()->bothify('##???####?#Z#')),
                    'pan' => strtoupper(fake()->bothify('?????####?')),
                    'iec_code' => fake()->numerify('##########'),
                    'status' => Vendor::STATUS_APPROVED,
                    'approved_at' => now()->subMonths(fake()->numberBetween(2, 30)),
                    'response_time_hours' => $responseHours,
                    'rating_cache' => $rating,
                    'reviews_count' => (int) round($products / 6),
                    'products_count_cache' => $products,
                    'avatar_gradient' => $gradient,
                    'tag_tone' => $tone,
                ],
            );

            $vendor->staff()->syncWithoutDetaching([$user->id => ['role' => 'owner']]);

            foreach ($certifications as $certification) {
                VendorDocument::updateOrCreate(
                    ['vendor_id' => $vendor->id, 'label' => $certification],
                    [
                        'type' => Str::slug($certification, '_'),
                        'number' => strtoupper(fake()->bothify('CERT-####-????')),
                        'issuing_authority' => 'Certification Body',
                        'issued_at' => now()->subYears(2),
                        'expires_at' => now()->addYears(2),
                        'status' => VendorDocument::STATUS_VERIFIED,
                        'is_public' => true,
                    ],
                );
            }
        }

        // A couple of pending applications so the admin approval queue is not empty.
        Vendor::factory()->pending()->count(2)->create();
    }
}
