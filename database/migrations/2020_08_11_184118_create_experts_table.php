<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpertsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('experts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('expert');
            $table->string('country');
            $table->string('timezone_name');
            $table->integer('timezone_offset');
            $table->time('work_from');
            $table->time('work_to');
            $table->time('work_from_gmt');  /*Not used Just for testing purposes*/
            $table->time('work_to_gmt');    /*Not used Just for testing purposes*/
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
        Schema::dropIfExists('experts');
    }
}
