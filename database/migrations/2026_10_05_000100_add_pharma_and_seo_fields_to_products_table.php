<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('generic_name')->nullable()->after('name');
            $table->string('brand_name')->nullable()->after('generic_name');
            $table->string('strength', 100)->nullable();
            $table->string('dosage_form', 100)->nullable();
            $table->string('pack_size', 150)->nullable();
            $table->json('ingredients')->nullable();
            $table->string('cas_number', 50)->nullable();
            $table->string('pharmacopoeia_standard', 50)->nullable();
            $table->string('schedule_class', 50)->nullable();
            $table->string('manufacturer')->nullable();
            $table->char('country_of_origin', 2)->nullable();
            $table->string('storage_conditions')->nullable();
            $table->unsignedSmallInteger('shelf_life_months')->nullable();
            $table->boolean('is_cold_chain')->default(false);
            $table->boolean('humidity_sensitive')->default(false);
            $table->boolean('light_sensitive')->default(false);
            $table->text('seo_keywords')->nullable();
            $table->string('focus_keyword')->nullable();

            $table->index('generic_name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['generic_name']);
            $table->dropColumn([
                'generic_name', 'brand_name', 'strength', 'dosage_form', 'pack_size', 'ingredients',
                'cas_number', 'pharmacopoeia_standard', 'schedule_class', 'manufacturer',
                'country_of_origin', 'storage_conditions', 'shelf_life_months', 'is_cold_chain',
                'humidity_sensitive', 'light_sensitive', 'seo_keywords', 'focus_keyword',
            ]);
        });
    }
};
