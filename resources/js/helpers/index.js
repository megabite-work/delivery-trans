export const managerOrderStatuses = {
    CREATED: {label: 'Создана', color: '#1e1e1e', backgroundColor: '#f5f5f5'},
    REQUEST: {label: 'Запрос', color: '#d46b08', backgroundColor: '#fff7e6'},
    NOT_CONFIRMED: {label: 'Не подтверждена', color: '#cf1322', backgroundColor: '#fff1f0'},
    CONFIRMED: {label: 'Подтверждена', color: '#08979c', backgroundColor: '#e6fffb'},
    DRIVER_ASSIGNED: {label: 'Водитель назначен', color: '#1d39c4', backgroundColor: '#f0f5ff'},
    IN_PROGRESS: {label: 'На исполнении', color: '#1677ff', backgroundColor: '#e6f4ff'},
    COMPLETED: {label: 'Выполнена', color: '#52c41a', backgroundColor: '#f6ffed'},
    SPECIFY_THE_COST: {label: 'Уточняем стоимость', color: '#d48806', backgroundColor: '#fffbe6'},
    WAITING_FOR_RECONCILIATION: {label: 'Ждет сверку', color: '#531dab', backgroundColor: '#f9f0ff'},
    RECONCILIATION: {label: 'Сверена', color: '#c41d7f', backgroundColor: '#fff0f6'},
    DOCUMENTS_SENT: {label: 'Документы отправлены', color: '#0958d9', backgroundColor: '#e6f4ff'},
    INCOMPLETE_SET: {label: 'Неполный комплект', color: '#ff4d4f', backgroundColor: '#fff2f0'},
    DOCUMENTS_ACCEPTED: {label: 'Документы приняты', color: '#1d39c4', backgroundColor: '#f0f5ff'},
    INVOICE_CREATED: {label: 'Счет сформирован', color: '#389e0d', backgroundColor: '#f6ffed'},
    PAID: {label: 'Оплачена', color: '#52c41a', backgroundColor: '#f6ffed'},
    DEBT: {label: 'Долг', color: '#cf1322', backgroundColor: '#fff1f0'},
}

export const logistOrderStatuses = {
    CREATED: {label: 'Создана', color: '#1e1e1e', backgroundColor: '#f5f5f5'},
    ASSIGN_CARRIER: {label: 'Назначить перевозчика', color: '#d46b08', backgroundColor: '#fff7e6'},
    CARRIER_ASSIGNED: {label: 'Перевозчик назначен', color: '#08979c', backgroundColor: '#e6fffb'},
    IN_PROGRESS: {label: 'На исполнении', color: '#1677ff', backgroundColor: '#e6f4ff'},
    COMPLETED: {label: 'Выполнена', color: '#52c41a', backgroundColor: '#f6ffed'},
    WAITING_FOR_RECONCILIATION: {label: 'Ждет сверку', color: '#531dab', backgroundColor: '#f9f0ff'},
    RECONCILIATION: {label: 'Сверена', color: '#c41d7f', backgroundColor: '#fff0f6'},
    DOCUMENTS_SUBMITTED: {label: 'Документы сданы', color: '#0958d9', backgroundColor: '#e6f4ff'},
    INCOMPLETE_SET: {label: 'Неполный комплект', color: '#ff4d4f', backgroundColor: '#fff2f0'},
    DOCUMENTS_ACCEPTED: {label: 'Документы приняты', color: '#1d39c4', backgroundColor: '#f0f5ff'},
    INVOICE_CREATED: {label: 'Счет сформирован', color: '#389e0d', backgroundColor: '#f6ffed'},
    PAID: {label: 'Оплачена', color: '#52c41a', backgroundColor: '#f6ffed'},
}

export function setAll(obj, val) {
    Object.keys(obj).forEach(key => obj[key] = val)
}
