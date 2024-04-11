<script setup>
import {h, ref} from "vue";
import {
    CheckCircleTwoTone,
    CloseCircleTwoTone,
    PlusCircleOutlined,
} from "@ant-design/icons-vue";

const prop = defineProps({
    modelValue: {type: Array, default: []},
    scroll: { type: Object, required: false },
    headerKeyText: { type: String, default: 'Ключ' },
    headerValueText: { type: String, default: 'Значение' },
    addButtonText: { type: String, default: 'Добавить' },
    keyPlaceholderText: {type: String, default: 'Ключ'},
    valuePlaceholderText: {type: String, default: 'Значение'},
    valuePostfixText: {type: String, default: ''},
    valueWidth: { type: Number, default: 150 },
    size: {type: String, default: 'small'}
})
const emit = defineEmits(['update:modelValue'])


const columns = [
    {key: 'k', title: prop.headerKeyText},
    {key: 'v', title: prop.headerValueText, width: prop.valueWidth},
]

const currentRowIdx = ref(-1)
const rows = ref([])
const addRow = () => {
    if (currentRowIdx.value >= 0) {applyRow(null)}
    rows.value.unshift({ k: '', v: 0 })
    editRow(0)
}
const tableRowFn = (record, idx) => ({ onClick: () => editRow(idx) })
const editRow = idx => currentRowIdx.value = idx
const applyRow = (e) => {
    if (!!e) { e.stopPropagation() }
    currentRowIdx.value = -1
    emit('update:modelValue', rows.value)
}
const deleteRow = (e, idx) => {
    e.stopPropagation()
    currentRowIdx.value = -1
    delete rows.value[idx]
    emit('update:modelValue', rows.value)
}
</script>

<template>
    <div style="text-align: right; margin-bottom: 10px">
        <a-button type="dashed" :icon="h(PlusCircleOutlined)" @click="addRow">{{ addButtonText }}</a-button>
    </div>
    <a-table
        :columns="columns"
        :size="size"
        :data-source="rows"
        :pagination="false"
        :scroll="{scrollToFirstRowOnChange: true, ...scroll}"
        :row-class-name="() => 'cursor-pointer'"
        :custom-row="tableRowFn"
        :key="currentRowIdx"
    >
        <template #bodyCell="rec">
            <template v-if="rec.index === currentRowIdx">
                <template v-if="rec.column.key === 'k'">
                    <a-input
                        v-model:value="rows[currentRowIdx].k"
                        :placeholder="keyPlaceholderText"
                    />
                </template>
                <template v-if="rec.column.key === 'v'">
                    <div style="display: flex">
                        <a-input-number
                            v-model:value="rows[currentRowIdx].v"
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
                <template v-if="rec.column.key === 'v'">
                    <div style="text-align: right">{{ rec.record.v }} {{ valuePostfixText }}</div>
                </template>
            </template>
        </template>
    </a-table>
</template>

<style scoped>

</style>
