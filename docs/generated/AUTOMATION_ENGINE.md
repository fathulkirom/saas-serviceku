# ServiceKU Enterprise Automation Engine

> **Sprint 13.0** — Event-driven automation for ALL ERP modules.
> **Status:** ✅ Production Ready

---

## 🎯 What is Automation Engine?

Automation Engine adalah sistem **If-This-Then-That** (IFTTT) enterprise untuk ServiceKU. Trigger → Condition → Action. Semua modul menggunakan engine yang sama.

---

## 🏗️ Architecture

```
Event (backend/frontend event)
  ↓
Trigger (what to listen for)
  ↓
Condition (when to execute)
  ↓
Automation (rule definition)
  ↓
Action (what to do)
  ↓
Queue (async execution)
  ↓
Result (success/failure log)
```

---

## 📊 Triggers (15+ built-in)

| Category | Triggers |
|----------|----------|
| **Record** | Created, Updated, Deleted |
| **Status** | Status Changed, Field Changed |
| **Time** | Schedule, Date Reached |
| **External** | Webhook, API, Manual |
| **Business** | Payment Success, Invoice Paid, Stock Low, Service Finished, Customer Created, Purchase Received |

---

## 🔍 Conditions (25+ operators)

| Category | Operators |
|----------|-----------|
| **Comparison** | Equals, Not Equals, Greater, Less, Between |
| **Text** | Contains, Starts With, Ends With |
| **State** | Is Empty, Is Not Empty |
| **List** | In, Not In |
| **User** | Role Is, Has Permission |
| **Org** | Branch Is, Business Type, Plan, Feature |
| **Date** | Before, After, Day Is, Month Is |

---

## ⚡ Actions (18 built-in)

| Category | Actions |
|----------|---------|
| **Record** | Update, Create, Delete, Change Status, Add Timeline, Create Activity |
| **Communication** | Send WhatsApp, Send Email, Push Notification |
| **Task** | Create Task, Create Reminder |
| **Document** | Create Invoice, Generate PDF |
| **External** | Webhook, HTTP Request, Run Workflow, Run Script |

---

## 📦 Reference Automations

| ID | Trigger | Conditions | Actions |
|----|---------|-----------|---------|
| `service.completed` | Status → Selesai | status = 'selesai' | Timeline + WhatsApp + Notification |
| `inventory.stock_low` | Stock Low | — | Activity + Notification |
| `crm.customer_welcome` | Customer Created | — | Task (1h delay) + Activity |

---

## 🔌 How to Create an Automation

```php
(new AutomationDefinition('my.rule', 'My Rule', trigger: TriggerType::STATUS_CHANGED))
    ->addCondition(new ConditionClause(ConditionOperator::EQUALS, 'status', 'done'))
    ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => 'Done!']))
    ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, ['message' => 'Automated.']));
```

---

## 🚀 Execution

```php
$runner = app(AutomationRunner::class);
$context = new AutomationContext('status.changed', subject: $service, changes: ['status' => 'done']);
$results = $runner->run(TriggerType::STATUS_CHANGED, $context);
```

---

*ServiceKU Enterprise Automation Engine — Sprint 13.0*
