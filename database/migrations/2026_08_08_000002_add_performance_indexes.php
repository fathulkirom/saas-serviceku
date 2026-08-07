<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // tenants indexes — critical for every request and registration
        Schema::table('tenants', function (Blueprint $table) {
            if (!$this->hasIndex('tenants', 'tenants_slug_index')) {
                $table->index('slug', 'tenants_slug_index');
            }
            if (!$this->hasIndex('tenants', 'tenants_email_index')) {
                $table->index('email', 'tenants_email_index');
            }
            if (!$this->hasIndex('tenants', 'tenants_subscription_status_index')) {
                $table->index('subscription_status', 'tenants_subscription_status_index');
            }
            if (!$this->hasIndex('tenants', 'tenants_created_at_index')) {
                $table->index('created_at', 'tenants_created_at_index');
            }
        });

        // domains index — every tenant request hits this
        Schema::table('domains', function (Blueprint $table) {
            if (!$this->hasIndex('domains', 'domains_domain_index')) {
                $table->index('domain', 'domains_domain_index');
            }
        });

        // system_logs — monitoring filters
        Schema::table('system_logs', function (Blueprint $table) {
            if (!$this->hasIndex('system_logs', 'system_logs_created_at_index')) {
                $table->index('created_at', 'system_logs_created_at_index');
            }
            if (!$this->hasIndex('system_logs', 'system_logs_level_index')) {
                $table->index('level', 'system_logs_level_index');
            }
        });

        // system_settings — every settings page hit
        Schema::table('system_settings', function (Blueprint $table) {
            if (!$this->hasIndex('system_settings', 'system_settings_key_index')) {
                $table->index('key', 'system_settings_key_index');
            }
            if (!$this->hasIndex('system_settings', 'system_settings_group_index')) {
                $table->index('group', 'system_settings_group_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndexIfExists('tenants_slug_index');
            $table->dropIndexIfExists('tenants_email_index');
            $table->dropIndexIfExists('tenants_subscription_status_index');
            $table->dropIndexIfExists('tenants_created_at_index');
        });
        Schema::table('domains', function (Blueprint $table) {
            $table->dropIndexIfExists('domains_domain_index');
        });
        Schema::table('system_logs', function (Blueprint $table) {
            $table->dropIndexIfExists('system_logs_created_at_index');
            $table->dropIndexIfExists('system_logs_level_index');
        });
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropIndexIfExists('system_settings_key_index');
            $table->dropIndexIfExists('system_settings_group_index');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        try {
            $indexes = Schema::getIndexes($table);
            foreach ($indexes as $idx) {
                if ($idx['name'] === $index) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            // Schema::getIndexes not available on all drivers (e.g. SQLite)
            return false;
        }
        return false;
    }
};
