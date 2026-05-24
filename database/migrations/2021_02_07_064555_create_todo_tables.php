<?php

/**
 * This file is part of the TodoList package.
 *
 * (c) Doug Harple <dharple@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Creates the core todo application schema.
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        if (!Schema::hasTable('user')) {
            Schema::create('user', function (Blueprint $table) {
                $table->id();
                $table->string('fullname');
                $table->string('password');
                $table->string('timezone', 128);
                $table->string('username', 32)->unique();
            });
        }

        if (!Schema::hasTable('section')) {
            Schema::create('section', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('status', 20);
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('user');
            });
        }

        if (!Schema::hasTable('item')) {
            Schema::create('item', function (Blueprint $table) {
                $table->id();
                $table->datetime('completed')->nullable();
                $table->datetime('created');
                $table->integer('priority');
                $table->unsignedBigInteger('section_id')->nullable();
                $table->foreign('section_id')->references('id')->on('section');
                $table->string('status', 20);
                $table->string('task');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('user');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('item');
        Schema::dropIfExists('section');
        Schema::dropIfExists('user');
    }
};
