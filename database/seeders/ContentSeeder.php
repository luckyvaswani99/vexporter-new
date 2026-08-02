<?php

namespace Database\Seeders;

use App\Models\Currency;
use App\Models\FlashSale;
use App\Models\Page;
use App\Models\Product;
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class ContentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCurrencies();
        $this->seedTestimonials();
        $this->seedFlashSale();
        $this->seedPages();
    }

    private function seedCurrencies(): void
    {
        $currencies = [
            ['USD', '$', 'US Dollar', 1],
            ['INR', '₹', 'Indian Rupee', 83.20],
            ['EUR', '€', 'Euro', 0.92],
            ['GBP', '£', 'Pound Sterling', 0.79],
            ['AED', 'AED ', 'UAE Dirham', 3.67],
        ];

        foreach ($currencies as [$code, $symbol, $name, $rate]) {
            Currency::updateOrCreate(['code' => $code], [
                'symbol' => $symbol,
                'name' => $name,
                'rate_to_usd' => $rate,
                'is_active' => true,
            ]);
        }
    }

    private function seedTestimonials(): void
    {
        $testimonials = [
            [
                'name' => 'John Davidson',
                'initials' => 'JD',
                'designation' => 'Procurement Head, MediCorp UK',
                'country_code' => 'GB',
                'avatar_gradient' => 'from-blue-400 to-blue-600',
                'rating' => 5,
                'body' => 'VEXPORTER transformed our procurement process. We sourced quality pharma APIs from verified Indian manufacturers at 30% lower cost than European suppliers.',
                'sort_order' => 1,
            ],
            [
                'name' => 'Amina Mohamed',
                'initials' => 'AM',
                'designation' => 'CEO, SunGrid Africa',
                'country_code' => 'KE',
                'avatar_gradient' => 'from-green-400 to-green-600',
                'rating' => 5,
                'body' => 'As a solar EPC company in Kenya, finding reliable Tier-1 panel suppliers was challenging. VEXPORTER connected us directly with MNRE-certified manufacturers.',
                'sort_order' => 2,
            ],
            [
                'name' => 'Rajesh Kumar',
                'initials' => 'RK',
                'designation' => 'Director, GlobalTrade LLC, Dubai',
                'country_code' => 'AE',
                'avatar_gradient' => 'from-orange-400 to-red-500',
                'rating' => 4.5,
                'body' => 'The escrow payment system gives us confidence. We have placed 50+ orders through VEXPORTER and every shipment was tracked end-to-end. Highly recommended!',
                'sort_order' => 3,
            ],
        ];

        foreach ($testimonials as $testimonial) {
            Testimonial::updateOrCreate(
                ['name' => $testimonial['name']],
                $testimonial + ['is_featured' => true],
            );
        }
    }

    private function seedFlashSale(): void
    {
        $sale = FlashSale::updateOrCreate(
            ['title' => 'Mega Export Deal: Up to 40% Off'],
            [
                'description' => 'Limited-time bulk pricing on pharma APIs, solar panels, and electronics. Valid for verified B2B buyers only.',
                'discount' => 40,
                'starts_at' => now()->subDay(),
                'ends_at' => now()->addDays(2)->endOfDay(),
                'is_active' => true,
            ],
        );

        $products = Product::visible()->featured()->take(4)->get();

        $sale->products()->syncWithoutDetaching(
            $products->mapWithKeys(fn (Product $product) => [
                $product->id => ['sale_price' => (int) round($product->base_price * 0.6)],
            ])->all(),
        );
    }

    private function seedPages(): void
    {
        $pages = [
            'about' => 'About Us',
            'careers' => 'Careers',
            'press' => 'Press',
            'partners' => 'Partners',
            'buyer-guide' => 'Buyer Guide',
            'vendor-guide' => 'Vendor Guide',
            'shipping-info' => 'Shipping Information',
            'returns' => 'Returns & Refunds',
            'privacy' => 'Privacy Policy',
            'terms' => 'Terms of Service',
            'cookies' => 'Cookie Policy',
        ];

        foreach ($pages as $slug => $title) {
            Page::updateOrCreate(['slug' => $slug], [
                'title' => $title,
                'body' => "<p>{$title} content is being finalised by the VEXPORTER team.</p>",
                'is_published' => true,
            ]);
        }
    }
}
