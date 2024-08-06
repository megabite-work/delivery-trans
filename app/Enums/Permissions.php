<?php

namespace App\Enums;

enum Permissions: string
{
    case ALL = '*';
    case ORDERS_LST_COLUMN_ID = 'ORDERS_LST_COLUMN_ID';
    case ORDERS_LST_COLUMN_CREATED_AT = 'ORDERS_LST_COLUMN_CREATED_AT';
    case ORDERS_LST_COLUMN_STARTED_AT = 'ORDERS_LST_COLUMN_STARTED_AT';
    case ORDERS_LST_COLUMN_STATUS_MANAGER = 'ORDERS_LST_COLUMN_STATUS_MANAGER';
    case ORDERS_LST_COLUMN_STATUS_LOGIST = 'ORDERS_LST_COLUMN_STATUS_LOGIST';
    case ORDERS_LST_COLUMN_CLIENT = 'ORDERS_LST_COLUMN_CLIENT';
    case ORDERS_LST_COLUMN_CARRIER = 'ORDERS_LST_COLUMN_CARRIER';
    case ORDERS_LST_COLUMN_DRIVER = 'ORDERS_LST_COLUMN_DRIVER';
    case ORDERS_LST_COLUMN_VEHICLE = 'ORDERS_LST_COLUMN_VEHICLE';
    case ORDERS_LST_COLUMN_WEIGHT = 'ORDERS_LST_COLUMN_WEIGHT';
    case ORDERS_LST_COLUMN_CLIENT_SUM = 'ORDERS_LST_COLUMN_CLIENT_SUM';
    case ORDERS_LST_COLUMN_CARRIER_SUM = 'ORDERS_LST_COLUMN_CARRIER_SUM';
    case ORDERS_LST_COLUMN_MARGIN_SUM = 'ORDERS_LST_COLUMN_MARGIN_SUM';
    case ORDERS_LST_COLUMN_MARGIN_PERCENT = 'ORDERS_LST_COLUMN_MARGIN_PERCENT';
    case ORDERS_LST_COLUMN_FROM = 'ORDERS_LST_COLUMN_FROM';
    case ORDERS_LST_COLUMN_TO = 'ORDERS_LST_COLUMN_TO';

    case ORDERS_SECTION = 'ORDERS_SECTION';
    case ORDERS_VIEW = 'ORDERS_VIEW';
    case ORDERS_ADD = 'ORDERS_ADD';
    case ORDERS_EDIT = 'ORDERS_EDIT';
    case ORDERS_DELETE = 'ORDERS_DELETE';

    case ORDER_CARGO_SECTION = 'ORDER_CARGO_SECTION';
    case ORDER_CAR_SECTION = 'ORDER_CAR_SECTION';
    case ORDER_MANAGER_STATUS = 'ORDER_MANAGER_STATUS';
    case ORDER_MANAGER_STATUS_CHANGE = 'ORDER_MANAGER_STATUS_CHANGE';
    case ORDER_CARRIER_STATUS = 'ORDER_CARRIER_STATUS';
    case ORDER_CARRIER_STATUS_CHANGE = 'ORDER_CARRIER_STATUS_CHANGE';
    case ORDER_CLIENT_PRICE = 'ORDER_CLIENT_PRICE';
    case ORDER_CARRIER_PRICE = 'ORDER_CARRIER_PRICE';
    case ORDER_CUSTOM_CLIENT_PRICE = 'ORDER_CUSTOM_CLIENT_PRICE';
    case ORDER_CUSTOM_CARRIER_PRICE = 'ORDER_CUSTOM_CARRIER_PRICE';

    case ORDER_CLIENT_SECTION = 'ORDER_CLIENT_SECTION';
    case ORDER_CLIENT_TARIFF_SECTION = 'ORDER_CLIENT_TARIFF_SECTION';
    case ORDER_CLIENT_EXPENSES_SECTION = 'ORDER_CLIENT_EXPENSES_SECTION';
    case ORDER_CLIENT_DISCOUNT_SECTION = 'ORDER_CLIENT_DISCOUNT_SECTION';
    case ORDER_CARRIER_SECTION = 'ORDER_CARRIER_SECTION';
    case ORDER_CARRIER_TARIFF_SECTION = 'ORDER_CARRIER_TARIFF_SECTION';
    case ORDER_CARRIER_EXPENSES_SECTION = 'ORDER_CARRIER_EXPENSES_SECTION';
    case ORDER_CARRIER_FINES_SECTION = 'ORDER_CARRIER_FINES_SECTION';

    case ORDER_LOCATION_FROM_SECTION = 'ORDER_LOCATION_FROM_SECTION';
    case ORDER_LOCATION_TO_SECTION = 'ORDER_LOCATION_TO_SECTION';

    case ORDER_ADDITIONAL_SERVICES = 'ORDER_ADDITIONAL_SERVICES';

    case CLIENTS_SECTION = 'CLIENTS_SECTION';
    case CLIENTS_VIEW = 'CLIENTS_VIEW';
    case CLIENTS_ADD = 'CLIENTS_ADD';
    case CLIENTS_EDIT = 'CLIENTS_EDIT';
    case CLIENTS_DELETE = 'CLIENTS_DELETE';
    case CLIENT_REGISTRIES_VIEW = 'CLIENT_REGISTRIES_VIEW';
    case CLIENTS_REGISTRIES_EDIT = 'CLIENTS_REGISTRIES_EDIT';
    case CLIENTS_REGISTRIES_DELETE = 'CLIENTS_REGISTRIES_DELETE';
    case CLIENTS_REGISTRIES_CREATE = 'CLIENTS_REGISTRIES_CREATE';

    case CARRIERS_SECTION = 'CARRIERS_SECTION';
    case CARRIERS_VIEW = 'CARRIERS_VIEW';
    case CARRIERS_ADD = 'CARRIERS_ADD';
    case CARRIERS_EDIT = 'CARRIERS_EDIT';
    case CARRIERS_DELETE = 'CARRIERS_DELETE';
    case CARRIERS_REGISTRIES_VIEW = 'CARRIER_REGISTRIES_VIEW';
    case CARRIERS_REGISTRIES_EDIT = 'CARRIER_REGISTRIES_EDIT';
    case CARRIERS_REGISTRIES_DELETE = 'CARRIER_REGISTRIES_DELETE';
    case CARRIERS_REGISTRIES_CREATE = 'CARRIER_REGISTRIES_CREATE';

    case PRICES_DIR = 'PRICES_DIR';
    case BODY_TYPES_DIR = 'BODY_TYPES_DIR';
    case CAPACITIES_DIR = 'CAPACITIES_DIR';
    case T_CONDITIONS_DIR = 'T_CONDITIONS_DIR';
    case TONNAGES_DIR = 'TONNAGES_DIR';

    case USERS_DIR = 'USERS_DIR';
    case USERS_CREATE = 'USERS_CREATE';
    case USERS_DELETE = 'USERS_DELETE';
    case USERS_EDIT = 'USERS_EDIT';

    case ROLES_DIR = 'ROLES_DIR';
    case ROLES_CREATE = 'ROLES_CREATE';
    case ROLES_EDIT = 'ROLES_EDIT';
    case ROLES_DELETE = 'ROLES_DELETE';
}
