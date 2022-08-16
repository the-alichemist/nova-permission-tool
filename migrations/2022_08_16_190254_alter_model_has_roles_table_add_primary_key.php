<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropPrimary('model_has_roles_role_model_type_primary');
        });
        Schema::enableForeignKeyConstraints();
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->id();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropPrimary();
            $table->unsignedInteger('id')->change();
            $table->dropColumn('id');
        });
        Schema::enableForeignKeyConstraints();
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->primary(['role_id', 'model_id', 'model_type'],
                    'model_has_roles_role_model_type_primary');
            
        });
    }
};
