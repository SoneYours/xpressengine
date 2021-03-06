<?php
namespace Xpressengine\Migrations;

use Illuminate\Database\Schema\Blueprint;
use Schema;
use Xpressengine\Support\Migration;

class StorageMigration implements Migration {

    public function install()
    {

        Schema::create('files', function (Blueprint $table) {
            $table->engine = "InnoDB";

            $table->string('id', 36);
            $table->string('originId', 36)->nullable();
            $table->string('userId', 36)->nullable();
            $table->string('disk', 20);
            $table->string('path');
            $table->string('filename', 100);
            $table->string('clientname', 100);
            $table->string('mime', 50);
            $table->integer('size');
            $table->integer('useCount')->default(0);
            $table->integer('downloadCount')->default(0);
            $table->timestamp('createdAt');
            $table->timestamp('updatedAt');

            $table->primary('id');
            $table->unique(['disk', 'path', 'filename'], 'findKey');
            $table->index('originId');
        });

        Schema::create('fileables', function (Blueprint $table) {
            $table->increments('id');
            $table->string('fileId', 36);
            $table->string('fileableId', 36);
            $table->timestamp('createdAt');

            $table->unique(['fileId', 'fileableId']);
        });
    }

    public function update($currentVersion)
    {

    }

    public function checkInstall()
    {
    }

    public function checkUpdate($currentVersion)
    {
    }
}
