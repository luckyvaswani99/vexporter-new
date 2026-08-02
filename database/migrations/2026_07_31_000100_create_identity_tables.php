<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('type')->default('buyer')->index();
            $table->string('phone')->nullable();
            $table->string('avatar')->nullable();
            $table->string('locale', 8)->default('en');
            $table->string('default_currency', 3)->default('USD');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
        });

        Schema::create('buyer_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('company_name')->nullable();
            $table->string('business_type')->nullable();
            $table->string('country_code', 2)->nullable();
            $table->string('gst_number')->nullable();
            $table->string('iec_code')->nullable();
            $table->string('import_license_no')->nullable();
            $table->string('drug_license_no')->nullable();
            $table->timestamp('drug_license_expires_at')->nullable();
            $table->string('annual_volume')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique('user_id');
        });

        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('contact_name');
            $table->string('company')->nullable();
            $table->string('line1');
            $table->string('line2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postcode', 24)->nullable();
            $table->string('country_code', 2);
            $table->string('phone')->nullable();
            $table->string('tax_id')->nullable();
            $table->boolean('is_default_billing')->default(false);
            $table->boolean('is_default_shipping')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default_shipping']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
        Schema::dropIfExists('buyer_profiles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'type', 'phone', 'avatar', 'locale', 'default_currency', 'is_active', 'last_login_at',
            ]);
        });
    }
};
