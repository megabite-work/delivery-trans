<script setup>
import {ref, h, watch, computed} from "vue";
import axios from "axios";
import {debounce, isArray, trim} from "radash";

import {message} from "ant-design-vue";
import {
    CheckCircleTwoTone,
    CloseCircleTwoTone,
    EditOutlined,
    ReloadOutlined,
    UserOutlined
} from '@ant-design/icons-vue';
import {useSuggests} from "../../stores/models/suggests.js";
import {usePricesStore} from "../../stores/models/prices.js";
import {useClientsStore} from "../../stores/models/clients.js";
import {managerOrderStatuses, logistOrderStatuses, decl} from "../../helpers/index.js";
import {useOrdersStore} from "../../stores/models/orders.js";
import {useAuthStore} from "../../stores/auth.js";

import KeyValueTable from "../KeyValueTable.vue";
import AddressList from "../AddressList.vue";
import SelectValueTableWithCnt from "../SelectValueTableWithCnt.vue";
import dayjs from "dayjs";


const authStore = useAuthStore()
const ordersStore = useOrdersStore()
const suggest = useSuggests()
const pricesStore = usePricesStore()
const clientStore = useClientsStore()
const model = defineModel()
const prop = defineProps({
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: null },
    readOnly: {type: Boolean, default: false }
})

const isStatusLoading = ref(false);
const cargoWeight = ref()
const capacitySearchError = ref(false)

watch(() => model.value.cargo_weight, () => {
    if (!!model.value.cargo_weight) {
        cargoWeight.value = model.value.cargo_weight / (weightSegmentsValue.value === 'т' ? 1000 : 1)
        return
    }
    cargoWeight.value = model.value.cargo_weight
})
const handleCargoWeightChange = async v => {
    model.value.cargo_weight = v * (weightSegmentsValue.value === 'т' ? 1000 : 1)
    await syncCargoWeightWithCap(model.value.cargo_weight / 1000, model.value.cargo_pallets_count)
}

const handlePalletsChange = async () => {
    await syncCargoWeightWithCap(model.value.cargo_weight / 1000, model.value.cargo_pallets_count)
}

const syncCargoWeightWithCap = debounce({delay: 500}, async (tonnage, pallets) => {
    await fetchCarCapacities()
    const o = [...carCapacitiesOptions.value].sort((a, b) => a.tonnage - b.tonnage).find((el) => el.tonnage >= (tonnage ? tonnage : 0) && (!model.value.cargo_in_pallets || el.pallets_count >= (pallets ? pallets : 0)))
    if (o) {
        model.value.car_capacity_id = o.id
        capacitySearchError.value = false
        await orderCalculate(false)
    } else {
        capacitySearchError.value = true
    }
})

const handleWeightTypeChange = (v) => {
    if (!!cargoWeight.value) {
        if (v === 'т') {
            cargoWeight.value = cargoWeight.value / 1000
            return
        }
        cargoWeight.value = cargoWeight.value * 1000
        return;
    }
    cargoWeight.value = 0
}
const currentCarrierTab = ref('carrier')
const currentClientTab = ref('client')

const weightSegments = ['т', 'кг']
const weightSegmentsValue = ref('т')

const cargoNameOptions = ref([])
const handleCargoNameSearch = debounce({delay: 500}, async (q) => {
    cargoNameOptions.value = await suggest.getCargoNameSuggest(q)
})

const carCapacitiesList = ref([])
const carCapacitiesOptions = computed(() => {
     let res = [...carCapacitiesList.value]
     if (res.findIndex((el) => el.value === model.value.car_capacity_id) < 0 && model.value.car_capacity && model.value.car_capacity_id) {
         res = [{
             value: model.value.car_capacity.id,
             label: `${model.value.car_capacity.tonnage}т. – ${model.value.car_capacity.volume}м³. – ${model.value.car_capacity.pallets_count}п.`,
             ...model.value.car_capacity
         }, ...res]
     }
     return res
})
const fetchCarCapacities = async () => {
    carCapacitiesList.value = await suggest.getCarCapacities()
}

const carBodyTypesOptions = ref([])
const fetchBodyTypesOptions = async () => {
    carBodyTypesOptions.value = await suggest.getCarBodyTypes()
}

const defaultPricesOptions = ref([])
const fetchDefaultPricesOptions = async () => {
    try {
        defaultPricesOptions.value = await pricesStore.getDefaultPrices()
    } catch {
        message.error('Не удалось получить прайс-листы')
    }
}
const handlePriceLoadingOpen = async (open) => {
    if (open) {
        await fetchDefaultPricesOptions()
    }
}

const applyClientPrice = async type => {
    if (!model.value.client_id) {
        return
    }
    try {
        const client = await clientStore.getClient(model.value.client_id)
        applyDefaultPrice(client, type)
    } catch {
        message.error('Не удалось получить прайс клиента')
    }
}
const applyDefaultPrice = (defaultPrice, type, onlyExists = false) => {
    if (!model.value.car_capacity_id || !model.value.vehicle_body_type) {
        return false
    }
    const p = defaultPrice.prices.find(price => {
        return price.type === type &&
        price.car_capacity_id === model.value.car_capacity_id &&
        price.car_body_type === model.value.vehicle_body_type
    })
    if (!p && onlyExists) {
        return false;
    }
    if (type === 'CARRIER') {
        model.value.carrier_tariff_hourly = p ? parseFloat(p.hourly) : 0
        model.value.carrier_tariff_min_hours = p ? parseFloat(p.min_hours) : 0
        model.value.carrier_tariff_hours_for_coming = p ? parseFloat(p.hours_for_coming) : 0
        model.value.carrier_tariff_mkad_price = p ? parseFloat(p.mkad_price) : 0
    } else if (type === 'CLIENT') {
        model.value.client_tariff_hourly = p ? parseFloat(p.hourly) : 0
        model.value.client_tariff_min_hours = p ? parseFloat(p.min_hours) : 0
        model.value.client_tariff_hours_for_coming = p ? parseFloat(p.hours_for_coming) : 0
        model.value.client_tariff_mkad_price = p ? parseFloat(p.mkad_price) : 0
    }
    return true
}
const handlePRefresh = async () => {
    if (!model.value.car_capacity_id || !model.value.vehicle_body_type) {
        return
    }
    let ncl = true
    let nca = true
    if (model.value.client_id) {
        try {
            const client = await clientStore.getClient(model.value.client_id)
            ncl = !applyDefaultPrice(client, 'CLIENT', true)
            nca = !applyDefaultPrice(client, 'CARRIER', true)
        } catch {
            message.error('Не удалось получить прайс клиента')
        }
    }
    if (!ncl && !nca) {
        await orderCalculate(false)
        return
    }
    await fetchDefaultPricesOptions()
    if (defaultPricesOptions.value.length === 0) {
        return
    }
    let p = defaultPricesOptions.value.find(item => item.is_default)
    if (!p) {
       p = defaultPricesOptions.value[0]
    }
    if (ncl) {
        applyDefaultPrice(p, 'CLIENT', true)
    }
    if (nca) {
        applyDefaultPrice(p, 'CARRIER', true)
    }
    await orderCalculate(false)
}

