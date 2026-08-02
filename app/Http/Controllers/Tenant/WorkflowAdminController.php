<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Services\WorkflowEngine;
use App\Models\Tenant\Workflow;
use Illuminate\Http\Request;

/**
 * Thin controller — delegates to WorkflowEngine.
 * Provides data for the Workflow Builder UI.
 */
class WorkflowAdminController extends Controller
{
    public function index(WorkflowEngine $engine)
    {
        $workflows = Workflow::where('is_active', true)->get()
            ->map(fn($w) => ['key' => $w->key, 'label' => $w->label, 'is_active' => $w->is_active, 'states_count' => $w->states()->count(), 'transitions_count' => $w->transitions()->count()]);

        return inertia('Sistem/Workflows', [
            'workflows' => $workflows,
            'graph' => null, // loaded on demand via graph()
        ]);
    }

    public function graph(Request $request, WorkflowEngine $engine)
    {
        $key = $request->query('workflow', 'service');
        $graph = $engine->getStateGraph($key);

        return back()->with(['graph' => $graph]);
    }
}
