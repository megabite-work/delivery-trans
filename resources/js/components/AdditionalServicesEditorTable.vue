<script setup>
import {h, ref} from "vue";
import {
    CheckCircleTwoTone,
    CloseCircleTwoTone,
    PlusCircleOutlined,
} from "@ant-design/icons-vue";
import axios from "axios";

const model = defineModel("modelValue", {
    type: Array,
    default: []
})

const emit = defineEmits(["edit", "add", "delete"]);

const prop = defineProps({
    size: {type: String, default: 'small'}
})

const columns = [
    {key: 'id', title: '#'},
    {key: 'name', title: "Наименование услуги"},
    {key: 'price', title: "Цена для клиента", width: 150},
    {key: 'carrier_price', title: "Цена для перевозчика", width: 150},
]

const optionsList = ref([])
const loading = ref(false)

const handleSearch = async (q = '') => {
    optionsList.value = []
    try {
        loading.value = true
        const { data } = await axios.get('api/suggest/additional-services/name', {params: {q}})
        optionsList.value = data.map(el => ({value: el}))
    } finally {
        loading.value = false
    }
}

const currentRowIdx = ref(-1)
const addRow = () => {
    if (currentRowIdx.value >= 0) {
        applyRow(null)
    }
    model.value = Array({ name: '', price: 0, carrier_price: 0 }, ...model.value)
    editRow(0)
}
const tableRowFn = (record, idx) => ({ onClick: () => editRow(idx) })
const editRow = idx => currentRowIdx.value = idx
const applyRow = (e) => {
    if (!!e) { e.stopPropagation() }
    if (!!model.value[currentRowIdx.value] && !!model.value[currentRowIdx.value].id) {
        emit('edit', model.value[currentRowIdx.value])
    } else {
        emit('add', model.value[currentRowIdx.value])
    }
    currentRowIdx.value = -1
}
const deleteRow = (e, idx) => {
    e.stopPropagation()
    if (!!model.value[idx].id){
        emit('delete', model.value[idx].id)
    }
    currentRowIdx.value = -1
    if (idx >= 0) {
        model.value.splice(idx, 1)
    }
}
</script>

<template>
    <div style="text-align: right; margin-bottom: 10px">
        <a-button type="dashed" :icon="h(PlusCircleOutlined)" @click="addRow">Добавить допуслугу</a-button>
    </div>
    <a-table
        size="small"
        :columns="columns"
        :data-source="model"
        :pagination="false"
        :scroll="{scrollToFirstRowOnChange: true}"
        :row-class-name="() => 'cursor-pointer'"
        :custom-row="tableRowFn"
        :key="currentRowIdx"
    >
        <template #bodyCell="rec">
            <template v-if="rec.column.key === 'id'">
                {{ rec.record.id ?? '' }}
            </template>
            <template v-if="rec.index === currentRowIdx">
                <template v-if="rec.column.key === 'name'">
                    <a-auto-complete
                        v-model:value="model[currentRowIdx].name"
                        placeholder="Наименование допуслуги"
                        style="width: 100%"
                        :options="optionsList"
                        @focus="handleSearch"
                        @search="handleSearch"
                    />
                </template>
                <template v-if="rec.column.key === 'price'">
                    <div style="display: flex">
                        <a-input-number
                            v-model:value="model[currentRowIdx].price"
                            decimal-separator=","
                            :min="0"
                            placeholder="Цена"
                            style="width: 100%"
                        />
                    </div>
                </template>
                <template v-if="rec.column.key === 'carrier_price'">
                    <div style="display: flex">
                        <a-input-number
                            v-model:value="model[currentRowIdx].carrier_price"
                            :min="0"
                            decimal-separator=","
                            placeholder="Цена для перевозчика"
                        />
                        <a-button shape="circle" type="ghost" :icon="h(CheckCircleTwoTone)" @click.prevent="applyRow"/>
                        <a-button shape="circle" type="ghost" :icon="h(CloseCircleTwoTone)" @click.prevent="(e) => deleteRow(e, rec.index)"/>
                    </div>
                </template>
            </template>
            <template v-else>
                <template v-if="rec.column.key === 'name'">
                    {{ rec.record.name }}
                </template>
                <template v-if="rec.column.key === 'price'">
                    <div style="text-align: right">{{ rec.record.price || 0 }} ₽</div>
                </template>
                <template v-if="rec.column.key === 'carrier_price'">
                    <div style="text-align: right">{{ rec.record.carrier_price || 0 }} ₽</div>
                </template>
            </template>
        </template>
    </a-table>
</template>

<style scoped>

</style>
