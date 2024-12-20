<?php
namespace App\Enums;

enum LogistOrderStatus: string
{
    case CREATED = 'CREATED';
    case ASSIGN_CARRIER = 'ASSIGN_CARRIER';
    case CARRIER_ASSIGNED = 'CARRIER_ASSIGNED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case WAITING_FOR_RECONCILIATION = 'WAITING_FOR_RECONCILIATION';
    case RECONCILIATION = 'RECONCILIATION';
    case DOCUMENTS_SUBMITTED = 'DOCUMENTS_SUBMITTED';
    case INCOMPLETE_SET = 'INCOMPLETE_SET';
    case DOCUMENTS_ACCEPTED = 'DOCUMENTS_ACCEPTED';
    case INVOICE_CREATED = 'INVOICE_CREATED';
    case PAID = 'PAID';

    public function label(): string {
        return match ($this) {
            LogistOrderStatus::CREATED => 'Создана',
            LogistOrderStatus::ASSIGN_CARRIER => 'Назначить перевозчика',
            LogistOrderStatus::CARRIER_ASSIGNED => 'Перевозчик назначен',
            LogistOrderStatus::IN_PROGRESS => 'На исполнении',
            LogistOrderStatus::COMPLETED => 'Выполнена',
            LogistOrderStatus::WAITING_FOR_RECONCILIATION => 'Ждет сверку',
            LogistOrderStatus::RECONCILIATION => 'Сверена',
            LogistOrderStatus::DOCUMENTS_SUBMITTED => 'Документы сданы',
            LogistOrderStatus::INCOMPLETE_SET => 'Неполный комплект',
            LogistOrderStatus::DOCUMENTS_ACCEPTED => 'Документы приняты',
            LogistOrderStatus::INVOICE_CREATED => 'Счет сформирован',
            LogistOrderStatus::PAID => 'Оплачена',
        };
    }
}
