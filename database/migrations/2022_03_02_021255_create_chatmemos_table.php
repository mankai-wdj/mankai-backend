<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChatmemosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chatmemos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id');
            // 채팅 id
            $table->foreignId('chatwriter_id');
            // 채팅 쓴 사람
            $table->foreignId("user_id");
            // 채팅 저장한 사람
            $table->string('chatmemo');
            // 채팅 내용
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
        Schema::dropIfExists('chatmemos');
    }
}
