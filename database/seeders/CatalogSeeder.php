<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProductAttribute;
use App\Models\Vertical;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    /**
     * Verticals, their categories, and the vertical-specific attribute sets.
     * Icon/gradient values come straight from the approved design.
     */
    public function run(): void
    {
        $verticals = [
            [
                'name' => 'Main Store',
                'slug' => 'main-store',
                'icon' => 'fa-bag-shopping',
                'watermark_icon' => 'fa-store',
                'gradient_class' => 'gradient-main',
                'chip_class' => 'bg-gray-100 text-gray-600',
                'accent' => 'gray',
                'sort_order' => 1,
                'tagline' => 'General merchandise, electronics, textiles, machinery, consumer goods and more from trusted global vendors.',
                'categories' => [
                    ['Electronics', 'fa-laptop', 'text-green-200', 'from-green-50 to-emerald-50'],
                    ['Textiles & Garments', 'fa-shirt', 'text-purple-200', 'from-purple-50 to-pink-50'],
                    ['Industrial Machinery', 'fa-gears', 'text-slate-200', 'from-slate-50 to-gray-100'],
                    ['Packaging Material', 'fa-box-open', 'text-amber-200', 'from-amber-50 to-yellow-50'],
                    ['Agro Commodities', 'fa-wheat-awn', 'text-lime-200', 'from-lime-50 to-green-50'],
                    ['Home & Furnishing', 'fa-couch', 'text-rose-200', 'from-rose-50 to-orange-50'],
                ],
            ],
            [
                'name' => 'Pharma',
                'slug' => 'pharma',
                'icon' => 'fa-prescription-bottle-medical',
                'watermark_icon' => 'fa-capsules',
                'gradient_class' => 'gradient-pharma',
                'chip_class' => 'bg-red-50 text-brand-red',
                'accent' => 'red',
                'sort_order' => 2,
                'tagline' => 'APIs, formulations, surgical instruments and lab equipment with WHO-GMP, FDA and EU-GMP certified vendors.',
                'categories' => [
                    ['Active Pharmaceutical Ingredients', 'fa-pills', 'text-blue-200', 'from-blue-50 to-indigo-50'],
                    ['Formulations', 'fa-tablets', 'text-pink-200', 'from-pink-50 to-rose-50'],
                    ['Surgical Instruments', 'fa-syringe', 'text-green-200', 'from-green-50 to-emerald-50'],
                    ['Lab Equipment', 'fa-flask', 'text-purple-200', 'from-purple-50 to-violet-50'],
                    ['Nutraceuticals', 'fa-leaf', 'text-lime-200', 'from-lime-50 to-green-50'],
                    ['Medical Disposables', 'fa-kit-medical', 'text-sky-200', 'from-sky-50 to-blue-50'],
                ],
            ],
            [
                'name' => 'Solar',
                'slug' => 'solar',
                'icon' => 'fa-solar-panel',
                'watermark_icon' => 'fa-sun',
                'gradient_class' => 'gradient-solar',
                'chip_class' => 'bg-orange-50 text-orange-600',
                'accent' => 'orange',
                'sort_order' => 3,
                'tagline' => 'Solar panels, inverters, batteries, mounting systems and complete EPC solutions from Tier-1 manufacturers.',
                'categories' => [
                    ['Solar Panels', 'fa-solar-panel', 'text-yellow-300', 'from-yellow-50 to-orange-50'],
                    ['Inverters', 'fa-bolt', 'text-indigo-300', 'from-indigo-50 to-purple-50'],
                    ['Battery Storage', 'fa-car-battery', 'text-cyan-300', 'from-cyan-50 to-blue-50'],
                    ['Mounting Structures', 'fa-grip-lines', 'text-slate-300', 'from-slate-50 to-gray-100'],
                    ['Cables & Accessories', 'fa-plug', 'text-orange-300', 'from-orange-50 to-amber-50'],
                    ['EPC Solutions', 'fa-clipboard-list', 'text-teal-300', 'from-teal-50 to-green-50'],
                ],
            ],
        ];

        foreach ($verticals as $definition) {
            $categories = $definition['categories'];
            unset($definition['categories']);

            $vertical = Vertical::updateOrCreate(['slug' => $definition['slug']], $definition);

            foreach ($categories as $index => [$name, $icon, $iconColor, $gradient]) {
                Category::updateOrCreate(
                    ['slug' => Str::slug($name)],
                    [
                        'vertical_id' => $vertical->id,
                        'name' => $name,
                        'icon' => $icon,
                        'icon_color' => $iconColor,
                        'image_gradient' => $gradient,
                        'is_featured' => $index < 3,
                        'sort_order' => $index + 1,
                    ],
                );
            }
        }

        $this->seedAttributes();
    }

    /** Filterable specs buyers actually shop on in each vertical. */
    private function seedAttributes(): void
    {
        $sets = [
            'pharma' => [
                ['cas_number', 'CAS Number', 'text', null, null],
                ['grade', 'Grade', 'select', null, ['BP', 'USP', 'EP', 'IP', 'JP']],
                ['purity', 'Purity', 'number', '%', null],
                ['dosage_form', 'Dosage Form', 'select', null, ['Tablet', 'Capsule', 'Injection', 'Syrup', 'Powder', 'Ointment']],
                ['strength', 'Strength', 'text', null, null],
                ['pack_size', 'Pack Size', 'text', null, null],
                ['therapeutic_category', 'Therapeutic Category', 'text', null, null],
                ['shelf_life_months', 'Shelf Life', 'number', 'months', null],
                ['storage_conditions', 'Storage Conditions', 'text', null, null],
                ['gmp_standard', 'GMP Standard', 'select', null, ['WHO-GMP', 'EU-GMP', 'US-FDA', 'PIC/S']],
                ['dmf_available', 'DMF / CEP Available', 'bool', null, null],
                ['prescription_required', 'Prescription Required', 'bool', null, null],
            ],
            'solar' => [
                ['wattage', 'Wattage', 'number', 'Wp', null],
                ['cell_type', 'Cell Type', 'select', null, ['Mono PERC', 'TOPCon', 'Polycrystalline', 'Thin Film', 'Bifacial']],
                ['efficiency', 'Module Efficiency', 'number', '%', null],
                ['voc', 'Open Circuit Voltage', 'number', 'V', null],
                ['isc', 'Short Circuit Current', 'number', 'A', null],
                ['inverter_phase', 'Inverter Phase', 'select', null, ['Single Phase', 'Three Phase']],
                ['inverter_capacity', 'Inverter Capacity', 'number', 'kW', null],
                ['battery_chemistry', 'Battery Chemistry', 'select', null, ['LiFePO4', 'Li-ion', 'Lead Acid', 'Gel']],
                ['battery_capacity', 'Battery Capacity', 'number', 'Ah', null],
                ['cycle_life', 'Cycle Life', 'number', 'cycles', null],
                ['ip_rating', 'IP Rating', 'text', null, null],
                ['product_warranty', 'Product Warranty', 'number', 'years', null],
                ['performance_warranty', 'Performance Warranty', 'number', 'years', null],
                ['container_qty', 'Qty per 40ft Container', 'number', 'pcs', null],
            ],
            'main-store' => [
                ['material', 'Material', 'text', null, null],
                ['color', 'Colour', 'text', null, null],
                ['size', 'Size', 'text', null, null],
                ['voltage', 'Voltage', 'number', 'V', null],
                ['packaging', 'Packaging', 'text', null, null],
            ],
        ];

        foreach ($sets as $verticalSlug => $attributes) {
            $vertical = Vertical::where('slug', $verticalSlug)->firstOrFail();

            foreach ($attributes as $index => [$code, $label, $type, $unit, $options]) {
                ProductAttribute::updateOrCreate(
                    ['vertical_id' => $vertical->id, 'code' => $code],
                    [
                        'label' => $label,
                        'type' => $type,
                        'unit' => $unit,
                        'options' => $options,
                        'is_filterable' => in_array($type, ['select', 'number', 'bool'], true),
                        'is_comparable' => true,
                        'sort_order' => $index + 1,
                    ],
                );
            }
        }
    }
}
