<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostmemosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postmemos', function (Blueprint $table) {
            $table->id();
            // 차후에 post id값 가져와서 수정하기
            $table->foreignId("post_id");
            $table->foreignId('user_id');
            $table->string('post_content');
            $table->string('postmemo');
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
        Schema::dropIfExists('postmemos');
    }
}
