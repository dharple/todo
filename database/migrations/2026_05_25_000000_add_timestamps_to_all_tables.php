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
 * Adds Laravel timestamps to all three entity tables.
 */
return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('item', function (Blueprint $table) {
            $table->renameColumn('created', 'created_at');
            $table->renameColumn('completed', 'completed_at');
            $table->datetime('updated_at')->nullable()->after('created_at');
        });

        Schema::table('section', function (Blueprint $table) {
            $table->datetime('created_at')->nullable()->after('user_id');
            $table->datetime('updated_at')->nullable()->after('created_at');
        });

        Schema::table('user', function (Blueprint $table) {
            $table->datetime('created_at')->nullable()->after('username');
            $table->datetime('updated_at')->nullable()->after('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('item', function (Blueprint $table) {
            $table->dropColumn('updated_at');
            $table->renameColumn('completed_at', 'completed');
            $table->renameColumn('created_at', 'created');
        });

        Schema::table('section', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });

        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
