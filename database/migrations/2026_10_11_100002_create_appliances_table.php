<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('appliances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type')->default('OUTPUT')->comment('OUTPUT, INPUT');
            $table->string('signal_type')->default('DIGITAL')->comment('DIGITAL, PWM, ANALOG');
            $table->json('supported_pin_types')->nullable()->comment('e.g. ["DIGITAL"], ["PWM"], ["ANALOG"]');
            $table->string('icon')->nullable()->comment('Tabler icon class, e.g. ti ti-bulb');
            $table->string('image')->nullable()->comment('Path to uploaded appliance image');
            $table->string('default_state')->default('0')->comment('Initial state e.g. 0 or 1');
            $table->tinyInteger('status')->default(1)->comment('1=ACTIVE, 2=INACTIVE');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appliances');
    }
};
