<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChatMessages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('int_chat_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('sender');
            $table->text('message');
            $table->integer('type')->nullable();            
            $table->string('additional_detail')->unique();
            $table->string('receiver')->nullable();
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        Schema::dropIfExists('chat_messages');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
