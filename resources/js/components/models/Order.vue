<script setup>
import {ref, h, watch, computed} from "vue";
import { debounce } from "radash";

import {message} from "ant-design-vue";
import {EditOutlined, ReloadOutlined} from '@ant-design/icons-vue';
import {useSuggests} from "../../stores/models/suggests.js";
import {usePricesStore} from "../../stores/models/prices.js";
import {useClientsStore} from "../../stores/models/clients.js";

import KeyValueTable from "../KeyValueTable.vue";
import AddressList from "../AddressList.vue";
import SelectValueTable from "../SelectValueTable.vue";

const suggest = useSuggests()
const pricesStore = usePricesStore()
const clientStore = useClientsStore()
const model = defineModel()
const prop = defineProps({ loading: { type: Boolean, default: false }, errors: { type: Object, default: null } })

const cargoWeight = ref()
watch(() => model.value.cargo_weight, () => {
    if (!!model.value.cargo_weight) {
        cargoWeight.value = model.value.cargo_weight / (weightSegmentsValue.value === 'т' ? 1000 : 1)
        return
    }
    cargoWeight.value = model.value.cargo_weight
})
const handleCargoWeightChange = v => {
    model.value.cargo_weight = v * (weightSegmentsValue.value === 'т' ? 1000 : 1)
}
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

const weightSegments = ['кг', 'т']
const weightSegmentsValue = ref('кг')

const cargoNameOptions = ref([])
const handleCargoNameSearch = debounce({delay: 500}, async (q) => {
    cargoNameOptions.value = await suggest.getCargoNameSuggest(q)
})

const carCapacitiesOptions = ref([])
const fetchCarCapacities = async () => {
    carCapacitiesOptions.value = await suggest.getCarCapacities()
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
        model.value = {
            ...model.value,
            carrier_tariff_hourly: p ? parseFloat(p.hourly) : 0,
            carrier_tariff_min_hours: p ? parseFloat(p.min_hours) : 0,
            carrier_tariff_hours_for_coming: p ? parseFloat(p.hours_for_coming) : 0,
            carrier_tariff_mkad_price: p ? parseFloat(p.mkad_price) : 0,
        }
    } else if (type === 'CLIENT') {
        model.value = {
            ...model.value,
            client_tariff_hourly: p ? parseFloat(p.hourly) : 0,
            client_tariff_min_hours: p ? parseFloat(p.min_hours) : 0,
            client_tariff_hours_for_coming: p ? parseFloat(p.hours_for_coming) : 0,
            client_tariff_mkad_price: p ? parseFloat(p.mkad_price) : 0,
        }
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
            ncl = !applyDefaultPrice(client, 'CLIENT')
            nca = !applyDefaultPrice(client, 'CARRIER')
        } catch {
            message.error('Не удалось получить прайс клиента')
        }
    }
    if (!ncl && nca) {
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
        applyDefaultPrice(p, 'CLIENT')
    }
    if (nca) {
        applyDefaultPrice(p, 'CARRIER')
    }
}

const tConditionOptions = ref([])
const fetchTConditionOptions = async () => {
    tConditionOptions.value = await suggest.getTConditions()
}

const clientOptions = ref([])
const carrierOptions = ref([])

const handleClientSearch = debounce({delay: 500}, async q => {
    clientOptions.value = await suggest.searchClient(q)
})
const handleClientSearchFocus = async () => {
    if (!model.value.client_id) {
        clientOptions.value = await suggest.searchClient('')
    }
}

const handleCarrierSearch = debounce({delay: 500}, async q => {
    carrierOptions.value = await suggest.searchCarrier(q)
})
const handleCarrierSearchFocus = async () => {
    if (!model.value.carrier_id) {
        carrierOptions.value = await suggest.searchCarrier('')
    }
}

const driversOptions = ref([])
const fetchDriversByCarrier = async () => {
    if (!model.value.carrier_id) {
        driversOptions.value = []
        return
    }
    driversOptions.value = await suggest.getDriversByCarrier(model.value.carrier_id)
}

