<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->unsignedBigInteger('department_head_id')->nullable()->after('name');
            $table->foreign('department_head_id')->references('id')->on('users')->onDelete('set null');
            $table->index('department_head_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->after('user_id');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
                $table->index('department_id');
            }
        });
    }

    public function down()
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['department_head_id']);
            $table->dropColumn('department_head_id');
        });

        Schema::table('audit_logs', function (Blueprint $table) {
            if (Schema::hasColumn('audit_logs', 'department_id')) {
                $table->dropForeign(['department_id']);
                $table->dropColumn('department_id');
            }
        });
    }
};
