<?php
namespace App\Enums;

enum ManagerOrderStatus: string
{
    case CREATED = 'CREATED';
    case REQUEST = 'REQUEST';
    case NOT_CONFIRMED = 'NOT_CONFIRMED';
    case CONFIRMED = 'CONFIRMED';
    case DRIVER_ASSIGNED = 'DRIVER_ASSIGNED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';
    case SPECIFY_THE_COST = 'SPECIFY_THE_COST';
    case WAITING_FOR_RECONCILIATION = 'WAITING_FOR_RECONCILIATION';
    case RECONCILIATION = 'RECONCILIATION';
    case DOCUMENTS_SENT = 'DOCUMENTS_SENT';
    case INCOMPLETE_SET = 'INCOMPLETE_SET';
    case DOCUMENTS_ACCEPTED = 'DOCUMENTS_ACCEPTED';
    case INVOICE_CREATED = 'INVOICE_CREATED';
    case PAID = 'PAID';
    case DEBT = 'DEBT';

    public function label(): string {
        return match ($this) {
            ManagerOrderStatus::CREATED => 'Создана',
            ManagerOrderStatus::REQUEST => 'Запрос',
            ManagerOrderStatus::NOT_CONFIRMED => 'Не подтверждена',
            ManagerOrderStatus::CONFIRMED => 'Подтверждена',
            ManagerOrderStatus::DRIVER_ASSIGNED => 'Водитель назначен',
            ManagerOrderStatus::IN_PROGRESS => 'На исполнении',
            ManagerOrderStatus::COMPLETED => 'Выполнена',
            ManagerOrderStatus::SPECIFY_THE_COST => 'Уточняем стоимость',
            ManagerOrderStatus::WAITING_FOR_RECONCILIATION => 'Ждет сверку',
            ManagerOrderStatus::RECONCILIATION => 'Сверена',
            ManagerOrderStatus::DOCUMENTS_SENT => 'Документы отправлены',
            ManagerOrderStatus::INCOMPLETE_SET => 'Неполный комплект',
            ManagerOrderStatus::DOCUMENTS_ACCEPTED => 'Документы приняты',
            ManagerOrderStatus::INVOICE_CREATED => 'Счет сформирован',
            ManagerOrderStatus::PAID => 'Оплачена',
            ManagerOrderStatus::DEBT => 'Долг',
        };
    }
}
