<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\EventLog;
use Illuminate\Http\Request;

/**
 * Thin read-only controller for the canonical Event Log viewer.
 * No business logic — EventLog is append-only, immutable.
 */
class EventLogController extends Controller
{
    public function index(Request $request)
    {
        $query = EventLog::query()->with('actor');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('event_key', 'like', "%{$search}%")
                  ->orWhere('entity_type', 'like', "%{$search}%")
                  ->orWhere('correlation_id', 'like', "%{$search}%");
            });
        }
        if ($eventKey = $request->query('event_key')) {
            $query->where('event_key', $eventKey);
        }
        if ($entityType = $request->query('entity_type')) {
            $query->where('entity_type', $entityType);
        }
        if ($severity = $request->query('severity')) {
            $query->where('severity', $severity);
        }

        $events = $query->orderBy('occurred_at', 'desc')->paginate(50)->withQueryString();

        return inertia('Monitoring/EventLog', [
            'events' => $events,
            'filters' => $request->only(['search', 'event_key', 'entity_type', 'severity']),
            'stats' => [
                'total' => EventLog::count(),
                'today' => EventLog::whereDate('occurred_at', today())->count(),
            ],
        ]);
    }
}
