<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Service;
use App\Models\Tenant\MasterData;
use App\Models\Tenant\User;
use App\Models\Tenant\TenantSetting;
use Illuminate\Http\Request;

class OnboardingController extends Controller
{
    public function index()
    {
        $isNew = Customer::count() === 0 && Service::count() === 0;

        $storeName = TenantSetting::getValue('store_name', '');
        $hasStoreName = !empty($storeName) && $storeName !== 'ServiceKU';

        $steps = [
            ['id' => 'profile', 'title' => 'Pengaturan Profil Toko', 'description' => 'Nama toko, alamat, dan nomor kontak', 'done' => $hasStoreName, 'url' => route('pengaturan.index')],
            ['id' => 'master_data', 'title' => 'Data Master Perangkat', 'description' => 'Kategori, brand & kelengkapan unit', 'done' => MasterData::count() > 0, 'url' => route('pengaturan.index')],
            ['id' => 'customer', 'title' => 'Tambah Pelanggan Pertama', 'description' => 'Daftarkan pelanggan pertama Anda', 'done' => Customer::count() > 0, 'url' => route('customers.index')],
            ['id' => 'service', 'title' => 'Buat Order Servis Pertama', 'description' => 'Catat unit masuk dan keluhan', 'done' => Service::count() > 0, 'url' => route('services.create')],
        ];

        $completedCount = collect($steps)->where('done', true)->count();
        $progressPercent = round(($completedCount / count($steps)) * 100);

        return inertia('Onboarding/Index', [
            'steps' => $steps,
            'completedCount' => $completedCount,
            'totalSteps' => count($steps),
            'progressPercent' => $progressPercent,
            'isNew' => $isNew,
        ]);
    }
}
