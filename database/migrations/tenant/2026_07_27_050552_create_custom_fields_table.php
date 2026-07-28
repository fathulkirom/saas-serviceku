<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('custom_fields', function (Blueprint $table) {
            $table->id();
            $table->string('module'); // customer, service, device
            $table->string('label');
            $table->string('type'); // text, number, dropdown, date, checkbox
            $table->json('options')->nullable(); // untuk dropdown
            $table->boolean('is_required')->default(false);
            $table->integer('ordering')->default(0);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('branch_id');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });

        Schema::create('custom_field_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('custom_field_id');
            $table->unsignedBigInteger('entity_id'); // id dari customer/service tergantung module
            $table->text('value')->nullable();
            $table->timestamps();

            $table->foreign('custom_field_id')->references('id')->on('custom_fields')->onDelete('cascade');
            $table->index(['custom_field_id', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('custom_field_values');
        Schema::dropIfExists('custom_fields');
    }
};
