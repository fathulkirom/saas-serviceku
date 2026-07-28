<?php
namespace Tests\Unit\Models;
use Tests\TestCase;
use App\Models\Tenant\Service;

class ServiceTest extends TestCase
{
    public function test_status_constants_are_defined()
    {
        $this->assertEquals('menunggu_alokasi', Service::STATUS_MENUNGGU_ALOKASI);
        $this->assertEquals('diterima', Service::STATUS_DITERIMA);
        $this->assertEquals('dikerjakan', Service::STATUS_DIKERJAKAN);
        $this->assertEquals('siap_diambil', Service::STATUS_SIAP_DIAMBIL);
        $this->assertEquals('selesai', Service::STATUS_SELESAI);
        $this->assertEquals('cancel', Service::STATUS_CANCEL);
    }

    public function test_get_status_label_returns_correct_indonesian_labels()
    {
        $service = new Service();
        $labels = [
            'menunggu_alokasi' => 'Menunggu Alokasi',
            'diterima' => 'Diterima Teknisi',
            'dikerjakan' => 'Dikerjakan',
            'siap_diambil' => 'Siap Diambil',
            'selesai' => 'Selesai',
            'cancel' => 'Dibatalkan',
        ];

        foreach ($labels as $status => $expected) {
            $service->status = $status;
            $this->assertEquals($expected, $service->getStatusLabel(), "Status $status failed");
        }
    }

    public function test_get_status_color_returns_valid_color_name()
    {
        $service = new Service();
        $colors = ['yellow', 'orange', 'blue', 'green', 'red', 'purple', 'pink', 'gray'];
        $statuses = ['menunggu_alokasi', 'diterima', 'dikerjakan', 'siap_diambil', 'selesai', 'cancel', 'indent', 'close'];

        foreach ($statuses as $status) {
            $service->status = $status;
            $this->assertContains($service->getStatusColor(), $colors, "Color for $status invalid");
        }
    }

    public function test_is_active_returns_true_for_active_statuses()
    {
        $service = new Service();
        $activeStatuses = ['menunggu_alokasi', 'diterima', 'dikerjakan', 'menunggu_konfirmasi_pelanggan', 'indent', 'onpartner'];

        foreach ($activeStatuses as $status) {
            $service->status = $status;
            $this->assertTrue($service->isActive(), "Status $status should be active");
        }
    }

    public function test_is_active_returns_false_for_completed_statuses()
    {
        $service = new Service();
        $inactiveStatuses = ['selesai', 'cancel', 'void', 'close'];

        foreach ($inactiveStatuses as $status) {
            $service->status = $status;
            $this->assertFalse($service->isActive(), "Status $status should be inactive");
        }
    }

    public function test_fillable_contains_expected_fields()
    {
        $service = new Service();
        $fillable = $service->getFillable();
        $this->assertContains('customer_id', $fillable);
        $this->assertContains('problem_description', $fillable);
        $this->assertContains('tracking_code', $fillable);
        $this->assertContains('status', $fillable);
        $this->assertContains('technician_id', $fillable);
        $this->assertContains('imei_sn', $fillable);
        $this->assertContains('kelengkapan', $fillable);
    }

    public function test_uses_soft_deletes()
    {
        $service = new Service();
        $this->assertContains('Illuminate\Database\Eloquent\SoftDeletes', class_uses($service));
    }
}
