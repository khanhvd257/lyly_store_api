<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRepliesRatingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rating_replies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rating_id');
            $table->string('username');
            $table->text('reply');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            // Tạo các foreign key constraints
            $table->foreign('rating_id')->references('id')->on('ratings')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('rating_replies');
    }
}
