<script setup>
import {computed, onMounted, reactive, ref} from "vue";
import {useSuggests} from "../stores/models/suggests.js";
import Drawer from "../components/Drawer.vue";
import {usePricesStore} from "../stores/models/prices.js";
import {message} from "ant-design-vue";
import PriceModel from "./models/PriceModel.vue";

const props = defineProps({
    prices: { type: Object, default: {} },
    ownerId: { type: Number, required: true },
    loading: { type: Boolean, default: false },
})

const emit = defineEmits(['priceChange'])

const pricesStore = usePricesStore()

const priceTypeOptions = reactive([
    { label: "Для заказчика", value: "CLIENT" },
    { label: "Для перевозчика", value: "CARRIER" },
])

const currentPrice = reactive({data: {id: null}, modified: false})
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const openDrawer = async (body_type, capacity, priceId = null) => {
    currentPrice.data = {
        id: null,
        type: priceType.value,
        car_body_type: body_type,
        car_capacity_id: capacity.id,
        car_capacity: capacity,
    }
    currentPrice.modified = false
    mainDrawer.isOpen = true
    if (priceId !== null) {
        try {
            mainDrawer.isLoading = true
            currentPrice.data = await pricesStore.getPriceById(priceId)
        } catch {
            mainDrawer.isOpen = false
            message.error('Не удалось загрузить прайс-лист')
        } finally {
            mainDrawer.isLoading = false
        }
    }
}

const priceLoading = ref(false)

const priceType = ref(priceTypeOptions[0].value)
const suggests = useSuggests()

const carCapacities = ref([])
const carBodyTypes = ref([])

const priceColumns = computed(() => {
    return Array({
        key: 'carCapacity',
        dataIndex: 'carCapacity',
    }, ...carBodyTypes.value.map(bt => ({
        title: bt.label,
        key: bt.value
    })))
})

const priceRows = computed(() => {
    return carCapacities.value.map(cc => {
        const cap = {
            carCapacity: {
                id: cc.id,
                tonnage: cc.tonnage,
                volume: cc.volume,
                pallets_count: cc.pallets_count,
            }
        }
        carBodyTypes.value.forEach(bodyType => {
            const p = props.prices.find(
                currentPrice => {
                    return currentPrice.type === priceType.value &&
                        currentPrice.car_capacity_id === cc.id &&
                        currentPrice.car_body_type === bodyType.value
                }
            )
            if (p) {
                cap[bodyType.value] = {
                    id: p.id,
                    hourly: p.hourly,
                    hours_for_coming: p.hours_for_coming,
                    min_hours: p.min_hours,
                    mkad_price: p.mkad_price,
                    car_capacity: p.car_capacity,
                }
            }
        })
        return cap
    })
})

const savePrice = async () => {
    try {
        mainDrawer.isSaving = true
        if (currentPrice.data.id === null) {
            await pricesStore.createPriceForClient({
                client_id: props.ownerId,
                ...currentPrice.data,
            })
        } else {
            await pricesStore.storePrice(currentPrice.data)
        }
        mainDrawer.isOpen = false
    } catch {
        message.error('Не удалось записать прайс-лист')
    } finally {
        mainDrawer.isSaving = false
        emit('priceChange')
    }
}
const deletePrice = async () => {
    if (currentPrice.data.id === null) {
        return
    }
    try {
        mainDrawer.isLoading = true
        await pricesStore.deletePrice(currentPrice.data.id)
    } catch {
        message.error('Не удалось удалить прайс-лист')
    } finally {
        mainDrawer.isLoading = false
        mainDrawer.isOpen = false
        emit('priceChange')
    }
}

onMounted(async () => {
    try {
        priceLoading.value = true
        carCapacities.value = await suggests.getCarCapacities()
        carBodyTypes.value = await suggests.getCarBodyTypes()
    } finally {
        priceLoading.value = false
    }
})
</script>

<template>
    <a-segmented v-model:value="priceType" :options="priceTypeOptions"/>
    <a-table
        :columns="priceColumns"
        :data-source="priceRows"
        :pagination="false"
        :loading="priceLoading || loading"
        size="small"
        style="padding-top: 5px"
    >
        <template #headerCell="cell">
            <div style="font-size: 10px; text-wrap: none; text-align: center">
                {{cell.title}}
            </div>
        </template>
        <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'carCapacity'">
                <div style="display: flex; font-size: 12px; font-weight: 500">
                    <div style="text-align: right; flex-basis: 50px">{{ record.carCapacity.tonnage }}т.</div>
                    <div style="width: 25px; text-align: center">–</div>
                    <div style="text-align: right; flex-basis: 50px">{{ record.carCapacity.volume }}м³</div>
                    <div style="width: 25px; text-align: center">–</div>
                    <div style="text-align: right; flex-basis: 30px">{{ record.carCapacity.pallets_count }}п.</div>
                </div>
            </template>
            <template v-else>
                <a-popover v-if="record[column.key]">
                    <template #content>
                        <div>
                            <div style="display: flex; flex-direction: row; gap: 3px">
                                <div style="flex-shrink: 0">
                                    <div>Ставка:</div>
                                    <div>Минимум:</div>
                                    <div>Часов на подачу:</div>
                                    <div>Тариф за МКАД:</div>
                                </div>
                                <div style="text-align: right; flex-shrink: 0; font-weight: 600">
                                    <div>{{ record[column.key].hourly }}</div>
                                    <div>{{ record[column.key].min_hours }}</div>
                                    <div>{{ record[column.key].hours_for_coming }}</div>
                                    <div>{{ record[column.key].mkad_price }}</div>
                                </div>
                                <div style="display: flex; flex-direction: column; flex-shrink: 0">
                                    <div>₽/час</div>
                                    <div>час.</div>
                                    <div>час.</div>
                                    <div>₽/км</div>
                                </div>
                            </div>
                        </div>
                    </template>
                    <div
                        style="background-color: #f1f5f9; width: 100%; font-size: 12px;padding: 0 5px; cursor: pointer"
                        @click="() => openDrawer(column.key, record.carCapacity, record[column.key].id)"
                    >
                        <div style="display: flex; flex-direction: row; gap: 3px">
                            <div style="text-align: right">
                                <div>{{ record[column.key].hourly }}</div>
                                <div>{{ record[column.key].min_hours }}</div>
                                <div>{{ record[column.key].hours_for_coming }}</div>
                                <div>{{ record[column.key].mkad_price }}</div>
                            </div>
                            <div style="display: flex; flex-direction: column; flex-shrink: 0">
                                <div>₽/час</div>
                                <div>час.</div>
                                <div>час.</div>
                                <div>₽/км</div>
                            </div>
                        </div>
                    </div>
                </a-popover>
                <div v-else
                     style="background-color: #f1f5f9; width: 100%; height: 100%; cursor: pointer"
                     @click="() => openDrawer(column.key, record.carCapacity,null)"
                >&nbsp;</div>
            </template>
        </template>
    </a-table>
    <Drawer
        v-model:open="mainDrawer.isOpen"
        @save="savePrice"
        @delete="deletePrice"
        @close="mainDrawer.isOpen = false"
        :loading="mainDrawer.isLoading"
        :saving="mainDrawer.isSaving"
        :title="`Прайс-лист для ${currentPrice.data.type === 'CLIENT' ? 'заказчика': 'перевозчика'}`"
        ok-text="Сохранить и закрыть"
        :need-delete = "currentPrice.data.id !== null"
        need-deletion-confirm-text="Данный прайс будет удален"
        delete-text=""
    >
        <PriceModel v-model="currentPrice.data" />
    </Drawer>
</template>

<style scoped>

</style>
