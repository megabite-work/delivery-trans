<script setup>
import {onMounted, onBeforeUnmount, ref, reactive} from 'vue';
import { message } from "ant-design-vue";

import { useClientsStore } from "../stores/models/clients.js";
import Layout from '../layouts/AppLayout.vue';
import Drawer from "../components/Drawer.vue";
import Client from "../components/models/Client.vue";

const columnsClients = [
    { key: 'type', width: 70 },
    { title: 'ИНН', dataIndex: 'inn', width: 150 },
    { title: 'Наименование', dataIndex: 'name_short', width: '100%' },
];

const clientsStore = useClientsStore()
const clientHeight = ref(document.documentElement.clientHeight)
const currentClient = reactive({ data:{}, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const openMainDrawer = async (clientId = null) => {
    currentClient.data = { id: null }
    currentClient.modified = false
    mainDrawer.isOpen = true
    if (clientId !== null) {
        try {
            mainDrawer.isLoading = true
            currentClient.data = await clientsStore.getClient(clientId)
        }catch (e) {
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
    currentClient.data = { id: null }
}

const saveClient = async () => {
    mainDrawer.isSaving = true
    try {
        if (currentClient.data.id === null) {
            currentClient.data  = await clientsStore.createClient(currentClient.data)
            currentClient.modified = false
            message.success('Карточка заказчика создана')
            return
        }
        currentClient.data = await clientsStore.storeClient(currentClient.data)
        currentClient.modified = false
        message.success('Карточка заказчика записана')
        closeMainDrawer()
    } catch (e) {
        message.error(`Ошибка. Не удалось ${currentClient.data.id === null ? 'создать' : 'сохранить'} карточку заказчика`)
    } finally {
        mainDrawer.isSaving = false
        await clientsStore.refreshDataList()
    }
}

const deleteClient = async () => {
    if (currentClient.data.id === null) {
        return
    }
    try {
        await clientsStore.deleteClient(currentClient.data.id)
        message.success('Заказчик успешно удален')
        await clientsStore.refreshDataList()
        closeMainDrawer()
    } catch (e) {
        message.error('Ошибка. Не удалось удалить заказчика')
    }
}

const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }

const tableRowFn = record => ({ onClick: () => openMainDrawer(record.id) })

onMounted(() => {
    clientsStore.refreshDataList()
    window.addEventListener('resize', updateClientHeight)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateClientHeight)
})

</script>

<template>
    <Layout title="Заказчики">
        <template #headerExtra><a-button type="primary" @click="() => openMainDrawer()">Новый заказчик</a-button></template>
        <a-table
            :custom-row="tableRowFn"
            :columns="columnsClients"
            :data-source="clientsStore.dataList"
            :pagination="{
                pageSize: clientsStore.paginator.perPage,
                current: clientsStore.paginator.currentPage,
                total: clientsStore.paginator.total,
            }"
            :scroll="{ y: clientHeight - 335 }"
            :row-class-name="() => 'cursor-pointer'"
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'type'">
                    <div :style="{display: 'flex'}">
                        <a-tooltip placement="right">
                            <template v-if="record.type === 'INDIVIDUAL'" #title>Индивидуальный предприниматель</template>
                            <template v-if="record.type === 'LEGAL'" #title>Юридическое лицо</template>

                            <svg v-if="record.type === 'INDIVIDUAL'" :style="{width: '20px', height: '20px', color: '#6b7280'}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                            </svg>
                            <svg v-else-if="record.type === 'LEGAL'" :style="{width: '20px', height: '20px', color: '#6b7280'}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                            </svg>
                        </a-tooltip>
                    </div>

                </template>
            </template>
        </a-table>
        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="saveClient"
            @close="() => {mainDrawer.isOpen = false}"
            @delete="deleteClient"
            :width="736"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`${currentClient.data.id === null ? 'Новый заказчик' : `Заказчик #${currentClient.data.id}`}${currentClient.modified ? '*' : ''}`"
            :ok-text="currentClient.data.id === null ? 'Сохранить' : 'Сохранить и закрыть'"
            :need-delete="currentClient.data.id !== null"
            need-deletion-confirm-text="Вы уверены? Заказчик будет удален!"
            delete-text="Удалить"
        >
            <Client v-model="currentClient.data" :loading="mainDrawer.isLoading" />
        </drawer>
    </Layout>
</template>
