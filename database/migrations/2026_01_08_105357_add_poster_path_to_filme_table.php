<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('filme', function (Blueprint $table) {
        $table->string('poster_path', 255)->after('regizor');
    });
}

public function down()
{
    Schema::table('filme', function (Blueprint $table) {
        $table->dropColumn('poster_path');
    });
}

};
