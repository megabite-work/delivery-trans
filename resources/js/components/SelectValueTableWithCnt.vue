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
    withoutSelected: {type: Boolean, default: true},
    scroll: { type: Object, required: false },
    headerKeyText: { type: String, default: 'Ключ' },
    headerCountText: { type: String, default: 'Количество' },
    headerValueText: { type: String, default: 'Значение' },
    addButtonText: { type: String, default: 'Добавить' },
    keyPlaceholderText: {type: String, default: 'Ключ'},
    valuePlaceholderText: {type: String, default: 'Значение'},
    valuePostfixText: {type: String, default: ''},
    countWidth: { type: Number, default: 150 },
    valueWidth: { type: Number, default: 150 },
    size: {type: String, default: 'small'}
})

const columns = [
    {key: 'k', title: prop.headerKeyText},
    {key: 'c', title: prop.headerCountText, width: prop.countWidth},
    {key: 'v', title: prop.headerValueText, width: prop.valueWidth},
]

const optionsList = ref([])
const loading = ref(false)
const handleFocus = async () => {
    try {
        loading.value = true
        optionsList.value = await prop.selectFetcher()
    } finally {
        loading.value = false
    }
}

const handleChange = (e) => {
    const option = optionsList.value.find((el) => el.value === e)
    if (option && option.v) {
        model.value[currentRowIdx.value].v = option.v
    }
}

const options = computed(() => {
    if (!prop.withoutSelected) {
        return optionsList
    }
    return optionsList.value.filter((el) => !model.value.find((v) => v.k === el.value))
})

const currentRowIdx = ref(-1)
const addRow = () => {
    if (currentRowIdx.value >= 0) {
        applyRow(null)
    }
    model.value = Array({ k: '', c: 1, v: 0 }, ...model.value)
    editRow(0)
}
const tableRowFn = (record, idx) => ({ onClick: () => editRow(idx) })
const editRow = idx => {
    if (!model.value[idx].c) {
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
        <a-button type="dashed" :icon="h(PlusCircleOutlined)" @click="addRow">{{ addButtonText }}</a-button>
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
                        :placeholder="keyPlaceholderText"
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
                        :placeholder="headerCountText"
                    />
                </template>
                <template v-if="rec.column.key === 'v'">
                    <div style="display: flex">
                        <a-input-number
                            v-model:value="model[currentRowIdx].v"
                            :min="0"
                            :placeholder="valuePlaceholderText"
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
            </template>
        </template>
    </a-table>
</template>

<style scoped>

</style>
