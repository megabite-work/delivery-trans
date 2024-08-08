<script setup>
import {onBeforeUnmount, onMounted, reactive, ref} from "vue";
import {message} from "ant-design-vue";
import Layout from "../layouts/AppLayout.vue";
import Drawer from "../components/Drawer.vue";
import {ControlOutlined} from "@ant-design/icons-vue";
import Role from "../components/models/Role.vue";
import {useRolesStore} from "../stores/models/roles.js";


const columnsRoles = [
    { key: '__icon', width: 50 },
    { title: 'Название роли', dataIndex: 'name' },
];

const rolesStore = useRolesStore()

const currentRole = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const openMainDrawer = async (roleId = null) => {
    currentRole.data = { id: null }
    currentRole.modified = false
    mainDrawer.isOpen = true
    if (roleId !== null) {
        try {
            mainDrawer.isLoading = true
            currentRole.data = await rolesStore.getRole(roleId)
        } catch (e) {
            mainDrawer.isOpen = false
            message.error('Ошибка загрузки')
        } finally {
            mainDrawer.isLoading = false
        }
    }
}

const closeMainDrawer = () => {
    if (mainDrawer.isSaving) {
        return
    }
    mainDrawer.isOpen = false
    currentRole.data = { id: null }
}

const saveRole = async () => {
    mainDrawer.isSaving = true
    try {
        if (currentRole.data.id === null) {
            currentRole.data  = await rolesStore.createRole(currentRole.data)
            currentRole.modified = false
            message.success('Роль создана')
        } else {
            currentRole.data = await rolesStore.storeRole(currentRole.data)
            currentRole.modified = false
            message.success('Роль записана')
        }
        mainDrawer.isSaving = false
        closeMainDrawer()
    } catch (e) {
        message.error(`Ошибка. Не удалось ${currentRole.data.id === null ? 'создать' : 'сохранить'} роль`)
    } finally {
        mainDrawer.isSaving = false
        await rolesStore.refreshDataList()
    }
}

const deleteRole = async () => {
    if (currentRole.data.id === null) {
        return
    }
    try {
        await rolesStore.deleteRole(currentRole.data.id)
        message.success('Роль успешно удалена')
        await rolesStore.refreshDataList()
        closeMainDrawer()
    } catch (e) {
        message.error('Ошибка. Не удалось удалить роль')
    }
}

const clientHeight = ref(document.documentElement.clientHeight)
const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }
const tableRowFn = record => ({ onClick: () => openMainDrawer(record.id) })
onMounted(() => {
    rolesStore.refreshDataList()
    window.addEventListener('resize', updateClientHeight)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateClientHeight)
})
</script>

<template>
    <Layout title="Роли">
        <template #headerExtra>
            <a-button type="primary" @click="() => openMainDrawer()">Новая роль</a-button>
        </template>
        <a-table
            :loading="rolesStore.listLoading"
            :custom-row="tableRowFn"
            :columns="columnsRoles"
            :data-source="rolesStore.dataList"
            :pagination="false"
            :scroll="{ y: clientHeight - 335 }"
            :row-class-name="() => 'cursor-pointer'"
        >
            <template #bodyCell="{ column, record }">

                <template v-if="column.key === '__icon'">
                    <ControlOutlined />
                </template>
            </template>
        </a-table>
        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="saveRole"
            @delete="deleteRole"
            @close="() => {mainDrawer.isOpen = false}"
            :width="736"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`${currentRole.data.id === null ? 'Новая роль' : `Роль ${currentRole.data.name}`}${currentRole.modified ? '*' : ''}`"
            ok-text="Сохранить и закрыть"
            :need-delete="currentRole.data.id !== null"
            need-deletion-confirm-text="Вы уверены? Роль будет удалена!"
            delete-text="Удалить"
        >
            <Role v-model="currentRole.data" :loading="mainDrawer.isLoading" :errors="rolesStore.err?.errors"/>
        </drawer>
    </Layout>
</template>

<style scoped>

</style>
