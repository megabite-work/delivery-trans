<script setup>
import {h, ref} from "vue";
import {
    CheckCircleTwoTone,
    CloseCircleTwoTone,
    PlusCircleOutlined,
} from "@ant-design/icons-vue";
import {debounce, isFunction} from "radash";

const prop = defineProps({
    scroll: { type: Object, required: false },
    headerKeyText: { type: String, default: 'Ключ' },
    headerValueText: { type: String, default: 'Значение' },
    addButtonText: { type: String, default: 'Добавить' },
    keyPlaceholderText: {type: String, default: 'Ключ'},
    valuePlaceholderText: {type: String, default: 'Значение'},
    valuePostfixText: {type: String, default: ''},
    valueWidth: { type: Number, default: 150 },
    size: {type: String, default: 'small'},
    suggests: {type: Function}
})

const model = defineModel({type: Array, default: []})
const emit = defineEmits(["update", "add"])

const columns = [
    {key: 'k', title: prop.headerKeyText},
    {key: 'v', title: prop.headerValueText, width: prop.valueWidth},
]
const suggestOptions = ref([])
const currentRowIdx = ref(-1)
const isAdded = ref(false)

const handleSearch = debounce({delay: 500},async q => {
    if (isFunction(prop.suggests)) {
        suggestOptions.value = (await prop.suggests(q)).map(el => ({value: el}))
    }
})
const addRow = () => {
    isAdded.value = true
    if (currentRowIdx.value >= 0) {applyRow(null)}
    model.value = [{ k: '', v: 0 }, ...model.value]
    editRow(0)
}
const tableRowFn = (record, idx) => ({ onClick: () => editRow(idx) })
const editRow = idx => currentRowIdx.value = idx
const applyRow = (e) => {
    if (!!e) { e.stopPropagation() }
    currentRowIdx.value = -1
    emit('update')
    if (isAdded.value) {
        emit('add', model.value[0])
        isAdded.value = false
    }
}
const deleteRow = (e, idx) => {
    e.stopPropagation()
    currentRowIdx.value = -1
    model.value.splice(idx, 1)
    emit('update')
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
<!--                    <a-input-->
<!--                        v-model:value="model[currentRowIdx].k"-->
<!--                        :placeholder="keyPlaceholderText"-->
<!--                    />-->
                    <a-auto-complete
                        v-model:value="model[currentRowIdx].k"
                        :options="suggestOptions"
                        :placeholder="keyPlaceholderText"
                        @search="handleSearch"
                        style="width: 100%"
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
                <template v-if="rec.column.key === 'v'">
                    <div style="text-align: right">{{ rec.record.v }} {{ valuePostfixText }}</div>
                </template>
            </template>
        </template>
    </a-table>
</template>

<style scoped>

</style>
