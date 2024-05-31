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
}
