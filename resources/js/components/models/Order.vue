<script setup>
import {ref, h, watch} from "vue";
import { debounce } from "radash";

import { PlusCircleOutlined, EditOutlined, ReloadOutlined } from '@ant-design/icons-vue';
import {useSuggests} from "../../stores/models/suggests.js";

const suggest = useSuggests()
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

const tonnagesOptions = ref([])
const fetchTonnages = async () => {
    tonnagesOptions.value = await suggest.getTonnages()
}

const carBodyTypesOptions = ref([])
const fetchBodyTypesOptions = async () => {
    carBodyTypesOptions.value = await suggest.getCarBodyTypes()
}

const tConditionOptions = ref([])
const fetchTConditionOptions = async () => {
    tConditionOptions.value = await  suggest.getTConditions()
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

</script>

<template>
<div :style="{
    backgroundColor: '#f5f5f4',
    padding: '10px 20px',
    borderRadius: '6px',
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
            <a-space>
                <a-form-item label="Тоннаж машины">
                    <a-select
                        v-model:value="model.vehicle_tonnage"
                        placeholder="Тоннаж"
                        :options="tonnagesOptions"
                        @focus="fetchTonnages"
                        :loading = "suggest.isLoading"
                    />
                </a-form-item>
                <a-form-item label="Тип кузова">
                    <a-select
                        v-model:value="model.vehicle_body_type"
                        placeholder="Кузов"
                        :options="carBodyTypesOptions"
                        @focus="fetchBodyTypesOptions"
                        :loading="suggest.isLoading"
                    />
                </a-form-item>
            </a-space>
            <a-form-item label="Загрузка">
                <a-checkbox v-model:checked="model.vehicle_loading_rear">Задняя</a-checkbox>
                <a-checkbox v-model:checked="model.vehicle_loading_lateral">Боковая</a-checkbox>
                <a-checkbox v-model:checked="model.vehicle_loading_upper">Верхняя</a-checkbox>
            </a-form-item>
        </a-col>
    </a-row>
    <a-divider :dashed="true"/>
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
                            placeholder="НДС"
                            :style="{ width: '100%' }"
                        >
                            <a-select-option :value="0">Без НДС</a-select-option>
                            <a-select-option :value="1">НДС</a-select-option>
                        </a-select>
                    </a-space>
                </a-tab-pane>
                <a-tab-pane key="price" tab="Тариф"></a-tab-pane>
                <a-tab-pane key="expenses" tab="Допрасходы"></a-tab-pane>
                <a-tab-pane key="discount" tab="Скидка"></a-tab-pane>
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
                            placeholder="НДС"
                            :style="{ width: '100%' }"
                        >
                            <a-select-option :value="0">Без НДС</a-select-option>
                            <a-select-option :value="1">НДС</a-select-option>
                        </a-select>
                        <template v-if="!!model.carrier_id">
                            <a-select
                                v-model:value="model.carrier_driver_id"
                                placeholder="Выберите водителя"
                                :style="{ width: '100%' }"
                                @focus="fetchDriversByCarrier"
                                :options="driversOptions"
                            >
                                <template #option="{ label, phone }">
                                    <div style="display: flex; justify-content: space-between; align-items: center">
                                        <div>{{ label }}</div>
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
                            <a-select
                                placeholder="Выберите машину"
                                :style="{ width: '100%' }"
                            ></a-select>
                            <a-row :gutter="8">
                                <a-col :span="12">
                                    <a-input
                                        type="number"
                                        placeholder="Одометр загрузка"
                                    />
                                </a-col>
                                <a-col :span="12">
                                    <a-input
                                        type="number"
                                        placeholder="Одометр разгрузка"
                                    />
                                </a-col>
                            </a-row>
                        </template>
                    </a-space>
                </a-tab-pane>
                <a-tab-pane key="price" tab="Тариф"></a-tab-pane>
                <a-tab-pane key="expenses" tab="Допрасходы"></a-tab-pane>
                <a-tab-pane key="fines" tab="Штрафы"></a-tab-pane>
            </a-tabs>
        </a-col>
    </a-row>
    <a-row :gutter="16" style="padding-top: 16px">
        <a-col :span="12">
            <a-card title="Откуда" style="width: 100%">
                <template #extra>
                    <a-button type="link" :icon="h(PlusCircleOutlined)">
                        Добавить адрес загрузки
                    </a-button>
                </template>
            </a-card>
        </a-col>
        <a-col :span="12">
            <a-card title="Куда" style="width: 100%">
                <template #extra>
                    <a-button type="link" :icon="h(PlusCircleOutlined)">
                        Добавить адрес разгрузки
                    </a-button>
                </template>
            </a-card>
        </a-col>
    </a-row>
    <a-divider :dashed="true"/>
    <div style="margin-top: 20px">
        <a-tabs>
            <a-tab-pane tab="Дополнительные услуги" key="additional">
                <a-table />
            </a-tab-pane>
            <template #rightExtra>
                <a-button>
                    Добавить допуслугу
                </a-button>
            </template>
        </a-tabs>
    </div>


<!--    <a-tabs v-model:active-key="currentRoutesTab">-->
<!--        <a-tab-pane key="loading" tab="Загрузка"></a-tab-pane>-->
<!--        <a-tab-pane key="unloading" tab="Разгрузка"></a-tab-pane>-->
<!--    </a-tabs>-->
<!--    <a-row :gutter="8">-->
<!--        <a-col :span="12">-->
<!--            -->
<!--            <a-space direction="vertical" :style="{ width: '100%' }">-->
<!--                <a-input placeholder="Адрес загрузки" />-->
<!--                <a-input placeholder="Контактное лицо" />-->
<!--                <a-input placeholder="Номер телефона" />-->

<!--            </a-space>-->
<!--        </a-col>-->
<!--        <a-col :span="12">-->
<!--            <a-divider>Разгрузка</a-divider>-->
<!--            <a-table :show-header="false" />-->
<!--            <a-space direction="vertical" :style="{ width: '100%' }">-->

<!--            </a-space>-->
<!--        </a-col>-->
<!--    </a-row>-->
</a-form>
</template>

<style scoped>

</style>
