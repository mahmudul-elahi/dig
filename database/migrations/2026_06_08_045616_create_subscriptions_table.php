<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('revenuecat_event_type');
            $table->string('product_id');
            $table->string('transaction_id')->nullable();
            $table->string('store')->nullable();
            $table->string('period_type')->nullable();
            $table->timestamp('purchased_at')->nullable();
            $table->timestamp('expiration_at')->nullable();
            $table->timestamp('auto_resume_at')->nullable();
            $table->text('original_payload')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