const handleCarrierChange = (e) => {
    const selectedCarrier = carrierOptions.value.find((el) => el.value === e)
    model.value.carrier_vat = selectedCarrier.vat
    model.value.carrier_driver_id = undefined
    model.value.carrier_car_id = undefined
    model.value.carrier_trailer_id = undefined
    carsOptions.value = []
    trailerOptions.value = []
    driversOptions.value = []
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
const carsOptions = ref([])
const trailerOptions = ref([])

const fetchCarsByCarrier = async () => {
    if (!model.value.carrier_id) {
        carsOptions.value = []
        return
    }
    const cars = await suggest.getCarsByCarrier(model.value.carrier_id)
    carsOptions.value = cars.filter(car => car.type !== 'TRAILER').map(car => ({ ...car, typeLabel: carsOptions[car.type] }))
}

const fetchTrailersByCarrier = async () => {
    if (!model.value.carrier_id) {
        trailerOptions.value = []
        return
    }
    const cars = await suggest.getCarsByCarrier(model.value.carrier_id)
    trailerOptions.value = cars.filter(car => car.type === 'TRAILER').map(car => ({ ...car, typeLabel: carsOptions[car.type] }))
}

const currentCarIsTractor = computed(() => {
    let res = false
    carsOptions.value.forEach((car) => {
        if (car.value === model.value.carrier_car_id && car.type === 'TRACTOR') {
            res = true
        }
    })
    return res
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
                <div :style="{
                display: 'flex',
                alignItems: 'center',
                gap: '8px',
                width: '50%'
            }">
                    <div :style="{
                    width: '12px',
                    height: '12px',
                    backgroundColor: '#9ca3af',
                    borderRadius: '8px'
                }"></div>
                    <div :style="{
                    color: '#27272a',
                    fontWeight: '600'
                }">Новая</div>
                </div>
                К оплате заказчику:
                <div :style="{
                    fontSize: '24px',
                    fontWeight: '600',
                    color: '#27272a'
                }">
                    <a-dropdown placement="bottom" arrow>
                        <a class="ant-dropdown-link" @click.prevent>2,134.27 ₽</a>
                        <template #overlay>
                            <a-menu>
                                <a-menu-item :icon="h(EditOutlined)">
                                    Изменить сумму
                                </a-menu-item>
                                <a-menu-divider />
                                <a-menu-item :icon="h(ReloadOutlined)">
                                    Посчитать автоматически
                                </a-menu-item>
                            </a-menu>
                        </template>
                    </a-dropdown>
                    <div :style="{
                        fontWeight: '400',
                        fontSize: '11px',
                        color: '#404040'
                    }">
                        Допрасходы: 3,700₽
                        <a-divider type="vertical" />
                        Скидка: 3,000₽
                    </div>
                </div>
            </div>


        </a-col>
        <a-col :span="12" style="color: #737373">
            <div :style="{
                    display: 'flex',
                    alignItems: 'center',
                    gap: '8px'
                }">
                <div :style="{
                        width: '12px',
                        height: '12px',
                        backgroundColor: '#9ca3af',
                        borderRadius: '8px'
                    }"></div>
                <div :style="{
                        color: '#27272a',
                        fontWeight: '600'
                    }">Новая</div>
            </div>
            К оплате перевозчику:
            <div :style="{
                fontSize: '24px',
                fontWeight: '600',
                color: '#27272a'
            }">
                <a-dropdown placement="bottom" arrow>
                    <a class="ant-dropdown-link" @click.prevent>1,720.00 ₽</a>
                    <template #overlay>
                        <a-menu>
                            <a-menu-item :icon="h(EditOutlined)">
                                Изменить сумму
                            </a-menu-item>
                            <a-menu-divider />
                            <a-menu-item :icon="h(ReloadOutlined)">
                                Посчитать автоматически
                            </a-menu-item>
                        </a-menu>
                    </template>
                </a-dropdown>
                <div :style="{
                            fontWeight: '400',
                            fontSize: '11px',
                            color: '#404040'
                        }">
                    Скидка: 500₽
                </div>
            </div>
        </a-col>
    </a-row>
</div>
<a-form layout="vertical" :model="model">
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
                    />
                </a-form-item>
                <a-form-item label="Температурный режим">
                    <a-select
                        v-model:value="model.cargo_temp"
                        placeholder="Режим"
                        style="width: 100%"
                        :options="tConditionOptions"
                        @focus="fetchTConditionOptions"
                        :loading = "suggest.isLoading"
                    />
                </a-form-item>
            </a-space>
            <a-space>
                <a-form-item label="Паллеты" style="width: 172px">
                    <a-checkbox v-model:checked="model.cargo_in_pallets">Груз на паллетах</a-checkbox>
                </a-form-item>
                <a-form-item v-if="!!model.cargo_in_pallets" label="Количество палет">
                    <a-input-number
                        v-model:value="model.cargo_pallets_count"
                        placeholder="Количество палет"
                        style="width: 100%"
                        :min="0"
                    />
                </a-form-item>
            </a-space>
        </a-col>
        <a-col :span="12">
            <a-divider orientation="left">Машина</a-divider>
            <a-form-item label="Вместимость машины">
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
                                                    @click="()=>applyDefaultPrice(defaultPrice, 'CLIENT')"
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
                            <a-divider dashed style="margin: 0; font-size: 11px" orientation="left" orientation-margin="0">Водитель</a-divider>
                            <a-select
                                v-model:value="model.carrier_driver_id"
                                placeholder="Выберите водителя"
                                :style="{ width: '100%' }"
                                :options="driversOptions"
                                @focus="fetchDriversByCarrier"
                            >
                                <template #option="{ name, phone }">
                                    <div style="display: flex; justify-content: space-between; align-items: center">
                                        <div>{{ name }}</div>
                                        <div style="font-size: 11px; font-weight: 500">
                                            {{ phone }}
                                        </div>
                                    </div>
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
                                                    @click="()=>applyDefaultPrice(defaultPrice, 'CARRIER')"
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
                    />
                </a-tab-pane>
            </a-tabs>
        </a-col>
    </a-row>
    <a-row :gutter="16" style="padding-top: 16px">
        <a-col :span="12">
            <AddressList
                v-model="model.from_where"
                title="Откуда"
                add-button-text="Добавить адрес загрузки"
            />
        </a-col>
        <a-col :span="12">
            <AddressList
                v-model="model.to_where"
                title="Куда"
                add-button-text="Добавить адрес разгрузки"
            />
        </a-col>
    </a-row>
    <a-divider orientation="left">Дополнительные услуги</a-divider>
    <SelectValueTable
        v-model="model.additional_service"
        header-key-text="Услуга"
        header-value-text="Сумма"
        add-button-text="Добавить допуслугу"
        key-placeholder-text="Выберите допуслугу"
        value-placeholder-text="Сумма"
        value-postfix-text="₽"
        :select-fetcher="suggest.getAdditionalServices"
    />
</a-form>

</template>

<style scoped>

</style>
