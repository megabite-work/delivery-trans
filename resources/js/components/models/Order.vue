<script setup>
import {ref, h, watch, computed, onMounted, reactive} from "vue";
import axios from "axios";
import {debounce, isArray, trim} from "radash";

import {message} from "ant-design-vue";
import {
    CheckCircleTwoTone,
    CloseCircleTwoTone,
    EditOutlined,
    ReloadOutlined,
    UserOutlined,
    DownloadOutlined,
    SelectOutlined,
    CalendarOutlined
} from '@ant-design/icons-vue';
import {useSuggests} from "../../stores/models/suggests.js";
import {usePricesStore} from "../../stores/models/prices.js";
import {useClientsStore} from "../../stores/models/clients.js";
import {managerOrderStatuses, logistOrderStatuses, decl} from "../../helpers/index.js";
import {useOrdersStore} from "../../stores/models/orders.js";
import {useAuthStore} from "../../stores/auth.js";

import KeyValueTable from "../KeyValueTable.vue";
import AddressList from "../AddressList.vue";
import dayjs from "dayjs";
import SelectAdditionalServicesTable from "../SelectAdditionalServicesTable.vue";
import Client from "./Client.vue";
import Drawer from "../Drawer.vue";
import {useCarriersStore} from "../../stores/models/carriers.js";
import Carrier from "./Carrier.vue";


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
const activeTab = ref("order")

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

const currStatusDate = ref(null)
const updateStatusDate = async (statusId, statusDate) => {
    try {
        const status = await ordersStore.setOrderStatusDate(statusId, statusDate)
        if (status.type === 'MANAGER') {
            model.value.status_manager = status
        }
        if (status.type === 'LOGIST') {
            model.value.status_logist = status
        }
        await ordersStore.refreshDataList()
        message.success('Дата статуса обновлена')
    } catch (e) {
        message.error('Не удалось обновить дату статуса')
    }
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
        model.value.carrier_tariff_additional_hour_price = p ? parseFloat(p.additional_hour_price) : 0
        model.value.carrier_tariff_additional_point_price = p ? parseFloat(p.additional_point_price) : 0
        model.value.carrier_tariff_loading_points = p ? parseInt(p.loading_points) : 0
        model.value.carrier_tariff_unloading_points = p ? parseInt(p.unloading_points) : 0
    } else if (type === 'CLIENT') {
        model.value.client_tariff_hourly = p ? parseFloat(p.hourly) : 0
        model.value.client_tariff_min_hours = p ? parseFloat(p.min_hours) : 0
        model.value.client_tariff_hours_for_coming = p ? parseFloat(p.hours_for_coming) : 0
        model.value.client_tariff_mkad_price = p ? parseFloat(p.mkad_price) : 0
        model.value.client_tariff_additional_hour_price = p ? parseFloat(p.additional_hour_price) : 0
        model.value.client_tariff_additional_point_price = p ? parseFloat(p.additional_point_price) : 0
        model.value.client_tariff_loading_points = p ? parseInt(p.loading_points) : 0
        model.value.client_tariff_unloading_points = p ? parseInt(p.unloading_points) : 0
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
    model.value.from_locations = []
    try {
        const fromLocation = await suggest.getLastOrderFromLocation(e)
        if (!!fromLocation.address) {
            model.value.from_locations = [{
                address: fromLocation.address
            }]
        }
    } catch (e) {}
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
    return res.filter(
        car => car.type === 'TRACTOR'
            || ((parseFloat(car.tonnage) >= parseFloat(model.value.cargo_weight) / 1000)
                && (!model.value.cargo_in_pallets || (parseFloat(model.value.cargo_pallets_count) || 0) <= (parseFloat(car.pallets_count) || 0))
                && ((car.loading_lateral && model.value.vehicle_loading_lateral)
                    || (car.loading_rear && model.value.vehicle_loading_rear)
                    || (car.loading_upper && model.value.vehicle_loading_upper))
            || car.id === model.value.carrier_car_id))
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
        model.value.vehicle_body_type = 'Рефрижератор'
    }
}

const getTotal = arr => {
    let total = 0
    if (isArray(arr)) {
        arr.forEach(v => total += parseFloat(v.v))
    }
    return total
}

