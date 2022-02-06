<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CopyMessageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('copy_message', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->increments('id_com');
            $table->string('first_com');
            $table->string('second_com');
            $table->boolean('isChoosen');
            $table->boolean('isChoosenSmile');
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
        Schema::dropIfExists('copy_message');
    }
}
