export const permissions = {
    ORDERS_LST_COLUMN_CREATED_AT: 'Дата заявки',
    ORDERS_LST_COLUMN_STARTED_AT: 'Дата и время старта поездки',
    ORDERS_LST_COLUMN_STATUS_MANAGER: 'Статус менеджера',
    ORDERS_LST_COLUMN_STATUS_LOGIST: 'Статус логиста',
    ORDERS_LST_COLUMN_CLIENT: 'Заказчик',
    ORDERS_LST_COLUMN_CARRIER: 'Перевозчик',
    ORDERS_LST_COLUMN_DRIVER: 'Водитель',
    ORDERS_LST_COLUMN_VEHICLE: 'Машина',
    ORDERS_LST_COLUMN_WEIGHT: 'Вес груза',
    ORDERS_LST_COLUMN_CLIENT_SUM: 'Сумма',
    ORDERS_LST_COLUMN_CARRIER_SUM: 'Себестоимость',
    ORDERS_LST_COLUMN_MARGIN_SUM: 'Маржа ₽',
    ORDERS_LST_COLUMN_MARGIN_PERCENT: 'Маржа %',
    ORDERS_LST_COLUMN_FROM: 'Адрес отправления',
    ORDERS_LST_COLUMN_TO: 'Адрес назначения',

    ORDERS_SECTION: 'Доступ к разделу \'Заявки\'',
    ORDERS_VIEW: 'Просмотр заявки',
    ORDERS_ADD: 'Добавление заявок',
    ORDERS_EDIT: 'Редактирование заявок',
    ORDERS_DELETE: 'Удаление заявок',
    CLIENTS_ORDERS_DOWNLOAD: 'Скачиваение заявок для клиентов',
    CARRIERS_ORDERS_DOWNLOAD: 'Скачивание заявок для перевозчиков',

    ORDER_CARGO_SECTION: 'Секция \'Груз\'',
    ORDER_CAR_SECTION: 'Секция \'Машина\'',
    ORDER_MANAGER_STATUS: 'Статус менеджера',
    ORDER_MANAGER_STATUS_CHANGE: 'Изменение статуса менеджера',
    ORDER_CARRIER_STATUS: 'Статус перевозчика',
    ORDER_CARRIER_STATUS_CHANGE: 'Изменение статуса перевозчика',
    ORDER_CLIENT_PRICE: 'Сумма клиента',
    ORDER_CARRIER_PRICE: 'Сумма перевозчика',
    ORDER_CUSTOM_CLIENT_PRICE: 'Установка ручной суммы клиента',
    ORDER_CUSTOM_CARRIER_PRICE: 'Установка ручной суммы перевозчика',

    ORDER_CLIENT_SECTION: 'Секция \'Заказчик\'',
    ORDER_CLIENT_TARIFF_SECTION: 'Секция \'Тариф заказчика\'',
    ORDER_CLIENT_EXPENSES_SECTION: 'Секция \'Допрасходы заказчика\'',
    ORDER_CLIENT_DISCOUNT_SECTION: 'Секция \'Скидки для заказчика\'',
    ORDER_CARRIER_SECTION: 'Секция \'Перевозчик\'',
    ORDER_CARRIER_TARIFF_SECTION: 'Секция \'Тариф перевозчика\'',
    ORDER_CARRIER_EXPENSES_SECTION: 'Секция \'Допрасходы перевозчика\'',
    ORDER_CARRIER_FINES_SECTION: 'Секция \'Штрафы перевозчика\'',

    ORDER_LOCATION_FROM_SECTION: 'Секция \'Откуда\'',
    ORDER_LOCATION_TO_SECTION: 'Секция \'Куда\'',

    ORDER_ADDITIONAL_SERVICES: 'Секция \'Дополнительные услуги\'',

    CLIENTS_SECTION: 'Доступ к разделу \'Заказчики\'',
    CLIENTS_VIEW: 'Просмотр заказчика',
    CLIENTS_ADD: 'Добавление заказчика',
    CLIENTS_EDIT: 'Редактирование заказчика',
    CLIENTS_DELETE: 'Удаление заказчика',
    CLIENTS_REGISTRIES_VIEW: 'Просмотр реестров заказчика',
    CLIENTS_REGISTRIES_EDIT: 'Редактирование реестров заказчика',
    CLIENTS_REGISTRIES_DELETE: 'Удаление реестров заказчика',
    CLIENTS_REGISTRIES_CREATE: 'Создание реестра заказчика',
    CLIENTS_REGISTRIES_DOWNLOAD: 'Скачивание PDF-реестра для клиента',

    CARRIERS_SECTION: 'Доступ к разделу \'Перевозчики\'',
    CARRIERS_VIEW: 'Просмотр перевозчика',
    CARRIERS_ADD: 'Добавление перевозчика',
    CARRIERS_EDIT: 'Редактирование перевозчика',
    CARRIERS_DELETE: 'Удаление перевозчика',
    CARRIERS_REGISTRIES_VIEW: 'Просмотр реестров перевозчика',
    CARRIERS_REGISTRIES_EDIT: 'Редактирование реестров перевозчика',
    CARRIERS_REGISTRIES_DELETE: 'Удаление реестров перевозчика',
    CARRIERS_REGISTRIES_CREATE: 'Создание реестра перевозчика',
    CARRIERS_REGISTRIES_DOWNLOAD: 'Скачивание PDF-реестра для перевозчика',


    PRICES_DIR: 'Справочник \'Прайс-листы\'',
    BODY_TYPES_DIR: 'Справочник \'Типы кузовов\'',
    CAPACITIES_DIR: 'Справочник \'Вместительность авто\'',
    T_CONDITIONS_DIR: 'Справочник \'Температурные условия\'',
    TONNAGES_DIR: 'Справочник \'Тоннаж\'',
    COMPANIES_DIR: 'Справочник \'Компании\'',

    USERS_DIR: 'Справочник \'Пользователи\'',
    USERS_CREATE: 'Добавление пользователей',
    USERS_DELETE: 'Удаоение пользователей',
    USERS_EDIT: 'Редактирование пользователей',

    ROLES_DIR: 'Справочник \'Роли\'',
    ROLES_CREATE: 'Создание ролей',
    ROLES_EDIT: 'Редактирование ролей',
    ROLES_DELETE: 'Удаление ролей',
}

