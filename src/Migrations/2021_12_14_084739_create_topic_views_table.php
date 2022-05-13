<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopicViewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(config('laravel-visit.table'), function (Blueprint $table) {
            $table->string('uuid', 16)->default('')->comment('实体id');
            $table->string('type', 16)->default('')->comment('实体类型');
            $table->unsignedInteger('view_count', false)->default(0)->comment('浏览量');
            $table->timestamps();
            $table->primary('uuid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(config('laravel-visit.table'));
    }
}
