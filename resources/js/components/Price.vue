<script setup>
import {onMounted, reactive, ref} from "vue";
import {useSuggests} from "../stores/models/suggests.js";

const props = defineProps({
    prices: { type: Object, default: {} }
})

const emit = defineEmits(['priceChange'])

const priceTypeOptions = reactive([
    { label: "Для заказчика", value: "CLIENT" },
    { label: "Для перевозчика", value: "CARRIER" },
])

const priceLoading = ref(false)

const priceColumns = ref([])
const priceRows = ref([])

const priceType = ref(priceTypeOptions[0].value)
const suggests = useSuggests()

const carCapacities = ref([])
const carBodyTypes = ref([])

onMounted(async () => {
    try {
        priceLoading.value = true
        carCapacities.value = await suggests.getCarCapacities()
        carBodyTypes.value = await suggests.getCarBodyTypes()
    } finally {
        priceLoading.value = false
    }
    priceColumns.value = Array({
        key: 'carCapacity',
        dataIndex: 'carCapacity',
    }, ...carBodyTypes.value.map(bt => ({
        title: bt.label,
        key: bt.value
    })))
    priceRows.value = carCapacities.value.map(cc => ({
        carCapacity: {
            tonnage: cc.tonnage,
            volume: cc.volume,
            pallets_count: cc.pallets_count,
        }
    }))
})
</script>

<template>
    <a-segmented v-model:value="priceType" :options="priceTypeOptions"/>
    <a-table
        :columns="priceColumns"
        :data-source="priceRows"
        :pagination="false"
        :loading="priceLoading"
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
                <div style="display: flex; font-size: 12px">
                    <div style="text-align: right; flex-basis: 50px">{{ record.carCapacity.tonnage }}т.</div>
                    <div style="width: 25px; text-align: center">–</div>
                    <div style="text-align: right; flex-basis: 50px">{{ record.carCapacity.volume }}м³</div>
                    <div style="width: 25px; text-align: center">–</div>
                    <div style="text-align: right; flex-basis: 30px">{{ record.carCapacity.pallets_count }}п.</div>
                </div>
            </template>
            <template v-else>
                <div style="background-color: #f1f5f9; width: 100%">&nbsp;</div>
            </template>
        </template>
    </a-table>
</template>

<style scoped>

</style>
