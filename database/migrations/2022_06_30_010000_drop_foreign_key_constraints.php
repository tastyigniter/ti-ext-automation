<?php

declare(strict_types=1);

namespace Igniter\Automation\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('igniter_automation_rule_actions', function(Blueprint $table): void {
            $table->dropForeignKeyIfExists(DB::getTablePrefix().'igniter_actions_automation_rule_id_foreign');
            $table->dropIndexIfExists(DB::getTablePrefix().'igniter_actions_automation_rule_id_foreign');
        });

        Schema::table('igniter_automation_rule_conditions', function(Blueprint $table): void {
            $table->dropForeignKeyIfExists(DB::getTablePrefix().'igniter_conditions_automation_rule_id_foreign');
            $table->dropIndexIfExists(DB::getTablePrefix().'igniter_conditions_automation_rule_id_foreign');
        });

        Schema::table('igniter_automation_logs', function(Blueprint $table): void {
            $table->dropForeignKeyIfExists('automation_rule_id');
            $table->dropForeignKeyIfExists('rule_action_id');

            $table->dropIndexIfExists(DB::getTablePrefix().'igniter_automation_logs_automation_rule_id_foreign');
            $table->dropIndexIfExists(DB::getTablePrefix().'igniter_automation_logs_rule_action_id_foreign');
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void {}
};
