<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class LegalPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'terms',
                'title' => 'Terms of Service',
                'seo_title' => 'Terms of Service - VEXPORTER Global B2B',
                'seo_description' => 'Terms and conditions governing the VEXPORTER B2B trade platform.',
                'body' => '<h1>Terms of Service</h1><p>Welcome to VEXPORTER. By registering or trading on this platform, buyers and vendors agree to international commercial trade guidelines, Incoterms 2020 rules, and applicable regulatory frameworks.</p>',
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy Policy',
                'seo_title' => 'Privacy Policy - VEXPORTER',
                'seo_description' => 'How VEXPORTER protects commercial and personal identity data.',
                'body' => '<h1>Privacy Policy</h1><p>VEXPORTER protects business identity, corporate documents, and statutory records under strict 256-bit SSL encryption and compliance standards.</p>',
            ],
            [
                'slug' => 'refunds',
                'title' => 'Refund & Return Policy (RMA)',
                'seo_title' => 'Refund & Dispute Policy - VEXPORTER',
                'seo_description' => 'Escrow protection and RMA dispute policies for buyers and vendors.',
                'body' => '<h1>Refund & Dispute Policy</h1><p>All transactions are backed by VEXPORTER Escrow Hold. Buyers may raise disputes within 7 days of cargo receipt.</p>',
            ],
            [
                'slug' => 'export-compliance',
                'title' => 'Export Compliance & Documentation',
                'seo_title' => 'Export Compliance & Customs Guidelines - VEXPORTER',
                'seo_description' => 'Statutory export declarations, LUT zero-rated tax rules, and pharma COA mandates.',
                'body' => '<h1>Export Compliance</h1><p>Exports on VEXPORTER comply with DGFT, customs regulations, and zero-rated IGST LUT provisions.</p>',
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(['slug' => $page['slug']], $page);
        }
    }
}
