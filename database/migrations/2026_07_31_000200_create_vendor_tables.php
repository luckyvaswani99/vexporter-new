<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('legal_name')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->text('about')->nullable();

            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country_code', 2)->default('IN');

            $table->string('gst_number')->nullable();
            $table->string('pan')->nullable();
            $table->string('iec_code')->nullable();
            $table->string('cin')->nullable();

            $table->string('status')->default('pending')->index();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('rejection_reason')->nullable();

            $table->decimal('commission_percent', 5, 2)->nullable();
            $table->unsignedBigInteger('min_order_value')->nullable();
            $table->unsignedSmallInteger('response_time_hours')->nullable();

            $table->decimal('rating_cache', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('products_count_cache')->default(0);

            // Presentation helpers until real logos are uploaded.
            $table->string('avatar_gradient')->default('from-gray-500 to-gray-700');
            $table->string('tag_tone')->default('gray');

            $table->string('payout_method')->nullable();
            $table->text('payout_details')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('role')->default('staff');
            $table->timestamps();

            $table->unique(['vendor_id', 'user_id']);
        });

        Schema::create('vendor_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('label')->nullable();
            $table->string('number')->nullable();
            $table->string('issuing_authority')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_note')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->index(['vendor_id', 'status']);
        });

        Schema::create('vendor_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->string('account_holder');
            $table->text('account_no');
            $table->string('ifsc')->nullable();
            $table->string('swift')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('currency', 3)->default('INR');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_kyc_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_kyc_logs');
        Schema::dropIfExists('vendor_bank_accounts');
        Schema::dropIfExists('vendor_documents');
        Schema::dropIfExists('vendor_users');
        Schema::dropIfExists('vendors');
    }
};
