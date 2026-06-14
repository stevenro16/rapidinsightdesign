<?php

namespace App\Http\Controllers;

use App\Mail\WorkOrderCustomerMessage;
use App\Mail\WorkOrderValidated;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class CustomerWorkOrderController extends Controller
{
    private const ADMIN_EMAIL = 'admin@rapidinsightdesigns.com';

    public function index(): View
    {
        $workOrders = auth()->user()->workOrders()
            ->withCount('agreements')
            ->with(['notes' => fn ($q) => $q->where('visible_to_customer', true)])
            ->get();

        return view('work-orders.index', compact('workOrders'));
    }

    public function show(WorkOrder $workOrder): View
    {
        $this->guard($workOrder);

        $workOrder->load([
            'agreements',
            'notes'    => fn ($q) => $q->where('visible_to_customer', true),
            'invoices' => fn ($q) => $q->where('visible_to_customer', true),
        ]);

        return view('work-orders.show', compact('workOrder'));
    }

    /** Customer confirms a work order they were asked to validate. Admin still finalizes. */
    public function validateOrder(WorkOrder $workOrder): RedirectResponse
    {
        $this->guard($workOrder);
        abort_unless($workOrder->awaitingCustomer(), 422);

        if (! $workOrder->customerValidated()) {
            $workOrder->update(['customer_validated_at' => now()]);
            $workOrder->logEvent('customer_validated', 'Customer validated the work order.', auth()->id());

            try {
                Mail::to(self::ADMIN_EMAIL)->send(new WorkOrderValidated($workOrder));
            } catch (\Throwable $e) {
                Log::error('Work-order validated email failed: ' . $e->getMessage());
            }
        }

        return back()->with('success', 'Thanks! We\'ve recorded your approval and will finalize it shortly.');
    }

    /** Customer posts a message/note on the work order; notifies the admin. */
    public function storeNote(Request $request, WorkOrder $workOrder): RedirectResponse
    {
        $this->guard($workOrder);

        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);

        $workOrder->notes()->create([
            'author_id'           => auth()->id(),
            'body'                => $data['body'],
            'visible_to_customer' => true,
        ]);
        $workOrder->logEvent('note_added', 'Customer posted a message.', auth()->id());

        try {
            Mail::to(self::ADMIN_EMAIL)->send(new WorkOrderCustomerMessage($workOrder, $data['body']));
        } catch (\Throwable $e) {
            Log::error('Work-order customer message email failed: ' . $e->getMessage());
        }

        return back()->with('success', 'Message sent — we\'ll get back to you soon.');
    }

    private function guard(WorkOrder $workOrder): void
    {
        abort_unless($workOrder->user_id === auth()->id(), 403);
    }
}
