<script setup>
import {onMounted, onBeforeUnmount, ref, reactive} from 'vue';
import { message } from "ant-design-vue";

import { useClientsStore } from "../stores/models/clients.js";
import Layout from '../layouts/AppLayout.vue';
import Drawer from "../components/Drawer.vue";
import Client from "../components/models/Client.vue";
import { UserIcon, BuildingOfficeIcon } from '@heroicons/vue/20/solid';

const columnsClients = [
    { key: 'type', width: 50 },
    { title: 'ИНН', dataIndex: 'inn', width: 150 },
    { title: 'Наименование', dataIndex: 'name_short', width: '100%' },
];

const clientsStore = useClientsStore()
const clientHeight = ref(document.documentElement.clientHeight)
const currentClient = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const openMainDrawer = async (clientId = null) => {
    currentClient.data = { id: null }
    currentClient.modified = false
    mainDrawer.isOpen = true
    if (clientId !== null) {
        try {
            mainDrawer.isLoading = true
            currentClient.data = await clientsStore.getClient(clientId)
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
        mainDrawer.isSaving = false
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
            :loading="clientsStore.listLoading"
            :custom-row="tableRowFn"
            :columns="columnsClients"
            :data-source="clientsStore.dataList"
            :pagination="{
                ...clientsStore.paginator,
                showSizeChanger: true,
                pageSizeOptions: ['15', '30', '50', '100'],
                style: {marginRight: '10px'},
                buildOptionText: size => `${size.value} / стр.`,
                onChange: page => clientsStore.setPage(page),
                onShowSizeChange: (page, size) => clientsStore.setPageSize(page, size)
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

                            <UserIcon v-if="record.type === 'INDIVIDUAL'" />
                            <BuildingOfficeIcon v-else-if="record.type === 'LEGAL'" />
                        </a-tooltip>
                    </div>

                </template>
            </template>
        </a-table>
        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="saveClient"
            @delete="deleteClient"
            @close="() => {mainDrawer.isOpen = false}"
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
            <Client
                v-model="currentClient.data"
                :loading="mainDrawer.isLoading"
                :errors="clientsStore.clientErr?.errors"
            />
        </drawer>
    </Layout>
</template>
