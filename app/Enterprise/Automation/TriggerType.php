<?php

namespace App\Enterprise\Automation;

enum TriggerType: string
{
    case RECORD_CREATED = 'record.created';
    case RECORD_UPDATED = 'record.updated';
    case RECORD_DELETED = 'record.deleted';
    case STATUS_CHANGED = 'status.changed';
    case FIELD_CHANGED = 'field.changed';
    case DATE_REACHED = 'date.reached';
    case SCHEDULE = 'schedule';
    case WEBHOOK = 'webhook';
    case API = 'api';
    case MANUAL = 'manual';
    case PAYMENT_SUCCESS = 'payment.success';
    case INVOICE_PAID = 'invoice.paid';
    case STOCK_LOW = 'stock.low';
    case SERVICE_FINISHED = 'service.finished';
    case CUSTOMER_CREATED = 'customer.created';
    case PURCHASE_RECEIVED = 'purchase.received';

    public function label(): string
    {
        return match ($this) {
            self::RECORD_CREATED => 'Record Created',
            self::RECORD_UPDATED => 'Record Updated',
            self::RECORD_DELETED => 'Record Deleted',
            self::STATUS_CHANGED => 'Status Changed',
            self::FIELD_CHANGED => 'Field Changed',
            self::DATE_REACHED => 'Date Reached',
            self::SCHEDULE => 'Schedule',
            self::WEBHOOK => 'Webhook',
            self::API => 'API Call',
            self::MANUAL => 'Manual Trigger',
            self::PAYMENT_SUCCESS => 'Payment Success',
            self::INVOICE_PAID => 'Invoice Paid',
            self::STOCK_LOW => 'Stock Low',
            self::SERVICE_FINISHED => 'Service Finished',
            self::CUSTOMER_CREATED => 'Customer Created',
            self::PURCHASE_RECEIVED => 'Purchase Received',
        };
    }
}
