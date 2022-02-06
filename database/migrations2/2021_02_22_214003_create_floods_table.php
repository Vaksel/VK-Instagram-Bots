<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFloodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('floods', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('owner_id')->default('');
            $table->string('post_comments_position');
            $table->string('post_comment_position');
            $table->string('flood_type');
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
        Schema::dropIfExists('floods');
    }
}
