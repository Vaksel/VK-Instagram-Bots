<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('uid');
            $table->string('firstMessage');
            $table->string('replyMessage');
            $table->unsignedBigInteger('status_id');
            $table->timestamps();

            $table->foreign('uid')->references('id')->on('users')->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreign('status_id')->references('id')->on('message_statuses')->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
