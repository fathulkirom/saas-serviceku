<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\AutomationRule;
use Illuminate\Http\Request;

/**
 * Thin CRUD controller for Automation Rules.
 * All logic is in AutomationEngine — this just manages the rules table.
 */
class AutomationAdminController extends Controller
{
    public function index()
    {
        $rules = AutomationRule::orderBy('priority', 'desc')->get();
        $workflows = \App\Models\Tenant\Workflow::where('is_active', true)->get(['key', 'label']);

        return inertia('Sistem/Automations', [
            'rules' => $rules,
            'workflows' => $workflows,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'event' => 'nullable|string',
            'workflow_key' => 'nullable|string',
            'action_type' => 'required|string',
            'action_config' => 'nullable|array',
            'conditions' => 'nullable|array',
            'delay_minutes' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        AutomationRule::create($data);
        return back()->with('success', 'Automation rule created.');
    }

    public function update(Request $request, AutomationRule $rule)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'event' => 'nullable|string',
            'workflow_key' => 'nullable|string',
            'action_type' => 'required|string',
            'action_config' => 'nullable|array',
            'conditions' => 'nullable|array',
            'delay_minutes' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $rule->update($data);
        return back()->with('success', 'Automation rule updated.');
    }

    public function toggle(AutomationRule $rule)
    {
        $rule->update(['is_active' => !$rule->is_active]);
        return back()->with('success', 'Rule ' . ($rule->is_active ? 'enabled' : 'disabled') . '.');
    }
}