const orderCalculation = ref({
    client: {sum: 0, expenses: 0, discount: 0, service: 0, total: 0, calculated: true},
    carrier: {sum: 0, expenses: 0, fine: 0, total: 0, calculated: true}
})

const orderCalculate = debounce({delay: 500}, async (drill = false) => {
    try {
        const { data } = await axios.post('/api/calculate', model.value)
        orderCalculation.value = data
        if(!drill) {
            model.value.client_sum = orderCalculation.value.client.total
            model.value.carrier_sum = orderCalculation.value.carrier.total
        }
    } catch {
        message.error('Ошибка при подсчете цены заказа')
    }
})

const tConditionOptions = ref([])
const fetchTConditionOptions = async () => {
    tConditionOptions.value = await suggest.getTConditions()
}

const clientList = ref([])
const carrierList = ref([])

const handleClientSearch = debounce({delay: 500}, async q => {
    clientList.value = await suggest.searchClient(q)
})
const handleClientSearchFocus = async () => {
    if (!model.value.client_id) {
        clientList.value = await suggest.searchClient('')
    }
}

const clientOptions = computed(() => {
    let res = [...clientList.value]
    if (res.findIndex((el) => el.value === model.value.client_id) < 0 && model.value.client && model.value.client_id) {
        res = [{
            value: model.value.client.id,
            label: model.value.client.name_short,
            inn: model.value.client.inn,
            vat: model.value.client.vat,
        }, ...res]
    }
    return res
})

const handleCarrierSearch = debounce({delay: 500}, async q => {
    carrierList.value = await suggest.searchCarrier(q)
})
const handleCarrierSearchFocus = async () => {
    if (!model.value.carrier_id) {
        carrierList.value = await suggest.searchCarrier('')
    }
}

const carrierOptions = computed(() => {
    let res = [...carrierList.value]
    if (res.findIndex((el) => el.value === model.value.carrier_id) < 0 && model.value.carrier && model.value.carrier_id) {
        res = [{
            value: model.value.carrier.id,
            label: model.value.carrier.name_short,
            inn: model.value.carrier.inn,
            vat: model.value.carrier.vat,
        }, ...res]
    }
    return res
})

const driversList = ref([])
const fetchDriversByCarrier = async () => {
    if (!model.value.carrier_id) {
        driversList.value = []
        return
    }
    driversList.value = await suggest.getDriversByCarrier(model.value.carrier_id)
}

const driversOptions = computed(() => {
    let res = [...driversList.value]
    if (res.findIndex((el) => el.value === model.value.carrier_driver_id) < 0 && model.value.carrier_driver && model.value.carrier_driver_id) {
        const name = trim(trim(model.value.carrier_driver.surname).concat(' ', trim(model.value.carrier_driver.name), ' ', trim(model.value.carrier_driver.patronymic)))
        const label = !!model.value.carrier_driver.phone ? model.value.carrier_driver.phone : (!!model.value.carrier_driver.email ? model.value.carrier_driver.email : '')
        res = [{
            value: model.value.carrier_driver.id,
            inn: model.value.carrier_driver.inn,
            name,
            label: label !== '' ? `${name} (${label})` : name,
            phone: model.value.carrier_driver.phone,
            disabled: !model.value.carrier_driver.is_active,
        },...res]
    }
    return res
})


const handleCarrierChange = async (e) => {
    const selectedCarrier = carrierOptions.value.find((el) => el.value === e)
    model.value.carrier = selectedCarrier
    model.value.carrier_vat = selectedCarrier.vat
    await fetchDriversByCarrier()
    await fetchCarsByCarrier()
    model.value.carrier_driver_id = undefined
    model.value.carrier_car_id = undefined
    model.value.carrier_trailer_id = undefined
}

const handleDriverChange = async e => {
    const lastCar = await suggest.getLastOrderDriverCar(e, model.value.car_capacity_id)
    if (lastCar) {
        model.value.carrier_car_id = lastCar.carrier_car_id
        model.value.carrier_trailer_id = lastCar.carrier_trailer_id
    }
}

const handleCarChange = () => {
    model.value.carrier_trailer_id = undefined
    trailerList.value = []
}


const handleClientChange = async (e) => {
    const selectedClient = clientOptions.value.find((el) => el.value === e)
    model.value.client_vat = selectedClient.vat
    await handlePRefresh()
}

const carTypes = {
    'TRUCK': 'Грузовик',
    'TRACTOR': 'Тягач',
    'TRAILER': 'Прицеп',
}
const carsList = ref([])
const trailerList = ref([])

const fetchCarsByCarrier = async () => {
    if (!model.value.carrier_id) {
        carsList.value = []
        return
    }
    const cars = await suggest.getCarsByCarrier(model.value.carrier_id)
    carsList.value = cars.filter(car => car.type !== 'TRAILER').map(car => ({ ...car, typeLabel: carsList[car.type] }))
}

