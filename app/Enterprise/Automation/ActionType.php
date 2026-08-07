<?php

namespace App\Enterprise\Automation;

enum ActionType: string
{
    case UPDATE_RECORD = 'update_record';
    case CREATE_RECORD = 'create_record';
    case DELETE_RECORD = 'delete_record';
    case SEND_WHATSAPP = 'send_whatsapp';
    case SEND_EMAIL = 'send_email';
    case PUSH_NOTIFICATION = 'push_notification';
    case CREATE_TASK = 'create_task';
    case CREATE_INVOICE = 'create_invoice';
    case GENERATE_PDF = 'generate_pdf';
    case ASSIGN_TECHNICIAN = 'assign_technician';
    case CHANGE_STATUS = 'change_status';
    case ADD_TIMELINE = 'add_timeline';
    case CREATE_ACTIVITY = 'create_activity';
    case CREATE_REMINDER = 'create_reminder';
    case WEBHOOK = 'webhook';
    case HTTP_REQUEST = 'http_request';
    case RUN_WORKFLOW = 'run_workflow';
    case RUN_SCRIPT = 'run_script';

    public function label(): string
    {
        return match ($this) {
            self::UPDATE_RECORD => 'Update Record',
            self::CREATE_RECORD => 'Create Record',
            self::DELETE_RECORD => 'Delete Record',
            self::SEND_WHATSAPP => 'Send WhatsApp',
            self::SEND_EMAIL => 'Send Email',
            self::PUSH_NOTIFICATION => 'Push Notification',
            self::CREATE_TASK => 'Create Task',
            self::CREATE_INVOICE => 'Create Invoice',
            self::GENERATE_PDF => 'Generate PDF',
            self::ASSIGN_TECHNICIAN => 'Assign Technician',
            self::CHANGE_STATUS => 'Change Status',
            self::ADD_TIMELINE => 'Add Timeline',
            self::CREATE_ACTIVITY => 'Create Activity',
            self::CREATE_REMINDER => 'Create Reminder',
            self::WEBHOOK => 'Call Webhook',
            self::HTTP_REQUEST => 'HTTP Request',
            self::RUN_WORKFLOW => 'Run Workflow',
            self::RUN_SCRIPT => 'Run Script',
        };
    }
}
