<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tokens', function (Blueprint $table) {
            $table->uuid('shop_id')->primary()->nullable(false);
            $table->text('access_token')->nullable(false);
            $table->text('refresh_token')->nullable(false);
            $table->text('token_type')->nullable(false);
            $table->integer('expires_in')->nullable(false);
            $table->timestamp('expires_at')->nullable(false);
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
        Schema::dropIfExists('tokens');
    }
}