export const permissionsSections = [
    {
        label: 'Заявки',
        permissions: [
            'ORDERS_SECTION',
            'ORDERS_VIEW',
            'ORDERS_ADD',
            'ORDERS_EDIT',
            'ORDERS_DELETE',
            'CLIENTS_ORDERS_DOWNLOAD',
            'CARRIERS_ORDERS_DOWNLOAD',
        ]
    },
    {
        label: 'Секции заявки',
        permissions: [
            'ORDER_CARGO_SECTION',
            'ORDER_CAR_SECTION',
            'ORDER_MANAGER_STATUS',
            'ORDER_MANAGER_STATUS_CHANGE',
            'ORDER_CARRIER_STATUS',
            'ORDER_CARRIER_STATUS_CHANGE',
            'ORDER_CLIENT_PRICE',
            'ORDER_CARRIER_PRICE',
            'ORDER_CUSTOM_CLIENT_PRICE',
            'ORDER_CUSTOM_CARRIER_PRICE',
            'ORDER_CLIENT_SECTION',
            'ORDER_CLIENT_TARIFF_SECTION',
            'ORDER_CLIENT_EXPENSES_SECTION',
            'ORDER_CLIENT_DISCOUNT_SECTION',
            'ORDER_CARRIER_SECTION',
            'ORDER_CARRIER_TARIFF_SECTION',
            'ORDER_CARRIER_EXPENSES_SECTION',
            'ORDER_CARRIER_FINES_SECTION',
            'ORDER_LOCATION_FROM_SECTION',
            'ORDER_LOCATION_TO_SECTION',
            'ORDER_ADDITIONAL_SERVICES',
        ]
    },
    {
        label: 'Отображенеие колонок в списке заявок',
        permissions: [
            'ORDERS_LST_COLUMN_CREATED_AT',
            'ORDERS_LST_COLUMN_STARTED_AT',
            'ORDERS_LST_COLUMN_STATUS_MANAGER',
            'ORDERS_LST_COLUMN_STATUS_LOGIST',
            'ORDERS_LST_COLUMN_CLIENT',
            'ORDERS_LST_COLUMN_CARRIER',
            'ORDERS_LST_COLUMN_DRIVER',
            'ORDERS_LST_COLUMN_VEHICLE',
            'ORDERS_LST_COLUMN_WEIGHT',
            'ORDERS_LST_COLUMN_CLIENT_SUM',
            'ORDERS_LST_COLUMN_CARRIER_SUM',
            'ORDERS_LST_COLUMN_MARGIN_SUM',
            'ORDERS_LST_COLUMN_MARGIN_PERCENT',
            'ORDERS_LST_COLUMN_FROM',
            'ORDERS_LST_COLUMN_TO',
        ]
    },
    {
        label: 'Заказчики',
        permissions: [
            'CLIENTS_SECTION',
            'CLIENTS_VIEW',
            'CLIENTS_ADD',
            'CLIENTS_EDIT',
            'CLIENTS_DELETE',
            'CLIENTS_REGISTRIES_VIEW',
            'CLIENTS_REGISTRIES_EDIT',
            'CLIENTS_REGISTRIES_DELETE',
            'CLIENTS_REGISTRIES_CREATE',
            'CLIENTS_REGISTRIES_DOWNLOAD',
        ]
    },
    {
        label: 'Перевозчики',
        permissions: [
            'CARRIERS_SECTION',
            'CARRIERS_VIEW',
            'CARRIERS_ADD',
            'CARRIERS_EDIT',
            'CARRIERS_DELETE',
            'CARRIERS_REGISTRIES_VIEW',
            'CARRIERS_REGISTRIES_EDIT',
            'CARRIERS_REGISTRIES_DELETE',
            'CARRIERS_REGISTRIES_CREATE',
            'CARRIERS_REGISTRIES_DOWNLOAD',
        ]
    },
    {
        label: 'Справочники',
        permissions: [
            'PRICES_DIR',
            'BODY_TYPES_DIR',
            'CAPACITIES_DIR',
            'T_CONDITIONS_DIR',
            'TONNAGES_DIR',
            'COMPANIES_DIR',
        ]
    },
    {
        label: 'Пользователи и роли',
        permissions: [
            'USERS_DIR',
            'USERS_CREATE',
            'USERS_DELETE',
            'USERS_EDIT',
            'ROLES_DIR',
            'ROLES_CREATE',
            'ROLES_EDIT',
            'ROLES_DELETE',
        ]
    },
]

