<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateLaravelReporterTables.
 *
 * CREATE TABLE `laravel_exceptions` (
 * `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
 * `type` varchar(190) COLLATE utf8_unicode_ci NOT NULL,
 * `code` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
 * `message` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 * `file` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 * `line` int(10) unsigned NOT NULL,
 * `trace` text COLLATE utf8_unicode_ci NOT NULL,
 * `method` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
 * `path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
 * `query` text COLLATE utf8_unicode_ci NOT NULL,
 * `body` text COLLATE utf8_unicode_ci NOT NULL,
 * `cookies` text COLLATE utf8_unicode_ci NOT NULL,
 * `headers` text COLLATE utf8_unicode_ci NOT NULL,
 * `ip` varchar(255) COLLATE utf8_unicode_ci,
 * `created_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 * `updated_at` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
 * PRIMARY KEY (`id`),
 * KEY `laravel_issues_name_index` (`name`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
class CreateExceptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $table = config('admin.extensions.reporter.table', 'laravel_exceptions');

        Schema::connection($connection)->create($table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->string('code');
            $table->string('message');
            $table->string('file');
            $table->integer('line');
            $table->text('trace');
            $table->string('method');
            $table->string('path');
            $table->text('query');
            $table->text('body');
            $table->text('cookies');
            $table->text('headers');
            $table->string('ip');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $connection = config('admin.database.connection') ?: config('database.default');

        $table = config('admin.extensions.reporter.table', 'laravel_exceptions');

        Schema::connection($connection)->dropIfExists($table);
    }
}
