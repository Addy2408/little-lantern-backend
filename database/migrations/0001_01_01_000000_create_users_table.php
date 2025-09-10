<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Schema, Hash, DB};

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('email')->unique();
            $table->string('password');

            $table->string('country_code', 5)->nullable();
            $table->string('mobile', 15)->nullable();
            $table->char('otp', 6)->nullable();
            $table->string('street')->nullable();
            $table->string('house_number')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pin_code', 10)->nullable();
            $table->string('avatar')->nullable();

            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();
            $table->boolean('is_active')->default(true);

            $table->enum('role', ['user', 'admin', 'moderator'])->default('user');

            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('last_login_ip', 45)->nullable();

            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        DB::table('users')->insert(
            [
                'first_name' => 'Aditya',
                'last_name' => 'Verma',
                'email' => 'adityaverma2408@gmail.com',
                'password' => Hash::make('Admin@123'),
                'country_code' => '+91',
                'mobile' => '6394304916',
                'role' => 'admin',
                'gender' => 'male',
                'dob' => '2000-08-24',
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
    }
};
