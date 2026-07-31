<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\SystemLog;

class SystemLogTest extends TestCase
{
    public function test_fillable_contains_expected_fields()
    {
        $log = new SystemLog();
        foreach (['level', 'type', 'tenant_id', 'message', 'context'] as $field) {
            $this->assertTrue(in_array($field, $log->getFillable()), "fillable harus berisi {$field}");
        }
    }

    public function test_record_creates_log_with_given_fields()
    {
        $log = SystemLog::record('info', 'security', 'Login berhasil', ['ip' => '127.0.0.1'], 'tenant-1');

        $this->assertDatabaseHas('system_logs', [
            'id' => $log->id,
            'level' => 'info',
            'type' => 'security',
            'tenant_id' => 'tenant-1',
            'message' => 'Login berhasil',
        ], 'central');
        $this->assertEquals(['ip' => '127.0.0.1'], $log->context);
    }

    public function test_info_helper_creates_system_log()
    {
        $log = SystemLog::info('Server up', ['cpu' => 20]);

        $this->assertEquals('info', $log->level);
        $this->assertEquals('system', $log->type);
        $this->assertEquals('Server up', $log->message);
        $this->assertEquals(['cpu' => 20], $log->context);
    }

    public function test_error_helper_creates_system_log()
    {
        $log = SystemLog::error('Database timeout');

        $this->assertEquals('error', $log->level);
        $this->assertEquals('system', $log->type);
        $this->assertEquals('Database timeout', $log->message);
    }

    public function test_warning_helper_creates_system_log()
    {
        $log = SystemLog::warning('Disk hampir penuh');

        $this->assertEquals('warning', $log->level);
        $this->assertEquals('system', $log->type);
        $this->assertEquals('Disk hampir penuh', $log->message);
    }

    public function test_record_allows_null_tenant()
    {
        $log = SystemLog::record('info', 'system', 'Tanpa tenant');

        $this->assertNull($log->tenant_id);
    }
}
