<?php

namespace App\Workspace\Definitions;

use App\Workspace\WorkspaceDefinition;

/**
 * ServiceWorkspace — First Workspace Engine implementation.
 * 
 * Registers tabs, actions, sidebar, shortcuts for Service module.
 * Other modules (Inventory, POS, Finance) follow the same pattern.
 */
class ServiceWorkspace extends WorkspaceDefinition
{
    public function __construct()
    {
        parent::__construct(
            id: 'service',
            title: 'Service Workspace',
            icon: '🔧',

            // ── TABS ──
            tabs: [
                ['id' => 'overview',  'label' => 'Overview',   'icon' => '📋'],
                ['id' => 'timeline',  'label' => 'Timeline',   'icon' => '🕐'],
                ['id' => 'spareparts','label' => 'Sparepart',   'icon' => '🔩'],
                ['id' => 'photos',    'label' => 'Foto',       'icon' => '📸'],
                ['id' => 'invoice',   'label' => 'Invoice',    'icon' => '💰'],
                ['id' => 'diagnosis', 'label' => 'Diagnosa',   'icon' => '🔍', 'roles' => ['owner', 'admin', 'technician']],
                ['id' => 'qc',        'label' => 'QC',         'icon' => '✅', 'roles' => ['owner', 'admin', 'manager']],
                ['id' => 'warranty',  'label' => 'Garansi',    'icon' => '🛡️', 'roles' => ['owner', 'admin', 'manager']],
            ],

            // ── ACTIONS ──
            actions: [
                ['id' => 'assign',   'label' => 'Assign Teknisi', 'roles' => ['owner', 'admin', 'cs'],        'shortcut' => 'a'],
                ['id' => 'diagnose', 'label' => 'Diagnosa',       'roles' => ['owner', 'admin', 'technician'], 'shortcut' => 'd'],
                ['id' => 'start',    'label' => 'Mulai Servis',   'roles' => ['owner', 'admin', 'technician'], 'shortcut' => 's'],
                ['id' => 'complete', 'label' => 'Selesai',        'roles' => ['owner', 'admin', 'technician'], 'shortcut' => 'x'],
                ['id' => 'indent',   'label' => 'Indent Part',    'roles' => ['owner', 'admin', 'cs'],        'shortcut' => 'i'],
                ['id' => 'ready',    'label' => 'Siap Diambil',   'roles' => ['owner', 'admin', 'manager', 'cashier'], 'shortcut' => 'r'],
                ['id' => 'create_invoice', 'label' => 'Buat Invoice', 'roles' => ['owner', 'admin', 'cashier'], 'shortcut' => 'v'],
                ['id' => 'pay',      'label' => 'Bayar',          'roles' => ['owner', 'admin', 'cashier'], 'shortcut' => 'p'],
                ['id' => 'pickup',   'label' => 'Serahkan (Pickup)', 'roles' => ['owner', 'admin', 'cs', 'cashier'], 'shortcut' => 'u'],
                ['id' => 'close',    'label' => 'Tutup Servis',   'roles' => ['owner', 'admin', 'manager'],   'shortcut' => 'q'],
                ['id' => 'cancel',   'label' => 'Batalkan',       'roles' => ['owner', 'admin', 'manager'],   'shortcut' => 'c', 'danger' => true],
                ['id' => 'reopen',   'label' => 'Minta Reopen',   'roles' => ['owner', 'admin', 'manager'],   'shortcut' => null, 'danger' => true],
                ['id' => 'print',    'label' => 'Cetak',          'roles' => ['owner', 'admin', 'manager', 'cs'], 'shortcut' => 'p'],
            ],

            // ── SIDEBAR WIDGETS ──
            sidebarWidgets: [
                ['id' => 'customer',   'component' => 'CustomerCard',   'priority' => 10],
                ['id' => 'metrics',    'component' => 'QuickMetrics',   'priority' => 20],
                ['id' => 'technician', 'component' => 'TechnicianCard', 'priority' => 30, 'roles' => ['owner', 'admin', 'manager']],
                ['id' => 'sla',        'component' => 'SlaTimer',       'priority' => 40, 'roles' => ['owner', 'admin', 'manager']],
                ['id' => 'related',    'component' => 'RelatedServices', 'priority' => 50],
                ['id' => 'history',    'component' => 'ServiceHistory', 'priority' => 60],
            ],

            // ── INSPECTOR SECTIONS ──
            inspectorSections: [
                ['id' => 'properties', 'label' => 'Properti',   'icon' => '📋'],
                ['id' => 'metadata',   'label' => 'Metadata',   'icon' => 'ℹ️'],
                ['id' => 'tags',       'label' => 'Tags',       'icon' => '🏷️'],
                ['id' => 'relations',  'label' => 'Relasi',     'icon' => '🔗'],
                ['id' => 'automation', 'label' => 'Automation', 'icon' => '⚡'],
            ],

            // ── SHORTCUTS ──
            shortcuts: [
                ['key' => 'e', 'ctrl' => true, 'action' => 'edit',       'label' => 'Edit Service'],
                ['key' => 'r', 'ctrl' => true, 'action' => 'refresh',    'label' => 'Refresh'],
                ['key' => 'p', 'ctrl' => true, 'action' => 'print',      'label' => 'Cetak'],
                ['key' => 'k', 'ctrl' => true, 'action' => 'search',     'label' => 'Quick Search'],
            ],

            // ── FEATURE GATES ──
            features: ['services'],
            denyBusinessTypes: ['retail_only'],

            // ── CONFIG ──
            config: [
                'autoRefreshSeconds' => 30,
                'showAuditTrail' => true,
                'allowFileUpload' => true,
                'maxPhotosPerUpload' => 10,
                'timelinePageSize' => 20,
            ],
        );
    }
}
