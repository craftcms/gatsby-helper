<?php

declare(strict_types=1);

namespace CraftCms\GatsbyHelper\Database\Migrations;

use CraftCms\Cms\Database\Migration;
use CraftCms\GatsbyHelper\Deltas;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** @since 1.0.0 */
class Install extends Migration
{
    public function up(): void
    {
        Schema::create(Deltas::TABLE, function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('elementId')->nullable();
            $table->integer('siteId');
            $table->string('typeName');
            $table->dateTime('dateDeleted');

            $table->unique(['elementId', 'siteId']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists(Deltas::TABLE);
    }
}
