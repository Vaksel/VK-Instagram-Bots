<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesDebugTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages_debug', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('msgId');
            $table->string('text');
            $table->string('status');
            $table->timestamps();

            $table->foreign('msgId')->references('id')->on('messages');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages_debug');
    }
}