const carsOptions = computed(() => {
    let res = [...carsList.value]
    if (res.findIndex((el) => el.value === model.value.carrier_car_id) < 0 && model.value.carrier_car && model.value.carrier_car_id) {
        res = [{
            value: model.value.carrier_car.id,
            label: `${model.value.carrier_car.name} – ${model.value.carrier_car.plate_number}`,
            ...model.value.carrier_car,
        }, ...res]
    }
    const currentCap = carCapacitiesOptions.value.find((el) => el.id === model.value.car_capacity_id)
    return res.filter(
        car => car.type === 'TRACTOR'
            || !currentCap
            || (!!currentCap
                && (parseFloat(car.tonnage) >= parseFloat(currentCap.tonnage))
                && (!model.value.cargo_in_pallets || parseFloat(currentCap.pallets_count) <= parseFloat(car.pallets_count))
            )
            || car.id === model.value.carrier_car_id
    )
})

const fetchTrailersByCarrier = async () => {
    if (!model.value.carrier_id) {
        trailerList.value = []
        return
    }
    const cars = await suggest.getCarsByCarrier(model.value.carrier_id)
    trailerList.value = cars.filter(car => car.type === 'TRAILER').map(car => ({ ...car, typeLabel: trailerList[car.type] }))
}

const trailerOptions = computed(() => {
    let res = [...trailerList.value]
    if (res.findIndex((el) => el.value === model.value.carrier_trailer_id) < 0 && model.value.carrier_trailer && model.value.carrier_trailer_id) {
        res = [{
            value: model.value.carrier_trailer.id,
            label: `${model.value.carrier_trailer.name} – ${model.value.carrier_trailer.plate_number}`,
            ...model.value.carrier_trailer,
        }, ...res]
    }

    const currentCap = carCapacitiesOptions.value.find((el) => el.id === model.value.car_capacity_id)
    return res.filter(
        car => !currentCap
            || (!!currentCap
                && (parseFloat(car.tonnage) >= parseFloat(currentCap.tonnage))
                && (!model.value.cargo_in_pallets || parseFloat(currentCap.pallets_count) <= parseFloat(car.pallets_count))
            )
            || car.id === model.value.carrier_car_id
    )
})

const currentCarIsTractor = computed(() => {
    let res = false
    carsOptions.value.forEach((car) => {
        if (car.value === model.value.carrier_car_id && car.type === 'TRACTOR') {
            res = true
        }
    })
    return res
})

const setOrderStatus = async (orderId, statusType, status) => {
    try {
        isStatusLoading.value = true
        const res = await ordersStore.setOrderStatus(orderId, statusType, status)
        if (res.type === 'MANAGER') {
            model.value.status_manager = res
        }
        if (res.type === 'LOGIST') {
            model.value.status_logist = res
        }
        await ordersStore.refreshDataList()
    } catch {
        message.error("Не удалось устаноывить статус заказа")
    } finally {
        isStatusLoading.value = false
    }
}

const clientPriceEditing = ref(false)
const carrierPriceEditing = ref(false)

const acceptCustomClientPrice = async () => {
    model.value.client_sum_calculated = false
    await orderCalculate(false)
    clientPriceEditing.value = false
}

const resetCustomClientPrice = async () => {
    model.value.client_sum_calculated = true
    await orderCalculate(false)
    clientPriceEditing.value = false
}

const acceptCustomCarrierPrice = async () => {
    model.value.carrier_sum_calculated = false
    await orderCalculate(false)
    carrierPriceEditing.value = false
}

const resetCustomCarrierPrice = async () => {
    model.value.carrier_sum_calculated = true
    await orderCalculate(false)
    carrierPriceEditing.value = false
}

const handleTempChange = () => {
    if (!model.value.vehicle_body_type) {
        model.value.vehicle_body_type = 'Рефрежератор'
    }
}

const getTotal = arr => {
    let total = 0
    if (isArray(arr)) {
        arr.forEach(v => total += parseFloat(v.v))
    }
    return total
}

const clientExpensesTotal = computed(() => {
    return getTotal(model.value.client_expenses)
})

const clientDiscountsTotal = computed(() => {
    return getTotal(model.value.client_discounts)
})

const additionalServiceTotal = computed(() => {
    return getTotal(model.value.additional_service)
})

const carrierExpensesTotal = computed(() => {
    return getTotal(model.value.carrier_expenses)
})

const carrierFinesTotal = computed(() => {
    return getTotal(model.value.carrier_fines)
})

const needMKADSync = ref(false)

const handleMKADrateBlur = () => {
    needMKADSync.value = !(model.value.client_tariff_mkad_rate > 0 && model.value.carrier_tariff_mkad_rate > 0)
}

const syncMKADRate = (v) => {
    if (needMKADSync.value) {
        model.value.client_tariff_mkad_rate = v
        model.value.carrier_tariff_mkad_rate = v
    }
}

watch(() => prop.loading, async (v) => {
    needMKADSync.value = !(model.value.client_tariff_mkad_rate > 0 && model.value.carrier_tariff_mkad_rate > 0)
    if (!v) {
        await orderCalculate(true)
    }
})

</script>

