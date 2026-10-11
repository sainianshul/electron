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
        Schema::create('device_pins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->cascadeOnDelete();
            $table->string('pin_name')->comment('e.g. D1, D2, D5, D6, D7');
            $table->integer('gpio_number')->comment('ESP8266 GPIO number e.g. 5, 4, 14, 12, 13');
            $table->string('pin_type')->comment('DIGITAL, PWM, ANALOG');
            $table->foreignId('appliance_id')->nullable()->constrained('appliances')->nullOnDelete();
            $table->string('custom_name')->nullable()->comment('User given name e.g. Balcony Light');
            $table->string('current_state')->default('0')->comment('e.g. 0, 1, or PWM value like 180');
            $table->boolean('is_assigned')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_pins');
    }
};
