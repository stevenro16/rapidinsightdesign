<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Agreement extends Model
{
    /** Fallback services-agreement template used when no SiteContent default is set. */
    public const DEFAULT_BODY = <<<'TXT'
SERVICES AGREEMENT

This Services Agreement ("Agreement") is entered into between RapidInsight Designs ("Provider") and the Client identified in your account ("Client").

1. SCOPE OF WORK
Provider agrees to design, develop, and deliver the web and software services described in the accompanying statement of work and project communications. Any work outside the agreed scope will be quoted separately before it begins.

2. FEES & PAYMENT
The total fee for the services is the Total Amount shown on this agreement. A deposit (the Deposit Amount shown) is due before work begins. Remaining balances are due according to the project milestones or upon completion, as recorded against this agreement. Work may be paused if payments are past due.

3. TIMELINE
Provider will make commercially reasonable efforts to meet the agreed schedule. Timelines depend on the Client supplying content, feedback, and approvals in a timely manner.

4. REVISIONS
Reasonable revisions within the agreed scope are included. Substantial changes after approval of a deliverable may incur additional fees.

5. INTELLECTUAL PROPERTY
Upon receipt of full payment, the Client owns the final delivered work product. Provider retains the right to reuse general techniques, code libraries, and to display the work in its portfolio unless otherwise agreed in writing.

6. CONFIDENTIALITY
Each party agrees to keep the other's non-public business information confidential.

7. WARRANTY & LIABILITY
Provider warrants the services will be performed in a professional manner. To the maximum extent permitted by law, Provider's total liability is limited to the fees paid under this agreement.

8. TERMINATION
Either party may terminate with written notice. The Client remains responsible for payment for all work completed up to the termination date.

9. ACCEPTANCE
By checking the box, signing below, and submitting payment, the Client acknowledges they have read, understood, and agreed to the terms of this Agreement.
TXT;

    /** Production-release template: services rendered + payment to roll out + production-readiness sign-off. */
    public const PRODUCTION_BODY = <<<'TXT'
SERVICES RENDERED & PRODUCTION RELEASE AGREEMENT

This agreement confirms the work completed by RapidInsight Designs ("Provider") for the Client identified in your account ("Client") and authorizes release of the solution to production.

1. SERVICES RENDERED
Provider has designed, developed, and delivered the web and software services described in the accompanying statement of work and project communications. The Client has had the opportunity to review the delivered work.

2. PRODUCTION READINESS & APPROVAL
By signing this agreement, the Client acknowledges and agrees that the current state of the website/application has been reviewed, accepted, and is ready for production. The Client authorizes Provider to roll out — deploy and launch — the solution to the live production environment in its current state.

3. PAYMENT TO PROCEED
Payment of the amount shown on this agreement is due in order to begin the production rollout. Provider will commence deployment upon receipt of the agreed payment (or deposit). Any outstanding balance remains due per the agreed terms.

4. POST-LAUNCH
After launch, requests for new features or changes outside the original scope will be quoted separately. Reasonable fixes to the delivered functionality are handled under our standard support terms.

5. INTELLECTUAL PROPERTY
Upon receipt of full payment, ownership of the final delivered work product transfers to the Client, consistent with the original services agreement.

6. ACCEPTANCE
By checking the box, signing below, and submitting payment, the Client confirms the solution is approved for production and authorizes its release to the live environment in its current state.
TXT;

    protected $fillable = [
        'user_id', 'work_order_id', 'created_by', 'title', 'body', 'status', 'has_cost',
        'total_amount', 'deposit_amount',
        'agreed', 'agreed_at', 'signature_method', 'signature_data', 'signature_name', 'signature_font', 'signed_at',
        'sent_at', 'submitted_at', 'completed_at', 'canceled_at', 'completed_by',
    ];

    protected $casts = [
        'has_cost'       => 'boolean',
        'total_amount'   => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'agreed'         => 'boolean',
        'agreed_at'      => 'datetime',
        'signed_at'      => 'datetime',
        'sent_at'        => 'datetime',
        'submitted_at'   => 'datetime',
        'completed_at'   => 'datetime',
        'canceled_at'    => 'datetime',
    ];

    /* ── Relationships ─────────────────────────────────────────────────── */

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->latest();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /* ── Money ─────────────────────────────────────────────────────────── */

    public function amountPaid(): float
    {
        return (float) $this->payments->where('status', 'confirmed')->sum('amount');
    }

    public function amountPending(): float
    {
        return (float) $this->payments->where('status', 'pending')->sum('amount');
    }

    public function balance(): float
    {
        return max(0, (float) $this->total_amount - $this->amountPaid());
    }

    public function depositPaid(): bool
    {
        return $this->amountPaid() >= (float) $this->deposit_amount && (float) $this->deposit_amount > 0;
    }

    /* ── Status helpers ────────────────────────────────────────────────── */

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending_customer_review' => 'Pending your review',
            'pending_validation'      => 'Pending validation',
            'completed'               => 'Completed',
            'canceled'                => 'Canceled',
            default                   => 'Draft',
        };
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'completed'               => 'badge-green',
            'pending_validation'      => 'badge-amber',
            'pending_customer_review' => 'badge-blue',
            'canceled'                => 'badge-red',
            default                   => 'badge-muted',
        };
    }

    public function actionNeededForCustomer(): bool
    {
        return $this->status === 'pending_customer_review';
    }

    public function actionNeededForAdmin(): bool
    {
        return $this->status === 'pending_validation';
    }

    public function hasSignature(): bool
    {
        return filled($this->signature_data);
    }

    /* ── State-machine guards ──────────────────────────────────────────── */

    public function isLocked(): bool
    {
        return in_array($this->status, ['completed', 'canceled'], true);
    }

    public function canSend(): bool
    {
        // A $0 / no-cost agreement is allowed — only the body is required.
        return $this->status === 'draft' && filled($this->body);
    }

    public function canCustomerSign(): bool
    {
        return $this->status === 'pending_customer_review';
    }

    /** Whether the customer must record a payment before submitting. */
    public function requiresPayment(): bool
    {
        return $this->has_cost && (float) $this->total_amount > 0;
    }

    public function canSubmit(): bool
    {
        return $this->status === 'pending_customer_review'
            && $this->agreed
            && $this->hasSignature()
            && (! $this->requiresPayment() || $this->payments()->count() > 0);
    }

    public function canValidate(): bool
    {
        return $this->status === 'pending_validation';
    }

    public function canCancel(): bool
    {
        return ! $this->isLocked();
    }
}