<template>
<div :style="{
    backgroundColor: '#f5f5f4',
    padding: '10px 20px',
    borderRadius: '6px'
}">
    <a-row>
        <a-col :span="12" style="color: #737373">
            <div>
                <div>
                    <a-dropdown trigger="click">
                        <div style="color: #27272a; font-size: 16px; font-weight: 500; margin-bottom: 8px; width: fit-content; cursor: pointer">
                            <template v-if="model.status_manager">
                                <a-tooltip>
                                    <div style="display: flex; align-items: center; gap: 8px">
                                        <div
                                            v-if="model.status_manager"
                                            :style="{backgroundColor: managerOrderStatuses[model.status_manager.status].color}"
                                            style="width: 12px; height: 12px; border-radius: 8px"
                                        />
                                        {{ managerOrderStatuses[model.status_manager.status].label }}
                                        <a-button v-if="isStatusLoading" type="text" shape="circle" loading/>
                                    </div>

                                    <template #title>
                                        <UserOutlined />&nbsp;&nbsp;{{ model.status_manager.user }}
                                    </template>
                                </a-tooltip>
                            </template>
                            <template v-else>–</template>
                        </div>
                        <template v-if="authStore.userCan('ORDER_MANAGER_STATUS_CHANGE') && model.status_manager" #overlay>
                            <a-menu>
                                <template v-for="(v, key) in managerOrderStatuses">
                                    <a-menu-item v-if="key !== model.status_manager.status" @click="() => setOrderStatus(model.id, 'MANAGER', key)">
                                        <div style="display: flex; flex-direction: row; align-items: center">
                                            <div :style="{
                                            width: '12px',
                                            height: '12px',
                                            backgroundColor: v.color,
                                            borderRadius: '8px'
                                            }"></div>
                                            <div style="padding-left: 8px">{{ v.label }}</div>
                                        </div>
                                    </a-menu-item>
                                </template>
                            </a-menu>
                        </template>
                    </a-dropdown>
                </div>
                К оплате заказчику<template v-if="orderCalculation.order && orderCalculation.order.client_hours"> за {{orderCalculation.order.client_hours}} {{decl(orderCalculation.order.client_hours, ['час', 'часа', 'часов'])}}</template>:
                <div :style="{
                    fontSize: '24px',
                    fontWeight: '600',
                    color: '#27272a'
                }">
                    <div v-if="!clientPriceEditing" style="display: flex">
                        <a-dropdown placement="bottom" :arrow="!prop.readOnly && authStore.userCan('ORDER_CUSTOM_CLIENT_PRICE')">
                            <a class="ant-dropdown-link" @click.prevent>
                                <div style="display: flex;">
                                    {{ model.client_sum ? parseFloat(model.client_sum).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) : '–' }}
                                </div>
                            </a>
                            <template v-if="!prop.readOnly && authStore.userCan('ORDER_CUSTOM_CLIENT_PRICE')" #overlay>
                                <a-menu>
                                    <a-menu-item :icon="h(EditOutlined)" @click="() => clientPriceEditing = true">
                                        Изменить сумму
                                    </a-menu-item>
                                    <a-menu-divider />
                                    <a-menu-item :icon="h(ReloadOutlined)" @click="resetCustomClientPrice">
                                        Посчитать автоматически
                                    </a-menu-item>
                                </a-menu>
                            </template>
                        </a-dropdown>
                        <div v-if="!orderCalculation.client.calculated" style="vertical-align: super; font-size: 14px; margin-left: 8px; color: #575757; font-weight: 400; text-decoration: line-through">
                            {{ (orderCalculation.client.sum + orderCalculation.client.expenses + orderCalculation.client.discount + orderCalculation.client.service).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
                        </div>
                    </div>
                    <div v-else style="display: flex; align-items: center">
                        <a-input-number
                            v-model:value="model.client_sum"
                            :min="0"
                            style="width: 200px"
                        >
                            <template #addonAfter>₽</template>
                        </a-input-number>
                        <a-button shape="circle" size="large" type="ghost" :icon="h(CheckCircleTwoTone)" @click.prevent="acceptCustomClientPrice"/>
                        <a-button shape="circle" size="large" type="ghost" :icon="h(CloseCircleTwoTone)" @click.prevent="resetCustomClientPrice"/>
                    </div>
                    <div :style="{
                        fontWeight: '400',
                        fontSize: '11px',
                        color: '#404040'
                    }">
                        Допрасходы: {{clientExpensesTotal.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'})}}
                        <a-divider type="vertical" />
                        Допуслуги: {{additionalServiceTotal.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'})}}
                        <a-divider type="vertical" />
                        Скидка: {{clientDiscountsTotal.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'})}}
                    </div>
                </div>
            </div>


        </a-col>
        <a-col :span="12" style="color: #737373">
            <div>
                <a-dropdown trigger="click">
                    <div style="color: #27272a; font-size: 16px; font-weight: 500; margin-bottom: 8px; width: fit-content; cursor: pointer">
                        <template v-if="model.status_logist">
                            <a-tooltip>
                                <div style="display: flex; align-items: center; gap: 8px">
                                    <div
                                        v-if="model.status_logist"
                                        :style="{backgroundColor: logistOrderStatuses[model.status_logist.status].color}"
                                        style="width: 12px; height: 12px; border-radius: 8px"
                                    />
                                    {{ logistOrderStatuses[model.status_logist.status].label }}
                                    <a-button v-if="isStatusLoading" type="text" shape="circle" loading/>
                                </div>

                                <template #title>
                                    <UserOutlined />&nbsp;&nbsp;{{ model.status_logist.user }}
                                </template>
                            </a-tooltip>
                        </template>
                        <template v-else>–</template>
                    </div>
                    <template v-if="authStore.userCan('ORDER_CARRIER_STATUS_CHANGE') && model.status_logist" #overlay>
                        <a-menu>
                            <template v-for="(v, key) in logistOrderStatuses">
                                <a-menu-item v-if="key !== model.status_logist.status" @click="() => setOrderStatus(model.id, 'LOGIST', key)">
                                    <div style="display: flex; flex-direction: row; align-items: center">
                                        <div :style="{
                                            width: '12px',
                                            height: '12px',
                                            backgroundColor: v.color,
                                            borderRadius: '8px'
                                            }"></div>
                                        <div style="padding-left: 8px">{{ v.label }}</div>
                                    </div>
                                </a-menu-item>
                            </template>
                        </a-menu>
                    </template>
                </a-dropdown>
            </div>
            К оплате перевозчику<template v-if="orderCalculation.order && orderCalculation.order.carrier_hours"> за {{orderCalculation.order.carrier_hours}} {{decl(orderCalculation.order.carrier_hours, ['час', 'часа', 'часов'])}}</template>:
            <div :style="{
                fontSize: '24px',
                fontWeight: '600',
                color: '#27272a'
            }">
                <div v-if="!carrierPriceEditing" style="display: flex">
                    <a-dropdown placement="bottom" :arrow="!prop.readOnly && authStore.userCan('ORDER_CUSTOM_CARRIER_PRICE')">
                        <a class="ant-dropdown-link" @click.prevent>
                            {{ model.carrier_sum ? parseFloat(model.carrier_sum).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) : '–' }}
                        </a>
                        <template v-if="!prop.readOnly && authStore.userCan('ORDER_CUSTOM_CARRIER_PRICE')" #overlay>
                            <a-menu>
                                <a-menu-item :icon="h(EditOutlined)" @click="() => carrierPriceEditing = true">
                                    Изменить сумму
                                </a-menu-item>
                                <a-menu-divider />
                                <a-menu-item :icon="h(ReloadOutlined)" @click="resetCustomCarrierPrice">
                                    Посчитать автоматически
                                </a-menu-item>
                            </a-menu>
                        </template>
                    </a-dropdown>
                    <div v-if="!orderCalculation.carrier.calculated" style="vertical-align: super; font-size: 14px; margin-left: 8px; color: #575757; font-weight: 400; text-decoration: line-through">
                        {{ (orderCalculation.carrier.sum + orderCalculation.carrier.expenses - orderCalculation.carrier.fine).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
                    </div>
                </div>
                <div v-else style="display: flex; align-items: center">
                    <a-input-number
                        v-model:value="model.carrier_sum"
                        :min="0"
                        style="width: 200px"
                    >
                        <template #addonAfter>₽</template>
                    </a-input-number>
                    <a-button shape="circle" size="large" type="ghost" :icon="h(CheckCircleTwoTone)" @click.prevent="acceptCustomCarrierPrice"/>
                    <a-button shape="circle" size="large" type="ghost" :icon="h(CloseCircleTwoTone)" @click.prevent="resetCustomCarrierPrice"/>
                </div>
                <div :style="{
                            fontWeight: '400',
                            fontSize: '11px',
                            color: '#404040'
                        }">
                    Допрасходы: {{carrierExpensesTotal.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'})}}
                    <a-divider type="vertical" />
                    Штрафы: {{carrierFinesTotal.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'})}}
                </div>
            </div>
        </a-col>
    </a-row>
</div>
<a-form layout="vertical" :model="model" :disabled="readOnly">
    <a-row :gutter="16">
        <a-col :span="12">
            <a-divider orientation="left">Груз</a-divider>
            <a-form-item label="Наименование груза" name="cargo_name">
                <a-auto-complete
                    v-model:value="model.cargo_name"
                    :options="cargoNameOptions"
                    placeholder="Введите наименование груза"
                    @search="handleCargoNameSearch"
                ></a-auto-complete>
            </a-form-item>
            <a-space>
                <a-form-item>
                    <template #label>
                        <div style="display: flex; justify-content: space-between; gap: 8px">
                            <div>Вес груза</div>
                            <a-segmented
                                :style="{ marginTop: '-1px' }"
                                v-model:value="weightSegmentsValue"
                                :options="weightSegments"
                                size="small"
                                @change="handleWeightTypeChange"
                            />
                        </div>
                    </template>
                    <a-input-number
                        v-model:value="cargoWeight"
                        @change="handleCargoWeightChange"
                        placeholder="Вес"
                        :min="0"
                        style="width: 100%"
                    >
                        <template #addonAfter>{{ weightSegmentsValue }}.</template>
                    </a-input-number>
                </a-form-item>
                <a-form-item label="Температурный режим">
                    <a-select
                        v-model:value="model.cargo_temp"
                        placeholder="Режим"
                        style="width: 100%"
                        :options="tConditionOptions"
                        @focus="fetchTConditionOptions"
                        :loading = "suggest.isLoading"
                        @change="handleTempChange"
                    />
                </a-form-item>
            </a-space>
            <a-space>
                <a-form-item label="Паллеты" style="width: 172px">
                    <a-checkbox v-model:checked="model.cargo_in_pallets" @change="handlePalletsChange">Груз на паллетах</a-checkbox>
                </a-form-item>
                <a-form-item v-if="!!model.cargo_in_pallets" label="Количество палет">
                    <a-input-number
                        v-model:value="model.cargo_pallets_count"
                        placeholder="Количество палет"
                        @change="handlePalletsChange"
                        style="width: 100%"
                        :min="0"
                    />
                </a-form-item>
            </a-space>
        </a-col>
        <a-col :span="12">
            <a-divider orientation="left">Машина</a-divider>
            <a-form-item label="Вместимость машины" name="type" :validate-status="capacitySearchError ? 'error': undefined" :help="capacitySearchError ? 'Невозможно подобрать вместимость машины' : undefined">
                <a-select
                    v-model:value="model.car_capacity_id"
                    placeholder="Вместимость"
                    :options="carCapacitiesOptions"
                    @focus="fetchCarCapacities"
                    :loading = "suggest.isLoading"
                    @change="handlePRefresh"
                />
            </a-form-item>
            <a-form-item label="Тип кузова">
                <a-select
                    v-model:value="model.vehicle_body_type"
                    placeholder="Кузов"
                    :options="carBodyTypesOptions"
                    @focus="fetchBodyTypesOptions"
                    :loading="suggest.isLoading"
                    @change="handlePRefresh"
                />
            </a-form-item>
            <a-form-item label="Загрузка">
                <a-checkbox v-model:checked="model.vehicle_loading_rear">Задняя</a-checkbox>
                <a-checkbox v-model:checked="model.vehicle_loading_lateral">Боковая</a-checkbox>
                <a-checkbox v-model:checked="model.vehicle_loading_upper">Верхняя</a-checkbox>
            </a-form-item>
        </a-col>
    </a-row>
    <a-divider :dashed="true" style="margin: 0"/>
    <a-row :gutter="16">
        <a-col :span="12">
            <a-tabs v-model:activeKey="currentClientTab">
                <a-tab-pane key="client" tab="Заказчик">
                    <a-space direction="vertical" :style="{ width: '100%' }">
                        <a-select
                            show-search
                            v-model:value="model.client_id"
                            placeholder="Выберите заказчика"
                            :style="{ width: '100%' }"
                            :filter-option="false"
                            :not-found-content="suggest.isLoading ? undefined : null"
                            @search="handleClientSearch"
                            @focus="handleClientSearchFocus"
                            @change="handleClientChange"
                            :options="clientOptions"
                        >
                            <template #option="{ label, inn }">
                                <div style="display: flex; justify-content: space-between; align-items: center">
                                    <div>{{ label }}</div>
                                    <div style="font-size: 11px; font-weight: 500">
                                        {{ inn }}
                                    </div>
                                </div>
                            </template>
                            <template v-if="suggest.isLoading" #notFoundContent>
                                <div style="display: flex; justify-content: center; align-items: center; min-height: 100px">
                                    <a-spin size="small" />
                                </div>
                            </template>
                        </a-select>
                        <a-select
                            v-model:value="model.client_vat"
                            placeholder="Выбор НДС"
                            :style="{ width: '100%' }"
                        >
                            <a-select-option :value="0">Без НДС</a-select-option>
                            <a-select-option :value="1">НДС</a-select-option>
                            <a-select-option :value="2">Наличные</a-select-option>
                        </a-select>
                    </a-space>
                </a-tab-pane>
                <a-tab-pane key="price" tab="Тариф">
                    <a-space direction="vertical" style="width: 100%">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 100px; text-align: right"></div>
                            <div style="flex-grow: 1">
                                <a-dropdown @open-change="handlePriceLoadingOpen">
                                    <a-button style="width: 100%">
                                        Загрузить прайс...
                                    </a-button>
                                    <template #overlay>
                                        <a-menu @click="() => {}">
                                            <a-menu-item v-if="!model.car_capacity_id || !model.vehicle_body_type" disabled>Заполните параметры машины</a-menu-item>
                                            <template v-else>
                                                <a-menu-item
                                                    key="cp"
                                                    v-if="!!model.client_id"
                                                    @click="()=>applyClientPrice('CLIENT')"
                                                >
                                                    Прайс заказчика
                                                </a-menu-item>
                                                <a-menu-divider v-if="defaultPricesOptions.length > 0 && !!model.client_id" />
                                                <a-menu-item
                                                    v-for="defaultPrice in defaultPricesOptions"
                                                    :key="defaultPrice.id"
                                                    @click="async () => {applyDefaultPrice(defaultPrice, 'CLIENT'); await orderCalculate(false)}"
                                                >
                                                    {{defaultPrice.name}}
                                                </a-menu-item>
                                            </template>
                                        </a-menu>
                                    </template>
                                </a-dropdown>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 100px; text-align: right">Ставка:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.client_tariff_hourly"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Ставка"
                                    @change="() => orderCalculate(false)"
                                >
                                    <template #addonAfter>
                                        <div style="width: 45px">₽ / час</div>
                                    </template>
                                </a-input-number>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px">
                            <div style="width: 100px; text-align: right">Минимум:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.client_tariff_min_hours"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Минимум часов"
                                    @change="() => orderCalculate(false)"
                                >
                                    <template #addonAfter>
                                        <div style="width: 45px">час.</div>
                                    </template>
                                </a-input-number>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px">
                            <div style="width: 100px; text-align: right">На подачу:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.client_tariff_hours_for_coming"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Часов на подачу"
                                    @change="() => orderCalculate(false)"
                                >
                                    <template #addonAfter>
                                        <div style="width: 45px">час.</div>
                                    </template>
                                </a-input-number>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px">
                            <div style="width: 100px; text-align: right">Тариф МКАД:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.client_tariff_mkad_price"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Тариф поездки за МКАД"
                                    @change="() => orderCalculate(false)"
                                >
                                    <template #addonAfter>
                                        <div style="width: 45px">₽ / км.</div>
                                    </template>
                                </a-input-number>
                            </div>
                        </div>
                        <a-divider dashed style="margin: 0;" />
                        <div style="display: flex; align-items: center; gap: 10px">
                            <div style="width: 100px; text-align: right">За МКАД:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.client_tariff_mkad_rate"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Поездка за МКАД"
                                    @change="(e) => { syncMKADRate(e); orderCalculate(false) }"
                                    @blur="handleMKADrateBlur"
                                >
                                    <template #addonAfter>
                                        <div style="width: 45px">км.</div>
                                    </template>
                                </a-input-number>
                            </div>
                        </div>
                    </a-space>
                </a-tab-pane>
                <a-tab-pane key="expenses" tab="Допрасходы">
                    <KeyValueTable
                        v-model="model.client_expenses"
                        :scroll="{y: 150}"
                        header-key-text="Наименование"
                        header-value-text="Cумма"
                        :value-width="150"
                        add-button-text="Добавить расход"
                        key-placeholder-text="Расход"
                        value-placeholder-text="Сумма"
                        value-postfix-text="₽"
                        :suggests="suggest.expensesSuggest"
                        :read-only="prop.readOnly"
                        @update="() => orderCalculate(false)"
                        @add="(el) => isArray(model.carrier_expenses) ? model.carrier_expenses.unshift(el) : model.carrier_expenses = [el]"
                    />
                </a-tab-pane>
                <a-tab-pane key="discount" tab="Скидки">
                    <KeyValueTable
                        v-model="model.client_discounts"
                        :scroll="{y: 150}"
                        header-key-text="Скидка"
                        header-value-text="Cумма"
                        :value-width="150"
                        add-button-text="Добавить скидку"
                        key-placeholder-text="Скидка"
                        value-placeholder-text="Сумма"
                        value-postfix-text="₽"
                        :read-only="prop.readOnly"
                        @update="() => orderCalculate(false)"
                    />
                </a-tab-pane>
            </a-tabs>
        </a-col>
        <a-col :span="12">
            <a-tabs v-model:activeKey="currentCarrierTab">
                <a-tab-pane key="carrier" tab="Перевозчик">
                    <a-space direction="vertical" :style="{ width: '100%' }">
                        <a-select
                            show-search
                            v-model:value="model.carrier_id"
                            placeholder="Выберите перевозчика"
                            :style="{ width: '100%' }"
                            :filter-option="false"
                            :not-found-content="suggest.isLoading ? undefined : null"
                            @search="handleCarrierSearch"
                            @focus="handleCarrierSearchFocus"
                            @change="handleCarrierChange"
                            :options="carrierOptions"
                        >
                            <template #option="{ label, inn }">
                                <div style="display: flex; justify-content: space-between; align-items: center">
                                    <div>{{ label }}</div>
                                    <div style="font-size: 11px; font-weight: 500">
                                        {{ inn }}
                                    </div>
                                </div>
                            </template>
                            <template v-if="suggest.isLoading" #notFoundContent>
                                <div style="display: flex; justify-content: center; align-items: center; min-height: 100px">
                                    <a-spin size="small" />
                                </div>
                            </template>
                        </a-select>
                        <a-select
                            v-model:value="model.carrier_vat"
                            placeholder="Выбор НДС"
                            :style="{ width: '100%' }"
                        >
                            <a-select-option :value="0">Без НДС</a-select-option>
                            <a-select-option :value="1">НДС</a-select-option>
                            <a-select-option :value="2">Наличные</a-select-option>
                        </a-select>
                        <template v-if="!!model.carrier_id">
                            <a-divider dashed style="margin: 0; font-size: 11px" orientation="left" orientation-margin="0">Водитель{{model.carrier_driver && model.carrier_driver.inn ? ` - ИНН: ${model.carrier_driver.inn}` : '' }}</a-divider>
                            <a-select
                                v-model:value="model.carrier_driver_id"
                                placeholder="Выберите водителя"
                                :style="{ width: '100%' }"
                                :options="driversOptions"
                                @focus="fetchDriversByCarrier"
                                @change="handleDriverChange"
                            >
                                <template #option="{ name, phone, inn }">
                                    <div style="display: flex; justify-content: space-between; align-items: center">
                                        <div>{{ name }}</div>
                                        <div style="font-size: 11px; font-weight: 500">
                                            {{ phone }}
                                        </div>
                                    </div>
                                    <div v-if="inn" style="font-size: 11px; font-weight: 500">ИНН: {{inn}}</div>
                                </template>
                                <template v-if="suggest.isLoading" #notFoundContent>
                                    <div style="display: flex; justify-content: center; align-items: center; min-height: 100px">
                                        <a-spin size="small" />
                                    </div>
                                </template>
                            </a-select>
                            <a-divider dashed style="margin: 0; font-size: 11px" orientation="left" orientation-margin="0">Машина</a-divider>
                            <a-select
                                v-model:value="model.carrier_car_id"
                                placeholder="Выберите машину"
                                :style="{ width: '100%' }"
                                @focus="fetchCarsByCarrier"
                                :options="carsOptions"
                                @change="handleCarChange"
                            >
                                <template #option="car">
                                    <div style="display:flex; flex-direction: column">
                                        <div style="display: flex; justify-content: space-between; align-items: center">
                                            <div>{{ car.name }}</div>
                                            <div style="font-size: 11px; font-weight: 500">
                                                {{ car.plate_number }}
                                            </div>
                                        </div>
                                        <div style="font-size: 11px">
                                            {{ carTypes[car.type] }}
                                            <template v-if="!!car.body_type">
                                                <a-divider type="vertical" />{{ car.body_type }}
                                            </template>
                                            <template v-if="!!car.tonnage">
                                                <a-divider type="vertical" />{{ car.tonnage }} т
                                            </template>
                                            <template v-if="!!car.volume">
                                                <a-divider type="vertical" />{{ car.volume }} м<sup>3</sup>
                                            </template>
                                            <template v-if="!!car.pallets_count">
                                                <a-divider type="vertical" />{{ car.pallets_count }} п
                                            </template>
                                        </div>
                                    </div>
                                </template>
                                <template v-if="suggest.isLoading" #notFoundContent>
                                    <div style="display: flex; justify-content: center; align-items: center; min-height: 100px">
                                        <a-spin size="small" />
                                    </div>
                                </template>
                            </a-select>
                            <template v-if="currentCarIsTractor">
                                <a-divider dashed style="margin: 0; font-size: 11px" orientation="left" orientation-margin="0">Прицеп</a-divider>
                                <a-select
                                    v-model:value="model.carrier_trailer_id"
                                    placeholder="Выберите прицеп"
                                    :style="{ width: '100%' }"
                                    @focus="fetchTrailersByCarrier"
                                    :options="trailerOptions"
                                >
                                    <template #option="car">
                                        <div style="display:flex; flex-direction: column">
                                            <div style="display: flex; justify-content: space-between; align-items: center">
                                                <div>{{ car.name }}</div>
                                                <div style="font-size: 11px; font-weight: 500">
                                                    {{ car.plate_number }}
                                                </div>
                                            </div>
                                            <div style="font-size: 11px">
                                                {{ carTypes[car.type] }}
                                                <template v-if="!!car.body_type">
                                                    <a-divider type="vertical" />{{ car.body_type }}
                                                </template>
                                                <template v-if="!!car.tonnage">
                                                    <a-divider type="vertical" />{{ car.tonnage }} т
                                                </template>
                                                <template v-if="!!car.volume">
                                                    <a-divider type="vertical" />{{ car.volume }} м<sup>3</sup>
                                                </template>
                                                <template v-if="!!car.pallets_count">
                                                    <a-divider type="vertical" />{{ car.pallets_count }} п
                                                </template>
                                            </div>
                                        </div>
                                    </template>
                                    <template v-if="suggest.isLoading" #notFoundContent>
                                        <div style="display: flex; justify-content: center; align-items: center; min-height: 100px">
                                            <a-spin size="small" />
                                        </div>
                                    </template>
                                </a-select>
                            </template>
                            <a-divider dashed style="margin: 0; font-size: 11px" orientation="left" orientation-margin="0">Одометр загрузка / разгрузка</a-divider>
                            <a-row :gutter="8">
                                <a-col :span="12">
                                    <a-input
                                        v-model:value="model.carrier_odometer_start"
                                        type="number"
                                        placeholder="Одометр загрузка"
                                    />
                                </a-col>
                                <a-col :span="12">
                                    <a-input
                                        v-model:value="model.carrier_odometer_end"
                                        type="number"
                                        placeholder="Одометр разгрузка"
                                    />
                                </a-col>
                            </a-row>
                            <a-divider dashed style="margin: 0; font-size: 11px" orientation="left" orientation-margin="0">Время завершения заказа</a-divider>
                            <a-date-picker
                                v-model:value="model.ended_at"
                                format="DD.MM.YYYY HH:mm"
                                :show-time="{ defaultValue: dayjs('00:00', 'HH:mm') }"
                                style="width: 100%"
                                @change="() => orderCalculate(false)"
                            />
                        </template>
                    </a-space>
                </a-tab-pane>
                <a-tab-pane key="price" tab="Тариф">
                    <a-space direction="vertical" style="width: 100%">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 100px; text-align: right"></div>
                            <div style="flex-grow: 1">
                                <a-dropdown @open-change="handlePriceLoadingOpen">
                                    <a-button style="width: 100%">
                                        Загрузить прайс...
                                    </a-button>
                                    <template #overlay>
                                        <a-menu @click="() => {}">
                                            <a-menu-item v-if="!model.car_capacity_id || !model.vehicle_body_type" disabled>Заполните параметры машины</a-menu-item>
                                            <template v-else>
                                                <a-menu-item
                                                    key="cp"
                                                    v-if="!!model.client_id"
                                                    @click="()=>applyClientPrice('CARRIER')"
                                                >
                                                    Прайс заказчика
                                                </a-menu-item>
                                                <a-menu-divider v-if="defaultPricesOptions.length > 0 && !!model.client_id" />
                                                <a-menu-item
                                                    v-for="defaultPrice in defaultPricesOptions"
                                                    :key="defaultPrice.id"
                                                    @click="async () => {applyDefaultPrice(defaultPrice, 'CARRIER'); await orderCalculate(false)}"
                                                >
                                                    {{defaultPrice.name}}
                                                </a-menu-item>
                                            </template>
                                        </a-menu>
                                    </template>
                                </a-dropdown>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 100px; text-align: right">Ставка:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.carrier_tariff_hourly"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Ставка"
                                    @change="() => orderCalculate(false)"
                                >
                                    <template #addonAfter>
                                        <div style="width: 45px">₽ / час</div>
                                    </template>
                                </a-input-number>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px">
                            <div style="width: 100px; text-align: right">Минимум:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.carrier_tariff_min_hours"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Минимум часов"
                                    @change="() => orderCalculate(false)"
                                >
                                    <template #addonAfter><div style="width: 45px">час.</div></template>
                                </a-input-number>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px">
                            <div style="width: 100px; text-align: right">На подачу:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.carrier_tariff_hours_for_coming"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Часов на подачу"
                                    @change="() => orderCalculate(false)"
                                >
                                    <template #addonAfter>
                                        <div style="width: 45px">час.</div>
                                    </template>
                                </a-input-number>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 10px">
                            <div style="width: 100px; text-align: right">Тариф МКАД:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.carrier_tariff_mkad_price"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Тариф поездки за МКАД"
                                    @change="() => orderCalculate(false)"
                                >
                                    <template #addonAfter>
                                        <div style="width: 45px">₽ / км.</div>
                                    </template>
                                </a-input-number>
                            </div>
                        </div>
                        <a-divider dashed style="margin: 0;" />
                        <div style="display: flex; align-items: center; gap: 10px">
                            <div style="width: 100px; text-align: right">За МКАД:</div>
                            <div style="flex-grow: 1">
                                <a-input-number
                                    v-model:value="model.carrier_tariff_mkad_rate"
                                    :min="0"
                                    style="width: 100%"
                                    placeholder="Поездка за МКАД"
                                    @change="(e) => { syncMKADRate(e); orderCalculate(false) }"
                                    @blur="handleMKADrateBlur"
                                >
                                    <template #addonAfter>
                                        <div style="width: 45px">км.</div>
                                    </template>
                                </a-input-number>
                            </div>
                        </div>
                    </a-space>
                </a-tab-pane>
                <a-tab-pane key="expenses" tab="Допрасходы">
                    <KeyValueTable
                        v-model="model.carrier_expenses"
                        :scroll="{y: 150}"
                        header-key-text="Наименование"
                        header-value-text="Cумма"
                        :value-width="150"
                        add-button-text="Добавить расход"
                        key-placeholder-text="Расход"
                        value-placeholder-text="Сумма"
                        value-postfix-text="₽"
                        :suggests="suggest.expensesSuggest"
                        :read-only="prop.readOnly"
                        @update="() => orderCalculate(false)"
                        @add="(el) => isArray(model.client_expenses) ? model.client_expenses.unshift(el) : model.client_expense = [el]"
                    />
                </a-tab-pane>
                <a-tab-pane key="fines" tab="Штрафы">
                    <KeyValueTable
                        v-model="model.carrier_fines"
                        :scroll="{y: 150}"
                        header-key-text="Штраф"
                        header-value-text="Cумма"
                        :value-width="150"
                        add-button-text="Добавить штраф"
                        key-placeholder-text="Штраф"
                        value-placeholder-text="Сумма"
                        value-postfix-text="₽"
                        :read-only="prop.readOnly"
                        @update="() => orderCalculate(false)"
                        @add="()=>{}"
                    />
                </a-tab-pane>
            </a-tabs>
        </a-col>
    </a-row>
    <a-row :gutter="16" style="padding-top: 16px">
        <a-col :span="12">
            <AddressList
                v-model="model.from_locations"
                title="Откуда"
                add-button-text="Добавить адрес загрузки"
                @change="() => orderCalculate(false)"
                :client-id="model.client_id"
            />
        </a-col>
        <a-col :span="12">
            <AddressList
                v-model="model.to_locations"
                title="Куда"
                add-button-text="Добавить адрес разгрузки"
                @change="() => orderCalculate(false)"
                :client-id="model.client_id"
            />
        </a-col>
    </a-row>
    <a-divider orientation="left">Дополнительные услуги</a-divider>
    <SelectValueTableWithCnt
        v-model="model.additional_service"
        header-key-text="Услуга"
        header-count-text="Кол-во"
        header-value-text="Сумма"
        add-button-text="Добавить допуслугу"
        key-placeholder-text="Выберите допуслугу"
        value-placeholder-text="Сумма"
        value-postfix-text="₽"
        :select-fetcher="suggest.getAdditionalServices"
        @change="() => orderCalculate(false)"
        :read-only="prop.readOnly"
    />
</a-form>

</template>

<style>
input, select, textarea, .ant-select-selector {
    color: #1a202c !important;
}
</style>
