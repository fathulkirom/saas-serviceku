<?php

namespace App\Enums;

class Status
{
    public const PENDING = 'pending';

    public const ACTIVE = 'active';

    public const INACTIVE = 'inactive';

    public const DRAFT = 'draft';

    public const COMPLETED = 'completed';

    public const CANCELLED = 'cancelled';

    public const CLOSED = 'closed';

    public const ARCHIVED = 'archived';

    public const APPROVED = 'approved';

    public const REJECTED = 'rejected';

    public const SUBMITTED = 'submitted';

    public const PAID = 'paid';

    public const UNPAID = 'unpaid';

    public const REFUNDED = 'refunded';

    public const EXPIRED = 'expired';

    public const FAILED = 'failed';

    public const SUCCESS = 'success';

    public const PROCESSING = 'processing';

    public const WAITING = 'waiting';

    public const IN_PROGRESS = 'in_progress';

    public const ON_HOLD = 'on_hold';

    public const PAUSED = 'paused';

    public const DONE = 'done';

    public const ASSIGNED = 'assigned';

    public const ACCEPTED = 'accepted';

    public const RETURNED = 'returned';

    public const RESERVED = 'reserved';

    public const USED = 'used';

    public const OPEN = 'open';

    public const RESOLVED = 'resolved';

    public const VOID = 'void';

    public static function all(): array
    {
        return (new \ReflectionClass(static::class))->getConstants();
    }
}
