<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\CustomerCommunication;
use App\Models\Tenant\CustomerMessageTemplate;
use App\Services\ProviderAdapter;
use Illuminate\Http\Request;

/**
 * Customer Communication Controller — Sprint 7.3C.
 * Thin controller — delegates to ProviderAdapter for all sending.
 */
class CustomerCommunicationController extends Controller
{
    /** List templates */
    public function templates()
    {
        $templates = CustomerMessageTemplate::orderBy('name')->get();
        return inertia('Pengaturan/MessageTemplates', ['templates' => $templates]);
    }

    /** Store template */
    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'key' => 'required|string|unique:customer_message_templates',
            'channel' => 'required|in:whatsapp,email',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);
        CustomerMessageTemplate::create($data);
        return back()->with('success', 'Template berhasil dibuat.');
    }

    /** Update template */
    public function updateTemplate(Request $request, CustomerMessageTemplate $template)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'channel' => 'required|in:whatsapp,email',
            'subject' => 'nullable|string|max:255',
            'body' => 'required|string',
            'is_active' => 'boolean',
        ]);
        $template->update($data);
        return back()->with('success', 'Template diperbarui.');
    }

    /** Send a communication to a customer */
    public function send(Request $request, Customer $customer, ProviderAdapter $adapter)
    {
        $this->authorize('update', $customer);

        $data = $request->validate([
            'type' => 'required|in:whatsapp,email',
            'message' => 'required|string|max:4000',
            'template_id' => 'nullable|exists:customer_message_templates,id',
        ]);

        $recipient = $data['type'] === 'whatsapp' ? $customer->phone : $customer->email;

        if (empty($recipient)) {
            return back()->with('error', "Customer tidak memiliki " . ($data['type'] === 'whatsapp' ? 'nomor telepon' : 'email') . '.');
        }

        // Create communication record (draft)
        $comm = CustomerCommunication::create([
            'customer_id' => $customer->id,
            'type' => $data['type'],
            'direction' => 'outbound',
            'status' => 'queued',
            'recipient' => $recipient,
            'message' => $data['message'],
            'template_id' => $data['template_id'] ?? null,
            'provider' => $data['type'] === 'whatsapp' ? 'whatsapp_web' : 'smtp',
            'actor_id' => auth()->id(),
        ]);

        // Send via ProviderAdapter — single abstraction for all channels
        $result = $adapter->send($data['type'], $recipient, $data['message']);

        if ($result['status'] === 'success') {
            $comm->markSent($result['provider_message_id'] ?? null);
            // Fire event for timeline + automation
            event(new \App\Events\Entity\CustomerCommunicationSent($comm));
        } else {
            $comm->markFailed($result['message'] ?? 'Unknown error');
            event(new \App\Events\Entity\CustomerCommunicationFailed($comm));
        }

        return back()->with($result['status'] === 'success' ? 'success' : 'error',
            $result['status'] === 'success' ? 'Pesan terkirim.' : 'Gagal mengirim: ' . $result['message']);
    }
}