const getVTotal = arr => {
    let total = 0
    if (isArray(arr)) {
        arr.forEach(v => total += parseFloat(v.v) * parseFloat(v.c))
    }
    return total
}

const getVPTotal = arr => {
    let total = 0
    if (isArray(arr)) {
        arr.forEach(v => total += parseFloat(v.vp) * parseFloat(v.c))
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
    return getVTotal(model.value.additional_service)
})

const additionalServiceCarrierTotal = computed(() => {
    return getVPTotal(model.value.additional_service)
})

const carrierExpensesTotal = computed(() => {
    return getTotal(model.value.carrier_expenses)
})

const carrierFinesTotal = computed(() => {
    return getTotal(model.value.carrier_fines)
})

const syncMKADRate = (v) => {
    model.value.client_tariff_mkad_rate = v
    model.value.carrier_tariff_mkad_rate = v
}

watch(() => prop.loading, async (v) => {
    if (!v) {
        await orderCalculate(true)
    }
})

const clientS = {
    client: "ORDER_CLIENT_SECTION",
    price: "ORDER_CLIENT_TARIFF_SECTION",
    expenses: "ORDER_CLIENT_EXPENSES_SECTION",
    discount: "ORDER_CLIENT_DISCOUNT_SECTION"
}

const carrierS = {
    carrier: "ORDER_CARRIER_SECTION",
    price: "ORDER_CARRIER_TARIFF_SECTION",
    expenses: "ORDER_CARRIER_EXPENSES_SECTION",
    fines: "ORDER_CARRIER_FINES_SECTION"
}

onMounted(() => {
    let s = true
    Object.keys(clientS).forEach(k => {
        if (s && authStore.userCan(clientS[k])) {
            currentClientTab.value = k
            s = false
        }
    })
    s = true
    Object.keys(carrierS).forEach(k => {
        if (s && authStore.userCan(carrierS[k])) {
            currentCarrierTab.value = k
            s = false
        }
    })
})

const clientsStore = useClientsStore()
const currentClient = reactive({ data:{ id: null }, modified: false })
const clientDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const openCurrentClient = async () => {
    clientDrawer.isOpen = true
    try {
        clientDrawer.isLoading = true
        currentClient.data = await clientsStore.getClient(model.value.client_id)
    } catch (e) {
        clientDrawer.isOpen = false
        message.error('Ошибка загрузки')
    } finally {
        clientDrawer.isLoading = false
    }
}

const closeClientDrawer = () => {
    if (clientDrawer.isSaving) {
        return
    }
    clientDrawer.isOpen = false
    currentClient.data = { id: null }
}

const saveClient = async () => {
    clientDrawer.isSaving = true
    try {
        if (authStore.userCan('CLIENTS_EDIT')) {
            currentClient.data = await clientsStore.storeClient(currentClient.data)
            currentClient.modified = false
            message.success('Карточка заказчика записана')
            clientDrawer.isSaving = false
            closeClientDrawer()
        }
    } catch (e) {
        message.error(`Ошибка. Не удалось сохранить карточку заказчика`)
    } finally {
        clientDrawer.isSaving = false
    }
}

const deleteClient = async () => {
    if (!authStore.userCan('CLIENTS_DELETE')) {
        return
    }
    try {
        await clientsStore.deleteClient(currentClient.data.id)
        model.value.client_id = undefined
        message.success('Заказчик успешно удален')
        closeClientDrawer()
    } catch (e) {
        message.error('Ошибка. Не удалось удалить заказчика')
    }
}

const carriersStore = useCarriersStore()
const currentCarrier = reactive({ data:{ id: null }, modified: false })
const carrierDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const openCurrentCarrier = async () => {
    currentCarrier.data = { id: null, is_active: true }
    currentCarrier.modified = false
    carrierDrawer.isOpen = true
    try {
        carrierDrawer.isLoading = true
        currentCarrier.data = await carriersStore.getCarrier(model.value.carrier_id)
    } catch (e) {
        carrierDrawer.isOpen = false
        message.error('Ошибка загрузки')
    } finally {
        carrierDrawer.isLoading = false
    }
}

