<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCnaesTable extends Migration{

    public function up(){
        Schema::create(
            'cnaes',
            function(Blueprint $table){
                $table->id();

                $table->bigInteger('parent_id')
                      ->nullable(true)
                      ->unsigned()
                      ->comment('CNAE parent');

                $table->string('identification',255)
                      ->nullable(false)
                      ->unique()
                      ->comment('CNAE identification');

                $table->string('name',255)
                      ->nullable(false)
                      ->comment('CNAE name');
            }
        );
    }

    public function down(){
        Schema::dropIfExists('cnaes');
    }
}