export const permissionColumns = {
    ORDERS_LST_COLUMN_CREATED_AT: ['created_at', 'updated_at'],
    ORDERS_LST_COLUMN_STARTED_AT: ['started_at'],
    ORDERS_LST_COLUMN_STATUS_MANAGER: ['status_manager'],
    ORDERS_LST_COLUMN_STATUS_LOGIST: ['status_logist'],
    ORDERS_LST_COLUMN_CLIENT: ['client'],
    ORDERS_LST_COLUMN_CARRIER: ['carrier'],
    ORDERS_LST_COLUMN_DRIVER: ['carrier_driver_id', 'carrier_driver'],
    ORDERS_LST_COLUMN_VEHICLE: ['carrier_car_id', 'carrier_car'],
    ORDERS_LST_COLUMN_WEIGHT: ['cargo_weight'],
    ORDERS_LST_COLUMN_CLIENT_SUM: ['client_sum'],
    ORDERS_LST_COLUMN_CARRIER_SUM: ['carrier_sum'],
    ORDERS_LST_COLUMN_MARGIN_SUM: ['margin_sum'],
    ORDERS_LST_COLUMN_MARGIN_PERCENT: ['margin_percent'],
    ORDERS_LST_COLUMN_FROM: ['from_locations'],
    ORDERS_LST_COLUMN_TO: ['to_locations'],
}