const closeCarrierDrawer = () => {
    if (carrierDrawer.isSaving) {
        return
    }
    carrierDrawer.isOpen = false
    currentCarrier.data = { id: null, is_active: true }
}

const saveCarrier = async () => {
    carrierDrawer.isSaving = true
    try {
        if (authStore.userCan('CARRIERS_EDIT')) {
            currentCarrier.data = await carriersStore.storeCarrier(currentCarrier.data)
            currentCarrier.modified = false
            message.success('Карточка перевозчика записана')
            carrierDrawer.isSaving = false
            closeCarrierDrawer()
        }
    } catch (e) {
        message.error(`Ошибка. Не удалось сохранить карточку перевозчика`)
    } finally {
        carrierDrawer.isSaving = false
    }
}

const deleteCarrier = async () => {
    if (!authStore.userCan('CARRIERS_DELETE')) {
        return
    }
    try {
        await carriersStore.deleteCarrier(currentCarrier.data.id)
        message.success('Перевозчик успешно удален')
        closeCarrierDrawer()
    } catch (e) {
        message.error('Ошибка. Не удалось удалить перевозчика')
    }
}

const downloadForClient = () => {
    window.open(`/download/client-order/${model.value.id}`, '_blank')
}
const downloadForCarrier = () => {
    window.open(`/download/carrier-order/${model.value.id}`, '_blank')
}

</script>

