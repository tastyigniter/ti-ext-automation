<?php

namespace Igniter\Automation\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeyConstraintsToTables extends Migration
{
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('igniter_automation_rule_actions', function (Blueprint $table) {
            $table->foreignId('automation_rule_id')->nullable()->change();
            $table->foreign('automation_rule_id', 'igniter_actions_automation_rule_id_foreign')
                ->references('id')
                ->on('igniter_automation_rules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('igniter_automation_rule_conditions', function (Blueprint $table) {
            $table->foreignId('automation_rule_id')->nullable()->change();
            $table->foreign('automation_rule_id', 'igniter_conditions_automation_rule_id_foreign')
                ->references('id')
                ->on('igniter_automation_rules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::table('igniter_automation_logs', function (Blueprint $table) {
            $table->foreignId('automation_rule_id')->nullable()->change();
            $table->foreign('automation_rule_id')
                ->references('id')
                ->on('igniter_automation_rules')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('rule_action_id')->nullable()->change();
            $table->foreign('rule_action_id')
                ->references('id')
                ->on('igniter_automation_rule_actions')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::table('igniter_automation_rule_actions', function (Blueprint $table) {
            $table->dropForeign(['automation_rule_id']);
        });

        Schema::table('igniter_automation_rule_conditions', function (Blueprint $table) {
            $table->dropForeign(['automation_rule_id']);
        });
    }
}
