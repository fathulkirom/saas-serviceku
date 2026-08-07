<?php

namespace App\Enterprise\Automation\Definitions;

use App\Enterprise\Automation\ActionType;
use App\Enterprise\Automation\AutomationDefinition;
use App\Enterprise\Automation\AutomationStep;
use App\Enterprise\Automation\ConditionClause;
use App\Enterprise\Automation\ConditionOperator;
use App\Enterprise\Automation\TriggerType;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceDiagnosis;
use App\Models\Tenant\ServiceQuotation;

/**
 * Reference Automations for ServiceKU.
 */
class ServiceAutomations
{
    /**
     * When new service is created:
     * → Add timeline entry
     * → Create activity log
     * → Send notification to CS/Manager
     */
    public static function serviceCreated(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.created',
            name: 'Servis Baru — Notifikasi CS',
            description: 'Saat servis baru dibuat, tambah timeline, kirim notifikasi ke CS & Manager.',
            trigger: TriggerType::RECORD_CREATED,
            module: 'service',
            modelClass: Service::class,
        ))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '📥 Servis baru #{{subject.tracking_code}} dibuat.',
            ]))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => 'Servis baru #{{subject.tracking_code}} — {{subject.customer.name}} — {{subject.problem_description}}',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, [
                'message' => '📥 Servis baru: #{{subject.tracking_code}} — {{subject.customer.name}} — {{subject.problem_description}}',
                'roles' => ['cs', 'manager', 'admin'],
            ]));
    }

    /**
     * When service status changes to "Selesai":
     * → Add timeline entry
     * → Send WhatsApp to customer
     * → Create push notification
     */
    public static function serviceCompleted(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.completed',
            name: 'Servis Selesai — Notifikasi',
            description: 'Saat servis selesai, tambah timeline, kirim WhatsApp & notifikasi.',
            trigger: TriggerType::STATUS_CHANGED,
            module: 'service',
            modelClass: Service::class,
        ))
            ->addCondition(new ConditionClause(ConditionOperator::EQUALS, 'status', 'selesai'))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '✅ Servis selesai dikerjakan (otomatis).',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, [
                'to' => '{{subject.customer.phone}}',
                'message' => 'Halo {{subject.customer.name}}, servis Anda dengan kode #{{subject.tracking_code}} telah selesai. Silakan datang untuk mengambil unit Anda.',
            ], delaySeconds: 0))
            ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                'title' => 'Servis Selesai',
                'body' => 'Servis #{{subject.tracking_code}} telah selesai.',
                'user_id' => '{{subject.created_by}}',
            ]));
    }

    /**
     * When stock drops below minimum:
     * → Create notification
     * → Create activity log
     */
    public static function stockLow(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'inventory.stock_low',
            name: 'Stok Menipis — Peringatan',
            description: 'Saat stok di bawah minimum, kirim notifikasi ke manager.',
            trigger: TriggerType::STOCK_LOW,
            module: 'inventory',
        ))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => '⚠ Stok {{subject.name}} menipis (tersisa {{subject.stock_quantity}}).',
            ]))
            ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                'title' => 'Stok Menipis',
                'body' => '{{subject.name}} tersisa {{subject.stock_quantity}}. Segera restock.',
            ]));
    }

    /**
     * When new customer is created:
     * → Create follow-up task
     * → Create activity
     */
    public static function customerWelcome(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'crm.customer_welcome',
            name: 'Pelanggan Baru — Follow Up',
            description: 'Saat pelanggan baru terdaftar, buat task follow-up.',
            trigger: TriggerType::CUSTOMER_CREATED,
            module: 'crm',
        ))
            ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                'title' => 'Follow-up: Pelanggan Baru {{subject.name}}',
                'assignee_id' => null,
            ], delaySeconds: 3600)) // 1 jam setelah registrasi
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => '🎉 Pelanggan baru terdaftar: {{subject.name}} ({{subject.phone}}).',
            ]));
    }

    /**
     * Sprint v2.0: Diagnosis Completed → timeline + activity + internal notification.
     */
    public static function diagnosisCompleted(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.diagnosis_completed',
            name: 'Diagnosis Selesai — Notifikasi',
            description: 'Saat teknisi menyelesaikan diagnosis, tambah timeline, aktivitas, dan notifikasi.',
            trigger: TriggerType::CUSTOM_EVENT,
            module: 'service',
            modelClass: ServiceDiagnosis::class,
        ))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '🔍 Diagnosis selesai: {{subject.findings}}',
            ]))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => 'Diagnosis selesai untuk servis — {{subject.findings}} — Estimasi: {{subject.estimated_minutes}} menit, Rp{{subject.estimated_cost}}',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, [
                'message' => '🔍 Diagnosis selesai untuk servis. Estimasi: {{subject.estimated_minutes}} menit.',
                'roles' => ['cs', 'manager'],
            ]));
    }

    /**
     * Sprint v2.0: Quotation Created → timeline + activity + customer notification.
     */
    public static function quotationCreated(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.quotation_created',
            name: 'Estimasi Dibuat — Notifikasi Customer',
            description: 'Saat estimasi biaya dibuat, tambah timeline, aktivitas, dan notifikasi ke customer.',
            trigger: TriggerType::CUSTOM_EVENT,
            module: 'service',
            modelClass: ServiceQuotation::class,
        ))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '💰 Estimasi biaya dibuat: Rp{{subject.total_cost}}',
            ]))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => 'Estimasi biaya Rp{{subject.total_cost}} menunggu persetujuan pelanggan.',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, [
                'message' => '💰 Estimasi biaya Rp{{subject.total_cost}} siap untuk persetujuan pelanggan.',
                'roles' => ['cs', 'manager'],
            ]));
    }

    /**
     * Sprint v2.0: Customer Approved Repair → timeline + activity + notify technician.
     */
    public static function customerApproved(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.approval_completed',
            name: 'Estimasi Disetujui — Notifikasi Teknisi',
            description: 'Saat pelanggan menyetujui estimasi, tambah timeline dan notifikasi ke teknisi.',
            trigger: TriggerType::CUSTOM_EVENT,
            module: 'service',
            modelClass: ServiceQuotation::class,
        ))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '✅ Estimasi disetujui. Servis siap dikerjakan.',
            ]))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => 'Estimasi disetujui — servis siap dikerjakan oleh teknisi.',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, [
                'message' => '✅ Estimasi disetujui! Servis siap dikerjakan.',
                'roles' => ['technician', 'cs'],
            ]));
    }

    /**
     * Sprint v2.0: Quotation Rejected → timeline + activity + notify CS/technician.
     */
    public static function quotationRejected(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.quotation_rejected',
            name: 'Estimasi Ditolak — Notifikasi',
            description: 'Saat pelanggan menolak estimasi, tambah timeline dan notifikasi ke CS/teknisi.',
            trigger: TriggerType::CUSTOM_EVENT,
            module: 'service',
            modelClass: ServiceQuotation::class,
        ))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '❌ Estimasi ditolak oleh pelanggan.',
            ]))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => 'Estimasi ditolak — perlu review lebih lanjut.',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, [
                'message' => '❌ Estimasi ditolak oleh pelanggan. Perlu tindak lanjut.',
                'roles' => ['cs', 'technician'],
            ]));
    }

    /**
     * Sprint v3.0: Repair Started → timeline + activity + notify internal.
     */
    public static function repairStarted(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.repair_started',
            name: 'Perbaikan Dimulai — Notifikasi',
            description: 'Saat teknisi memulai perbaikan, tambah timeline, aktivitas, dan notifikasi.',
            trigger: TriggerType::CUSTOM_EVENT,
            module: 'service',
            modelClass: Service::class,
        ))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '🔧 Perbaikan dimulai oleh teknisi.',
            ]))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => 'Perbaikan dimulai — teknisi mulai mengerjakan servis.',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, [
                'message' => '🔧 Perbaikan servis #{{subject.tracking_code}} dimulai.',
                'roles' => ['cs', 'manager'],
            ]));
    }

    /**
     * Sprint v3.0: Repair Completed → timeline + activity + notify QC.
     */
    public static function repairCompleted(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.repair_completed',
            name: 'Perbaikan Selesai — Handoff QC',
            description: 'Saat teknisi menyelesaikan perbaikan, tambah timeline dan notifikasi ke QC/manager.',
            trigger: TriggerType::CUSTOM_EVENT,
            module: 'service',
            modelClass: Service::class,
        ))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '✅ Perbaikan selesai — menunggu QC.',
            ]))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => 'Perbaikan selesai. Servis siap untuk Quality Control.',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, [
                'message' => '✅ Perbaikan selesai untuk servis #{{subject.tracking_code}}. QC diperlukan.',
                'roles' => ['manager', 'admin'],
            ]));
    }

    /**
     * Sprint v3.0: QC Passed → timeline + activity + customer ready notification.
     */
    public static function qcPassed(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.qc_passed',
            name: 'QC Lulus — Servis Siap Diambil',
            description: 'Saat QC lulus, tambah timeline dan notifikasi siap diambil.',
            trigger: TriggerType::CUSTOM_EVENT,
            module: 'service',
            modelClass: Service::class,
        ))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '🏁 QC LULUS — servis siap diambil pelanggan.',
            ]))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => 'QC PASS. Servis siap diambil.',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, [
                'message' => '🏁 QC LULUS untuk servis #{{subject.tracking_code}}. Siap diambil pelanggan.',
                'roles' => ['cs', 'manager'],
            ]));
    }

    /**
     * Sprint v3.0: QC Failed → timeline + activity + notify technician for rework.
     */
    public static function qcFailed(): AutomationDefinition
    {
        return (new AutomationDefinition(
            id: 'service.qc_failed',
            name: 'QC Gagal — Kembali ke Perbaikan',
            description: 'Saat QC gagal, tambah timeline dan notifikasi ke teknisi untuk rework.',
            trigger: TriggerType::CUSTOM_EVENT,
            module: 'service',
            modelClass: Service::class,
        ))
            ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                'message' => '⚠ QC GAGAL — dikembalikan ke perbaikan.',
            ]))
            ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                'message' => 'QC FAIL. Servis dikembalikan ke teknisi untuk perbaikan ulang.',
            ]))
            ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, [
                'message' => '⚠ QC GAGAL untuk servis #{{subject.tracking_code}}. Perlu perbaikan ulang.',
                'roles' => ['technician', 'cs'],
            ]));
    }

    /** @return AutomationDefinition[] */
    public static function all(): array
    {
        return [
            self::serviceCreated(),
            self::serviceCompleted(),
            self::stockLow(),
            self::customerWelcome(),
            self::diagnosisCompleted(),
            self::quotationCreated(),
            self::customerApproved(),
            self::quotationRejected(),
            self::repairStarted(),
            self::repairCompleted(),
            self::qcPassed(),
            self::qcFailed(),
        ];
    }
}
