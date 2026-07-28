<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_settings', function (Blueprint $table) {
            $table->id();
            $table->string('pkp_status')->default('non_pkp'); // non_pkp, pkp
            $table->decimal('ppn_percentage', 5, 2)->default(11.00);
            $table->string('npwp')->nullable();
            $table->string('pkp_number')->nullable();
            $table->unsignedBigInteger('branch_id');
            $table->timestamps();

            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_settings');
    }
};
