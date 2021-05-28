<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('user_name', 100);
            $table->string('first_name', 50);
            $table->string('last_name', 50);
            $table->enum('gender', ['male', 'female', 'other'])->nullable();
            $table->date('dob')->nullable();
            $table->integer('age')->nullable();            
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('password');            
            $table->string('facebook_id')->nullable();
            $table->string('instagram_id')->nullable();
            $table->string('profession', 50)->nullable();
            $table->enum('relationship_status', ['single', 'in_relationship', 'its_complicated', 'not_looking'])->nullable();
            $table->enum('visibility', ['visible', 'hidden'])->default('visible');
            $table->enum('religion', ['christian', 'catholic', 'buddest', 'spiritual', 'muslim', 'sikh', 'hindu', 'agnostic', 'atheist', 'other'])->nullable();
            $table->string('family', 20)->nullable();
            $table->string('body_shape', 20)->nullable();
            $table->float('height', 8,2)->nullable();
            $table->string('auth_token', 100)->unique()->nullable()->default(null);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            $table->index('gender');
            $table->index('age');
            $table->index('profession');
            $table->index('relationship_status');
            $table->index('religion');
            $table->index('visibility');

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
        Schema::dropIfExists('users');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
}
