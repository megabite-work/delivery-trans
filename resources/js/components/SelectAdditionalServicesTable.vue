<script setup>
import {computed, h, ref} from "vue";
import {
    CheckCircleTwoTone,
    CloseCircleTwoTone,
    PlusCircleOutlined,
} from "@ant-design/icons-vue";

const model = defineModel("modelValue", {
    type: Array,
    default: []
})

const emit = defineEmits(["change"]);

const prop = defineProps({
    selectFetcher: {type: Function, default: () => []},
    cid: {type: Number, default: 0},
    withoutSelected: {type: Boolean, default: true},
    scroll: { type: Object, required: false },
    valuePostfixText: {type: String, default: ''},
    size: {type: String, default: 'small'},
    readOnly: {type: Boolean, default: false }
})

const columns = [
    {key: 'k', title: 'Услуга'},
    {key: 'c', title: 'Кол-во', width: 150},
    {key: 'v', title: 'Цена клиент', width: 150},
    {key: 'vp', title: 'Цена перевозчик', width: 150},
]

const optionsList = ref([])
const loading = ref(false)
const handleFocus = async () => {
    try {
        loading.value = true
        optionsList.value = await prop.selectFetcher('', prop.cid)
    } finally {
        loading.value = false
    }
}

const handleChange = (e) => {
    const option = optionsList.value.find((el) => el.value === e)
    if (option) {
        model.value[currentRowIdx.value].v = (option.v || 0) * model.value[currentRowIdx.value].c || 0
        model.value[currentRowIdx.value].vp = (option.vp || 0) * model.value[currentRowIdx.value].c || 0
    }
}

const options = computed(() => {
    if (!prop.withoutSelected) {
        return optionsList.value.filter(() => true)
    }
    return optionsList.value.filter((el) => !model.value.find((v) => v.k === el.value))
})

const currentRowIdx = ref(-1)

const addRow = () => {
    if (currentRowIdx.value >= 0) {
        applyRow(null)
    }
    model.value = Array({ k: '', c: 1, v: 0, vp: 0 }, ...model.value)
    editRow(0)
}
const tableRowFn = (record, idx) => ({ onClick: () => {
    if(!prop.readOnly) {
        editRow(idx)
    }
}})
const editRow = idx => {
    if (model.value[idx] && !model.value[idx].hasOwnProperty('c')) {
        model.value[idx].c = 1
    }
    currentRowIdx.value = idx
}
const applyRow = (e) => {
    if (!!e) { e.stopPropagation() }
    currentRowIdx.value = -1
    emit('change')
}
const deleteRow = (e, idx) => {
    e.stopPropagation()
    currentRowIdx.value = -1
    if (idx >= 0) {
        model.value.splice(idx, 1)
    }
    emit('change')
}
</script>

<template>
    <div style="text-align: right; margin-bottom: 10px">
        <a-button type="dashed" :icon="h(PlusCircleOutlined)" @click="addRow">Добавить допуслугу</a-button>
    </div>
    <a-table
        :columns="columns"
        :size="size"
        :data-source="model"
        :pagination="false"
        :scroll="{scrollToFirstRowOnChange: true, ...scroll}"
        :row-class-name="() => 'cursor-pointer'"
        :custom-row="tableRowFn"
        :key="currentRowIdx"
    >
        <template #bodyCell="rec">
            <template v-if="rec.index === currentRowIdx">
                <template v-if="rec.column.key === 'k'">
                    <a-select
                        v-model:value="model[currentRowIdx].k"
                        placeholder="Выберите допуслугу"
                        style="width: 100%"
                        :options="options"
                        @focus="handleFocus"
                        @change="handleChange"
                    >
                        <template v-if="loading" #notFoundContent>
                            <div style="display: flex; justify-content: center; align-items: center; min-height: 100px">
                                <a-spin size="small" />
                            </div>
                        </template>
                    </a-select>
                </template>
                <template v-if="rec.column.key === 'c'">
                    <a-input-number
                        v-model:value="model[currentRowIdx].c"
                        style="width: 100%"
                        :min="0"
                        placeholder="Кол-во"
                    />
                </template>
                <template v-if="rec.column.key === 'v'">
                    <div style="display: flex">
                        <a-input-number
                            v-model:value="model[currentRowIdx].v"
                            :min="0"
                            placeholder="Цена"
                            style="width: 100%"
                        />
                    </div>
                </template>
                <template v-if="rec.column.key === 'vp'">
                    <div style="display: flex">
                        <a-input-number
                            v-model:value="model[currentRowIdx].vp"
                            :min="0"
                            placeholder="Цена"
                        />
                        <a-button shape="circle" type="ghost" :icon="h(CheckCircleTwoTone)" @click.prevent="applyRow"/>
                        <a-button shape="circle" type="ghost" :icon="h(CloseCircleTwoTone)" @click.prevent="(e) => deleteRow(e, rec.index)"/>
                    </div>
                </template>
            </template>
            <template v-else>
                <template v-if="rec.column.key === 'k'">
                    {{ rec.record.k }}
                </template>
                <template v-if="rec.column.key === 'c'">
                    {{ rec.record.c ?? 1 }}
                </template>
                <template v-if="rec.column.key === 'v'">
                    <div style="text-align: right">{{ rec.record.v }} {{ valuePostfixText }}</div>
                </template>
                <template v-if="rec.column.key === 'vp'">
                    <div style="text-align: right">{{ rec.record.vp }} {{ valuePostfixText }}</div>
                </template>
            </template>
        </template>
    </a-table>
</template>

<style scoped>

</style>
