<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SystemLog;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::all()->map(fn($plan) => [
            'id' => $plan->id,
            'name' => $plan->name,
            'slug' => $plan->slug,
            'description' => $plan->description,
            'price' => $plan->price,
            'promo_price' => $plan->promo_price,
            'promo_start' => $plan->promo_start?->format('Y-m-d'),
            'promo_end' => $plan->promo_end?->format('Y-m-d'),
            'is_promo_active' => $plan->isPromoActive(),
            'discount_percent' => $plan->discountPercent(),
            'effective_price' => $plan->effectivePrice(),
            'trial_days' => $plan->trial_days,
            'features' => $plan->features,
            'business_types' => $plan->business_types,
            'is_active' => $plan->is_active,
        ]);
        return inertia("Admin/Plans", ["plans" => $plans]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "slug" => "required|string|max:255|unique:plans,slug",
            "description" => "nullable|string",
            "price" => "required|numeric|min:0",
            "promo_price" => "nullable|numeric|min:0",
            "promo_start" => "nullable|date",
            "promo_end" => "nullable|date|after_or_equal:promo_start",
            "trial_days" => "nullable|integer|min:0",
            "features" => "nullable|array",
            "business_types" => "nullable|array",
            "is_active" => "nullable|boolean",
        ]);
        Plan::create($validated);
        SystemLog::info("Plan created: " . $validated['name']);
        return back()->with("success", "Plan berhasil ditambahkan.");
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            "name" => "required|string|max:255",
            "slug" => "required|string|max:255|unique:plans,slug," . $plan->id,
            "description" => "nullable|string",
            "price" => "required|numeric|min:0",
            "promo_price" => "nullable|numeric|min:0",
            "promo_start" => "nullable|date",
            "promo_end" => "nullable|date|after_or_equal:promo_start",
            "trial_days" => "nullable|integer|min:0",
            "features" => "nullable|array",
            "business_types" => "nullable|array",
            "is_active" => "nullable|boolean",
        ]);
        $plan->update($validated);
        SystemLog::info("Plan updated: " . $plan->name);
        return back()->with("success", "Plan berhasil diperbarui.");
    }

    public function updateDefaultMenus(Request $request, Plan $plan)
    {
        $validated = $request->validate(['default_menus' => 'required|array', 'default_menus.*' => 'array']);
        $plan->setDefaultMenus($validated['default_menus']);
        SystemLog::info("Default menus updated for plan: " . $plan->name);
        return back()->with('success', 'Default menu berhasil diperbarui.');
    }
}
