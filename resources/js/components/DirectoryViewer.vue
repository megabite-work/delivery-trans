<script setup>

import axios from "axios";
import {computed, onMounted, reactive, ref} from "vue";
import {message} from "ant-design-vue";
import {UnorderedListOutlined} from "@ant-design/icons-vue";

const createNewElement = () => {
    currentModel.data = {}
    isNewModel.value = true
    isModalOpen.value = true
}

const props = defineProps({
    dirName: { type: String, required: true },
    dirColumns: { type: Array, required: true },
    dirKey: { type: String, required: true },
    canEdit: { type: Boolean, default: true },
    canDelete: { type: Boolean, default: true },
})

defineExpose({
    createNewElement
})

const dirList = ref([])
const isDirLoading = ref(false)
const currentModel = reactive({ data: {}, modified: false })
const isModalOpen = ref(false)
const isNewModel = ref(true)
const isSaving = ref(false)

const handleModalOk = async () => {
    try {
        isSaving.value = true
        if (isNewModel.value) {
            await axios.post(`api/${props.dirName}`, currentModel.data)
        } else {
            await axios.put(`api/${props.dirName}/${currentModel.data[props.dirKey]}`, currentModel.data)
        }
    } catch {
        message.error('При сохранении элемента возникла ошибка')
    } finally {
        isModalOpen.value = false
        isSaving.value = false
        await getDataList()
    }
}

const editModel = (model) => {
    isNewModel.value = false
    currentModel.data = {...model}
    isModalOpen.value = true
}

const deleteModel = async (id) => {
    try {
        await axios.delete(`api/${props.dirName}/${id}`)
    } catch {
        message.error("При удалении элемента произошла ошибка")
    } finally {
        await getDataList()
    }
}

const tableColumns = computed(() => {
    return [
        { key: '_i', width: 50 },
        ...props.dirColumns.map(el => ({key: el.key, title: el.label, type: el.type})),
        { key: '_actions' }
    ]
})

onMounted( () => {
    getDataList()
})

const getDataList = async () => {
    try {
        isDirLoading.value = true
        const { data } = await axios.get(`api/${props.dirName}`)
        dirList.value = data
    } catch(e) {
        message.error('Во время загрузки справочника произошла ошибка')
    } finally {
        isDirLoading.value = false
    }
}
</script>

<template>
    <a-table
        :loading="isDirLoading"
        :columns="tableColumns"
        :data-source="dirList"
        :pagination="false"
        :row-class-name="() => 'cursor-pointer'"
    >
        <template #bodyCell="{ column, record }">
            <template v-if="column.key === '_i'">
                <UnorderedListOutlined />
            </template>
            <template v-if="column.key === '_actions'">
                <a-button v-if="canEdit" type="link" @click="() => editModel(record)">Редактировать</a-button>
                <a-divider v-if="props.canEdit && props.canDelete" type="vertical" />
                <a-popconfirm v-if="canDelete"
                  title="Вы хотите удалить этот элемент?"
                  ok-text="Да"
                  cancel-text="Нет"
                  @confirm="() => deleteModel(record[props.dirKey])"
                >
                    <a-button v-if="canDelete" type="link">Удалить</a-button>
                </a-popconfirm>
            </template>
            <template v-else>
                {{ record[column.key] }}
            </template>
        </template>
    </a-table>
    <a-modal
        v-model:open="isModalOpen"
        :title="isNewModel ? 'Новый элемент' : 'Редактирование элемента'"
        @ok="handleModalOk"
        :ok-button-props="{ loading: isSaving, disabled: isSaving }"
    >
        <a-form layout="vertical">
            <a-form-item v-for="col in props.dirColumns" :key="col.key" :label="col.label">
                <a-input v-if="col.type === 'string'" v-model:value="currentModel.data[col.key]" />
                <a-input-number
                    v-if="col.type === 'number'"
                    v-model:value="currentModel.data[col.key]"
                    style="width: 100%"
                />
            </a-form-item>
        </a-form>
    </a-modal>
</template>

<style scoped>

</style>
