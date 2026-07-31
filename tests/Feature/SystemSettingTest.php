<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\SystemSetting;

class SystemSettingTest extends TestCase
{
    public function test_get_value_returns_default_when_not_found()
    {
        $this->assertNull(SystemSetting::getValue('non_existent_key'));
        $this->assertEquals('fallback', SystemSetting::getValue('non_existent_key', 'fallback'));
    }

    public function test_set_and_get_value_roundtrip()
    {
        SystemSetting::setValue('test_key', 'test_value', 'testing');
        $this->assertEquals('test_value', SystemSetting::getValue('test_key'));
        $this->assertEquals('test_value', SystemSetting::getValue('test_key', 'default_ignored'));
    }

    public function test_set_value_updates_existing_key()
    {
        SystemSetting::setValue('test_key', 'first', 'testing');
        SystemSetting::setValue('test_key', 'second', 'testing');
        $this->assertEquals('second', SystemSetting::getValue('test_key'));
        $this->assertEquals(1, SystemSetting::where('key', 'test_key')->count());
    }

    public function test_get_group_returns_plucked_values()
    {
        SystemSetting::setValue('a', '1', 'group_a');
        SystemSetting::setValue('b', '2', 'group_a');
        SystemSetting::setValue('c', '3', 'group_b');

        $groupA = SystemSetting::getGroup('group_a');
        $this->assertEquals(['a' => '1', 'b' => '2'], $groupA->toArray());
    }
}
