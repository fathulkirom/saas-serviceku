<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ApiToken;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    public function index()
    {
        $tokens = ApiToken::where('branch_id', auth()->user()->branch_id)
            ->with('user')->latest()->get();

        return inertia('Pengaturan/ApiTokens', ['tokens' => $tokens]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'scopes'  => 'nullable|array',
            'scopes.*'=> 'string',
        ]);

        $rawToken = ApiToken::generateToken();
        $token = ApiToken::create([
            'user_id'   => auth()->id(),
            'branch_id' => auth()->user()->branch_id,
            'name'      => $validated['name'],
            'token'     => hash('sha256', $rawToken),
            'scopes'    => $validated['scopes'] ?? ['services.read', 'sales.read'],
            'is_active' => true,
        ]);

        return back()->with('success', "Token API dibuat. Simpan token ini (hanya tampil sekali): <code>{$rawToken}</code>");
    }

    public function destroy(ApiToken $token)
    {
        $token->update(['is_active' => false]);
        return back()->with('success', 'Token dinonaktifkan.');
    }
}