<template>
<a-tabs v-model:activeKey="activeTab" style="margin-top: -20px">
    <a-tab-pane key="order" tab="Заявка">
        <div v-if="authStore.userCan('ORDER_CLIENT_PRICE') || authStore.userCan('ORDER_CARRIER_PRICE')" :style="{
            backgroundColor: '#f5f5f4',
            padding: '10px 20px',
            borderRadius: '6px'
        }">
            <a-row>
                <a-col :span="12" style="color: #737373">
                    <div>
                        <div style="display: flex; justify-content: space-between;">
                            <div v-if="authStore.userCan('ORDER_MANAGER_STATUS')">
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
                                                    <UserOutlined />&nbsp;&nbsp;{{ model.status_manager.user }}<br/>
                                                    <span style="font-size: 12px">{{ dayjs(model.status_manager.updated_at).format('DD.MM.YY HH:mm') }}</span>
                                                </template>
                                            </a-tooltip>
                                        </template>
                                        <template v-else>–</template>
                                    </div>
                                    <template v-if="authStore.userCan('ORDER_MANAGER_STATUS_CHANGE') && model.status_manager" #overlay>
                                        <a-menu>
                                            <a-menu-item @click="() => currStatusDate = dayjs(model.status_manager.updated_at)">
                                                <a-popconfirm @confirm="async () => await updateStatusDate(model.status_manager.id, currStatusDate)">
                                                    <template #title>Новая дата статуса</template>
                                                    <template #icon />
                                                    <template #description>
                                                        <a-date-picker
                                                            style="width: 200px"
                                                            v-model:value="currStatusDate"
                                                            :show-time="{ format: 'HH:mm' }"
                                                            format="DD-MM-YYYY HH:mm"
                                                            show-time
                                                        />
                                                    </template>
                                                    <CalendarOutlined />&nbsp;&nbsp;Сменить дату и время статуса
                                                </a-popconfirm>
                                            </a-menu-item>
                                            <a-menu-divider />
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
                            <div v-if="authStore.userCan('CLIENTS_ORDERS_DOWNLOAD') && !!model.id" style="padding-right: 20px; font-size: 16px">
                                <a-tooltip>
                                    <a-button :icon="h(DownloadOutlined)" size="middle" @click="downloadForClient">Скачать</a-button>
                                    <template #title>Скачать заявку для заказчика</template>
                                </a-tooltip>
                            </div>
                        </div>
                        <template v-if="authStore.userCan('ORDER_CLIENT_PRICE')">
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
                                        decimal-separator=","
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
                        </template>
                    </div>
                </a-col>
                <a-col :span="12" style="color: #737373">
                    <div style="display: flex; justify-content: space-between; align-items: center">
                        <div v-if="authStore.userCan('ORDER_CARRIER_STATUS')">
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
                                                <UserOutlined />&nbsp;&nbsp;{{ model.status_logist.user }}<br/>
                                                <span style="font-size: 12px">{{ dayjs(model.status_logist.created_at).format('DD.MM.YY HH:mm') }}</span>
                                            </template>
                                        </a-tooltip>
                                    </template>
                                    <template v-else>–</template>
                                </div>
                                <template v-if="authStore.userCan('ORDER_CARRIER_STATUS_CHANGE') && model.status_logist" #overlay>
                                    <a-menu>
                                        <a-menu-item @click="() => currStatusDate = dayjs(model.status_logist.updated_at)">
                                            <a-popconfirm @confirm="async () => await updateStatusDate(model.status_logist.id, currStatusDate)">
                                                <template #title>Новая дата статуса</template>
                                                <template #icon />
                                                <template #description>
                                                    <a-date-picker
                                                        style="width: 200px"
                                                        v-model:value="currStatusDate"
                                                        :show-time="{ format: 'HH:mm' }"
                                                        format="DD-MM-YYYY HH:mm"
                                                        show-time
                                                    />
                                                </template>
                                                <CalendarOutlined />&nbsp;&nbsp;Сменить дату и время статуса
                                            </a-popconfirm>
                                        </a-menu-item>
                                        <a-menu-divider />
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
                        <div v-if="authStore.userCan('CARRIERS_ORDERS_DOWNLOAD') && !!model.id" style="font-size: 16px">
                            <a-tooltip>
                                <a-button :icon="h(DownloadOutlined)" size="middle" @click="downloadForCarrier">Скачать</a-button>
                                <template #title>Скачать заявку для перевозчика</template>
                            </a-tooltip>
                        </div>
                    </div>
                    <template v-if="authStore.userCan('ORDER_CARRIER_PRICE')">
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
                                    decimal-separator=","
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
                                Допуслуги: {{additionalServiceCarrierTotal.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'})}}
                                <a-divider type="vertical" />
                                Штрафы: {{carrierFinesTotal.toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'})}}
                            </div>
                        </div>
                    </template>
                </a-col>
            </a-row>
        </div>
        <a-form layout="vertical" :model="model" :disabled="readOnly">
            <a-row :gutter="16">
                <a-col v-if="authStore.userCan('ORDER_CARGO_SECTION')" :span="12">
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
                                decimal-separator=","
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
                                decimal-separator=","
                                style="width: 100%"
                                :min="0"
                            />
                        </a-form-item>
                    </a-space>
                </a-col>
                <a-col v-if="authStore.userCan('ORDER_CAR_SECTION')" :span="12 + (authStore.userCan('ORDER_CARGO_SECTION') ? 0 : 12)">
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
                        <a-tab-pane v-if="authStore.userCan('ORDER_CLIENT_SECTION')" key="client" tab="Заказчик" style="margin-top: -16px">
                            <a-space direction="vertical" :style="{ width: '100%' }">
                                <a-input-group compact>
                                    <a-select
                                        show-search
                                        v-model:value="model.client_id"
                                        placeholder="Выберите заказчика"
                                        :filter-option="false"
                                        :not-found-content="suggest.isLoading ? undefined : null"
                                        @search="handleClientSearch"
                                        @focus="handleClientSearchFocus"
                                        @change="handleClientChange"
                                        :options="clientOptions"
                                        style="width: calc(100% - 32px)"
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
                                    <a-button :icon="h(SelectOutlined)" :disabled="!model.client_id" @click="openCurrentClient"/>
                                </a-input-group>
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
                        <a-tab-pane v-if="authStore.userCan('ORDER_CLIENT_TARIFF_SECTION')" key="price" tab="Тариф">
                            <a-space direction="vertical" style="width: 100%">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 150px; text-align: right"></div>
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
                                                            @click="async ()=>{applyClientPrice('CLIENT'); await orderCalculate(false)}"
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
                                    <div style="width: 150px; text-align: right">Ставка:</div>
                                    <div style="flex-grow: 1">
                                        <a-input-number
                                            v-model:value="model.client_tariff_hourly"
                                            :min="0"
                                            style="width: 100%"
                                            decimal-separator=","
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
                                    <div style="width: 150px; text-align: right">Минимум:</div>
                                    <div style="flex-grow: 1">
                                        <a-input-number
                                            v-model:value="model.client_tariff_min_hours"
                                            :min="0"
                                            decimal-separator=","
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
                                    <div style="width: 150px; text-align: right">На подачу:</div>
                                    <div style="flex-grow: 1">
                                        <a-input-number
                                            v-model:value="model.client_tariff_hours_for_coming"
                                            :min="0"
                                            style="width: 100%"
                                            decimal-separator=","
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
                                    <div style="width: 150px; text-align: right">Тариф МКАД:</div>
                                    <div style="flex-grow: 1">
                                        <a-input-number
                                            v-model:value="model.client_tariff_mkad_price"
                                            :min="0"
                                            style="width: 100%"
                                            decimal-separator=","
                                            placeholder="Тариф поездки за МКАД"
                                            @change="() => orderCalculate(false)"
                                        >
                                            <template #addonAfter>
                                                <div style="width: 45px">₽ / км.</div>
                                            </template>
                                        </a-input-number>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px">
                                    <div style="width: 150px; text-align: right">Стоимость доп.часа:</div>
                                    <div style="flex-grow: 1">
                                        <a-input-number
                                            v-model:value="model.client_tariff_additional_hour_price"
                                            :min="0"
                                            style="width: 100%"
                                            placeholder="Стоимость доп.часа"
                                            decimal-separator=","
                                            @change="() => orderCalculate(false)"
                                        >
                                            <template #addonAfter>
                                                <div style="width: 45px">₽</div>
                                            </template>
                                        </a-input-number>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px">
                                    <div style="width: 150px; text-align: right">Стоимость доп.точки:</div>
                                    <div style="flex-grow: 1">
                                        <a-input-number
                                            v-model:value="model.client_tariff_additional_point_price"
                                            :min="0"
                                            style="width: 100%"
                                            decimal-separator=","
                                            placeholder="Стоимость доп.точки"
                                            @change="() => orderCalculate(false)"
                                        >
                                            <template #addonAfter>
                                                <div style="width: 45px">₽</div>
                                            </template>
                                        </a-input-number>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px">
                                    <div style="width: 150px; text-align: right">Точек загрузки:</div>
                                    <div style="flex-grow: 1">
                                        <a-input-number
                                            v-model:value="model.client_tariff_loading_points"
                                            :min="0"
                                            decimal-separator=","
                                            style="width: 100%"
                                            placeholder="Включено точек загрузки"
                                            @change="() => orderCalculate(false)"
                                        >
                                        </a-input-number>
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px">
                                    <div style="width: 150px; text-align: right">Точек разгрузки:</div>
                                    <div style="flex-grow: 1">
                                        <a-input-number
                                            v-model:value="model.client_tariff_unloading_points"
                                            :min="0"
                                            style="width: 100%"
                                            decimal-separator=","
                                            placeholder="Включено точек разгрузки"
                                            @change="() => orderCalculate(false)"
                                        >
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
                                            decimal-separator=","
                                            placeholder="Поездка за МКАД"
                                            @change="(e) => { syncMKADRate(e); orderCalculate(false) }"
                                        >
                                            <template #addonAfter>
                                                <div style="width: 45px">км.</div>
                                            </template>
                                        </a-input-number>
                                    </div>
                                </div>
                            </a-space>
                        </a-tab-pane>
                        <a-tab-pane v-if="authStore.userCan('ORDER_CLIENT_EXPENSES_SECTION')" key="expenses" tab="Допрасходы">
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
                                @add="(el) => isArray(model.carrier_expenses) ? model.carrier_expenses.unshift({...el}) : model.carrier_expenses = [{...el}]"
                            />
                        </a-tab-pane>
                        <a-tab-pane v-if="authStore.userCan('ORDER_CLIENT_DISCOUNT_SECTION')" key="discount" tab="Скидки">
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
                        <a-tab-pane v-if="authStore.userCan('ORDER_CARRIER_SECTION')" key="carrier" tab="Перевозчик" style="margin-top: -16px">
                            <a-space direction="vertical" :style="{ width: '100%' }">
                                <a-input-group compact>
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
                                        style="width: calc(100% - 32px)"
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
                                    <a-button :icon="h(SelectOutlined)" :disabled="!model.carrier_id" @click="openCurrentCarrier"/>
                                </a-input-group>
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
                        <a-tab-pane v-if="authStore.userCan('ORDER_CARRIER_TARIFF_SECTION')" key="price" tab="Тариф">
                            <a-space direction="vertical" style="width: 100%">
                                <div v-if="(model.carrier && !model.carrier.is_resident) || !model.carrier" style="display: flex; align-items: center; gap: 10px;">
                                    <div style="width: 150px; text-align: right"></div>
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
                                                            @click="async ()=>{applyClientPrice('CARRIER'); await orderCalculate(false)}"
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
                                <a-alert v-if="model.carrier && model.carrier.is_resident" type="info">
                                    <template #message>
                                        Перевозчик - резидент. Применен тариф заказчика.
                                    </template>
                                </a-alert>
                                <template v-if="(model.carrier && !model.carrier.is_resident) || !model.carrier">
                                    <div style="display: flex; align-items: center; gap: 10px;">
                                        <div style="width: 150px; text-align: right">Ставка:</div>
                                        <div style="flex-grow: 1">
                                            <a-input-number
                                                v-model:value="model.carrier_tariff_hourly"
                                                :min="0"
                                                style="width: 100%"
                                                placeholder="Ставка"
                                                decimal-separator=","
                                                @change="() => orderCalculate(false)"
                                            >
                                                <template #addonAfter>
                                                    <div style="width: 45px">₽ / час</div>
                                                </template>
                                            </a-input-number>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px">
                                        <div style="width: 150px; text-align: right">Минимум:</div>
                                        <div style="flex-grow: 1">
                                            <a-input-number
                                                v-model:value="model.carrier_tariff_min_hours"
                                                :min="0"
                                                style="width: 100%"
                                                decimal-separator=","
                                                placeholder="Минимум часов"
                                                @change="() => orderCalculate(false)"
                                            >
                                                <template #addonAfter><div style="width: 45px">час.</div></template>
                                            </a-input-number>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px">
                                        <div style="width: 150px; text-align: right">На подачу:</div>
                                        <div style="flex-grow: 1">
                                            <a-input-number
                                                v-model:value="model.carrier_tariff_hours_for_coming"
                                                :min="0"
                                                style="width: 100%"
                                                decimal-separator=","
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
                                        <div style="width: 150px; text-align: right">Тариф МКАД:</div>
                                        <div style="flex-grow: 1">
                                            <a-input-number
                                                v-model:value="model.carrier_tariff_mkad_price"
                                                :min="0"
                                                style="width: 100%"
                                                decimal-separator=","
                                                placeholder="Тариф поездки за МКАД"
                                                @change="() => orderCalculate(false)"
                                            >
                                                <template #addonAfter>
                                                    <div style="width: 45px">₽ / км.</div>
                                                </template>
                                            </a-input-number>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px">
                                        <div style="width: 150px; text-align: right">Стоимость доп.часа:</div>
                                        <div style="flex-grow: 1">
                                            <a-input-number
                                                v-model:value="model.carrier_tariff_additional_hour_price"
                                                :min="0"
                                                decimal-separator=","
                                                style="width: 100%"
                                                placeholder="Стоимость доп.часа"
                                                @change="() => orderCalculate(false)"
                                            >
                                                <template #addonAfter>
                                                    <div style="width: 45px">₽</div>
                                                </template>
                                            </a-input-number>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px">
                                        <div style="width: 150px; text-align: right">Стоимость доп.точки:</div>
                                        <div style="flex-grow: 1">
                                            <a-input-number
                                                v-model:value="model.carrier_tariff_additional_point_price"
                                                :min="0"
                                                style="width: 100%"
                                                decimal-separator=","
                                                placeholder="Стоимость доп.точки"
                                                @change="() => orderCalculate(false)"
                                            >
                                                <template #addonAfter>
                                                    <div style="width: 45px">₽</div>
                                                </template>
                                            </a-input-number>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px">
                                        <div style="width: 150px; text-align: right">Точек загрузки:</div>
                                        <div style="flex-grow: 1">
                                            <a-input-number
                                                v-model:value="model.carrier_tariff_loading_points"
                                                :min="0"
                                                style="width: 100%"
                                                decimal-separator=","
                                                placeholder="Включено точек загрузки"
                                                @change="() => orderCalculate(false)"
                                            >
                                            </a-input-number>
                                        </div>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 10px">
                                        <div style="width: 150px; text-align: right">Точек разгрузки:</div>
                                        <div style="flex-grow: 1">
                                            <a-input-number
                                                v-model:value="model.carrier_tariff_unloading_points"
                                                :min="0"
                                                decimal-separator=","
                                                style="width: 100%"
                                                placeholder="Включено точек разгрузки"
                                                @change="() => orderCalculate(false)"
                                            >
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
                                                decimal-separator=","
                                                placeholder="Поездка за МКАД"
                                                @change="() => orderCalculate(false)"
                                            >
                                                <template #addonAfter>
                                                    <div style="width: 45px">км.</div>
                                                </template>
                                            </a-input-number>
                                        </div>
                                    </div>
                                </template>
                            </a-space>
                        </a-tab-pane>
                        <a-tab-pane v-if="authStore.userCan('ORDER_CARRIER_EXPENSES_SECTION')" key="expenses" tab="Допрасходы">
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
                                @add="(el) => isArray(model.client_expenses) ? model.client_expenses.unshift({...el}) : model.client_expense = [{...el}]"
                            />
                        </a-tab-pane>
                        <a-tab-pane v-if="authStore.userCan('ORDER_CARRIER_FINES_SECTION')" key="fines" tab="Штрафы">
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
            <a-row v-if="authStore.userCan('ORDER_LOCATION_FROM_SECTION') || authStore.userCan('ORDER_LOCATION_TO_SECTION')" :gutter="16" style="padding-top: 16px">
                <a-col v-if="authStore.userCan('ORDER_LOCATION_FROM_SECTION')" :span="12 + (authStore.userCan('ORDER_LOCATION_TO_SECTION') ? 0 : 12)">
                    <AddressList
                        v-model="model.from_locations"
                        title="Откуда"
                        add-button-text="Добавить адрес загрузки"
                        @change="() => orderCalculate(false)"
                        :client-id="model.client_id"
                    />
                </a-col>
                <a-col v-if="authStore.userCan('ORDER_LOCATION_TO_SECTION')" :span="12 + (authStore.userCan('ORDER_LOCATION_FROM_SECTION') ? 0 : 12)">
                    <AddressList
                        v-model="model.to_locations"
                        title="Куда"
                        add-button-text="Добавить адрес разгрузки"
                        @change="() => orderCalculate(false)"
                        :client-id="model.client_id"
                    />
                </a-col>
            </a-row>
            <template v-if="authStore.userCan('ORDER_ADDITIONAL_SERVICES')">
                <a-divider orientation="left">Дополнительные услуги</a-divider>
                <SelectAdditionalServicesTable
                    v-model="model.additional_service"
                    :cid="model.client_id"
                    value-postfix-text="₽"
                    :select-fetcher="suggest.getAdditionalServices"
                    @change="() => orderCalculate(false)"
                    :read-only="prop.readOnly"
                    :without-selected="false"
                />
            </template>
        </a-form>
    </a-tab-pane>
    <a-tab-pane key="statuses" tab="Статусы" :disabled="!model.id">
        <a-row>
            <a-col :span="12">
                <a-typography-title :level="4" style="padding-bottom: 16px">Менеджер</a-typography-title>
                <template v-if="isArray(model.statuses)">
                    <a-timeline>
                        <a-timeline-item v-for="status in model.statuses.filter((e) => e.type === 'MANAGER')" :color="managerOrderStatuses[status.status].color">
                            <p>{{ dayjs(status.updated_at).format("DD.MM.YYYY HH:mm") }} ({{ status.user }})</p>
                            <p>
                                <span style="font-weight: 600">
                                    {{ managerOrderStatuses[status.status].label }}
                                </span>
                            </p>
                        </a-timeline-item>
                    </a-timeline>
                </template>
            </a-col>
            <a-col :span="12">
                <a-typography-title :level="4" style="padding-bottom: 16px">Логист</a-typography-title>
                <template v-if="isArray(model.statuses)">
                    <a-timeline>
                        <a-timeline-item v-for="status in model.statuses.filter((e) => e.type === 'LOGIST')" :color="logistOrderStatuses[status.status].color">
                            <p>{{ dayjs(status.updated_at).format("DD.MM.YYYY HH:mm") }} ({{ status.user }})</p>
                            <p>
                                <span style="font-weight: 600">
                                    {{ logistOrderStatuses[status.status].label }}
                                </span>
                            </p>
                        </a-timeline-item>
                    </a-timeline>
                </template>
            </a-col>
        </a-row>
    </a-tab-pane>
</a-tabs>
<drawer
    v-model:open="clientDrawer.isOpen"
    @save="saveClient"
    @delete="deleteClient"
    @close="() => {clientDrawer.isOpen = false}"
    :width="800"
    :loading="clientDrawer.isLoading"
    :saving="clientDrawer.isSaving"
    :ok-loading="clientDrawer.isSaving"
    :title="`${currentClient.data.id === null ? 'Новый заказчик' : `Заказчик #${currentClient.data.id}`}${currentClient.modified ? '*' : ''}`"
    :ok-text="currentClient.data.id === null ? 'Сохранить' : 'Сохранить и закрыть'"
    :need-ok="authStore.userCan('CLIENTS_ADD') || authStore.userCan('CLIENTS_EDIT')"
    :need-delete="authStore.userCan('CLIENTS_DELETE') && currentClient.data.id !== null"
    need-deletion-confirm-text="Вы уверены? Заказчик будет удален!"
    delete-text="Удалить"
>
    <template v-if="(!authStore.userCan('CLIENTS_EDIT') && currentClient.data.id !== null) || (!authStore.userCan('ORDERS_ADD') && currentClient.data.id === null)" #extra>
        <div style="color: #9ca3af">Только для просмотра</div>
    </template>
    <Client
        v-model="currentClient.data"
        :loading="clientDrawer.isLoading"
        :errors="clientsStore.err?.errors"
        :read-only="(!authStore.userCan('CLIENTS_EDIT') && currentClient.data.id !== null) || (!authStore.userCan('CLIENTS_ADD') && currentClient.data.id === null)"
    />
</drawer>

<drawer
    v-model:open="carrierDrawer.isOpen"
    @save="saveCarrier"
    @delete="deleteCarrier"
    @close="() => {carrierDrawer.isOpen = false}"
    :width="736"
    :loading="carrierDrawer.isLoading"
    :saving="carrierDrawer.isSaving"
    :ok-loading="carrierDrawer.isSaving"
    :title="`${currentCarrier.data.id === null ? 'Новый перевозчик' : `Перевозчик #${currentCarrier.data.id}`}${currentCarrier.modified ? '*' : ''}`"
    :ok-text="currentCarrier.data.id === null ? 'Сохранить' : 'Сохранить и закрыть'"
    :need-ok="authStore.userCan('CARRIERS_ADD') || authStore.userCan('CARRIERS_EDIT')"
    :need-delete="authStore.userCan('CARRIERS_DELETE') && currentCarrier.data.id !== null"
    need-deletion-confirm-text="Вы уверены? Перевозчик будет удален!"
    delete-text="Удалить"
>
    <template v-if="(!authStore.userCan('CARRIERS_EDIT') && currentCarrier.data.id !== null) || (!authStore.userCan('CARRIERS_ADD') && currentCarrier.data.id === null)" #extra>
        <div style="color: #9ca3af">Только для просмотра</div>
    </template>
    <Carrier
        v-model="currentCarrier.data"
        :loading="carrierDrawer.isLoading"
        :errors="carriersStore.err?.errors"
        :read-only="(!authStore.userCan('CARRIERS_EDIT') && currentCarrier.data.id !== null) || (!authStore.userCan('CARRIERS_ADD') && currentCarrier.data.id === null)"
    />
</drawer>

</template>

<style>
input, select, textarea, .ant-select-selector {
    color: #1a202c !important;
}
</style>
