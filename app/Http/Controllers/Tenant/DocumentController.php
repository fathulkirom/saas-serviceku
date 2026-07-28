<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\KnowledgeBase;
use App\Models\Tenant\Sop;
use App\Models\Tenant\QuickReply;
use App\Models\Tenant\User;
use App\Models\Tenant\MasterData;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DocumentController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->get('tab', 'kb');

        return Inertia::render('Dokumen/Index', [
            'activeTab' => $tab,

            'articles' => fn() => KnowledgeBase::with('creator')
                ->where('branch_id', auth()->user()->branch_id)
                ->latest()
                ->paginate(20),

            'kbDeviceTypes' => fn() => MasterData::getByCategory('device_category'),

            'kbBrands' => fn() => MasterData::getByCategory('brand'),

            'sops' => fn() => Sop::with('creator')
                ->where('branch_id', auth()->user()->branch_id)
                ->latest()
                ->paginate(20),

            'sopRoles' => fn() => User::getAvailableRoles(),

            'quickReplies' => fn() => QuickReply::with('user')
                ->where('branch_id', auth()->user()->branch_id)
                ->latest()
                ->paginate(20),
        ]);
    }
}
