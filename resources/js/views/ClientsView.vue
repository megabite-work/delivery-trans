<script setup>
import {onMounted, onBeforeUnmount, ref, reactive, computed} from 'vue';
import { message } from "ant-design-vue";

import { useClientsStore } from "../stores/models/clients.js";
import Layout from '../layouts/AppLayout.vue';
import Drawer from "../components/Drawer.vue";
import Client from "../components/models/Client.vue";
import { UserIcon, BuildingOfficeIcon } from '@heroicons/vue/20/solid';
import {managerOrderStatuses, logistOrderStatuses, decl} from "../helpers/index.js";

import dayjs from "dayjs";

const columnsClients = [
    { key: 'type', width: 50 },
    { title: 'ИНН', dataIndex: 'inn', width: 150 },
    { title: 'Наименование', dataIndex: 'name_short', width: '100%' },
    { title: 'Заказов', key: 'orders_count', width: 150},
];

const columnsRegitries = [
    { key: 'id', title: '#' },
    { key: 'date', title: 'Дата реестра' },
    { key: 'orders_count', title: 'Заказов'},
    { key: 'status', title: 'Стaтус' },
    { key: 'client_sum', title: 'Сумма, ₽' },
    { key: 'client_paid', title: 'Оплачено, ₽' },
    { key: 'client_vat', title: 'НДС' },
];

const columnsOrders = [
    { key: 'id', title: '#' },
    { key: 'created_at', title: 'Дата заказа' },
    { key: 'status_manager', title: 'Статус менеджер' },
    { key: 'status_logist', title: 'Статус логист' },
    { key: 'client_sum', title: 'Сумма заказа' },
    { key: 'client_vat', title: 'НДС' },
];

const vatArr = ['Без НДС', 'НДС', 'Нал'];
const clientsStore = useClientsStore()
const clientHeight = ref(document.documentElement.clientHeight)
const currentClient = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })
const registrySelectionState = ref({})

const clientHasSelectedOrders = computed(() => (clientId => registrySelectionState.value[clientId] && registrySelectionState.value[clientId].length > 0))
const clientSelectedRowKeys = computed(() => (clientId => {
    if (registrySelectionState.value[clientId]) {
        return registrySelectionState.value[clientId]
    }
    return []
}))
const onClientOrderSelectChange = (clientId, selectedRowKeys) => {
    registrySelectionState.value[clientId] = selectedRowKeys
};

const clientsList = computed(() => {
    return clientsStore.dataList.map(client => ({ ...client, key: client.id}))
})

const registriesList = computed(() => (clientId => {
    const c = clientsList.value.find(clientRecord => clientRecord.id === clientId)
    if (c) {
        return [
            {
                key: 0,
                id: 0,
                client_id: clientId,
                status: 'Без реестра',
                orders_count: c.orders.filter(orderRecord => orderRecord.registry_id === null).length,
                client_sum: c.orders.reduce((acc, cur) => acc + cur.registry_id === null ? 0 : cur.client_sum, 0),
                orders: c.orders.filter(orderRecord => orderRecord.registry_id === null)
            },
            ...c.registries.map(clientRecord => ({
                ...clientRecord,
                key: clientRecord.id,
                date: dayjs(clientRecord.date).format('DD.MM.YYYY'),
                orders_count: clientRecord.orders.length,
                client_sum: parseFloat(clientRecord.client_sum),
                client_paid: parseFloat(clientRecord.client_paid),
            }))
        ]
    }
    return [];
}))

const registryOrdersList = computed(() => (registry => {
    if (registry) {
        return registry.orders.map(o => ({
            ...o,
            key: o.id,
            created_at: dayjs(o.created_at).format('DD.MM.YYYY HH:mm'),
            client_sum: parseFloat(o.client_sum),
        }))
    }
    return null
}))

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
        <template #headerExtra>
            <a-input-search
                v-model:value="clientsStore.filter"
                placeholder="Поиск по заказчикам"
                style="width: 400px"
                :allow-clear="true"
                @search="clientsStore.applyFilter()"
            />
            <a-divider type="vertical" />
            <a-button type="primary" @click="() => openMainDrawer()">Новый заказчик</a-button>
        </template>
        <a-table
            :loading="clientsStore.listLoading"
            :custom-row="tableRowFn"
            :columns="columnsClients"
            :data-source="clientsList"
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
                <template v-if="column.key === 'orders_count'">
                    {{ record.orders.length === 0 ? '–' : record.orders.length}}
                </template>
            </template>
            <template #expandedRowRender="{ record }">
                <template v-if="record.orders && record.orders.length > 0">
                    <a-table :columns="columnsRegitries" :data-source="registriesList(record.id)" :pagination="false" size="small">
                        <template #bodyCell="{ column, record }">
                            <template v-if="column.key === 'id'">
                                <template v-if="record.id !== 0">
                                    {{ record.id }}
                                </template>
                            </template>
                            <template v-if="column.key === 'date'">
                                <template v-if="record.id !== 0">
                                    {{ record.date }}
                                </template>
                                <template v-else>
                                    Заказы без реестра
                                </template>
                            </template>
                            <template v-if="column.key === 'orders_count'">
                                {{ record.orders_count }}
                            </template>
                            <template v-if="column.key === 'status'">
                                <template v-if="record.client_sum > 0 && record.client_paid === 0">
                                    <a-badge status="error" />
                                    Не оплачен
                                </template>
                                <template v-else-if="record.client_sum > 0 && record.client_sum === record.client_paid">
                                    <a-badge status="success" />
                                    Оплачен
                                </template>
                                <template v-else-if="record.client_sum > 0 && record.client_sum > record.client_paid">
                                    <a-badge status="warning" />
                                    Частично оплачен
                                </template>
                                <template v-else-if="record.client_sum > 0 && record.client_sum < record.client_paid">
                                    <a-badge color="blue" />
                                    Переплата
                                </template>
                                <template v-else>
                                    <a-badge status="default" />
                                    Нет статуса
                                </template>
                            </template>
                            <template v-if="column.key === 'client_sum'">
                                {{ parseFloat(record.client_sum).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
                            </template>
                            <template v-if="column.key === 'client_paid'">
                                <template v-if="record.id !== 0">
                                    {{ parseFloat(record.client_paid).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
                                </template>
                                <template v-else>–</template>
                            </template>
                            <template v-if="column.key === 'client_vat'">
                                <template v-if="record.id !== 0">
                                    {{ vatArr[record.vat] }}
                                </template>
                                <template v-else>–</template>
                            </template>
                        </template>
                        <template #expandedRowRender="{ record }">
                            <template v-if="record.orders && record.orders.length > 0">
                                <div style="padding: 0 40px 25px;">
                                    <div v-if="record.id === 0" style="margin-bottom: 16px">
                                        <a-button
                                            type="primary"
                                            :disabled="!clientHasSelectedOrders(record.client_id)"
                                            :loading="false"
                                            @click="() => {}"
                                        >
                                            Создать реестр
                                        </a-button>
                                        <span style="margin-left: 8px">
                                            <template v-if="clientHasSelectedOrders(record.client_id)">
                                              {{ `${decl(clientSelectedRowKeys(record.client_id).length, ['Вабран', 'Вабрано', 'Вабрано'])} ${clientSelectedRowKeys(record.client_id).length} ${decl(clientSelectedRowKeys(record.client_id).length, ['заказ', 'заказа', 'заказов'])}` }}
                                            </template>
                                        </span>
                                    </div>
                                    <a-table
                                        size="small"
                                        :columns="columnsOrders"
                                        :data-source="registryOrdersList(record)"
                                        :pagination="false"
                                        :row-selection="record.id === 0 ? { selectedRowKeys: clientSelectedRowKeys(record.client_id), onChange: v => onClientOrderSelectChange(record.client_id, v) } : undefined"
                                    >
                                        <template #bodyCell="{ column, record }">
                                            <template v-if="column.key === 'client_sum'">
                                                {{ parseFloat(record.client_sum).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
                                            </template>
                                            <template v-else-if="column.key === 'status_manager'">
                                                <a-badge :color="managerOrderStatuses[record.status_manager.status].color" />
                                                {{ managerOrderStatuses[record.status_manager.status].label }}
                                            </template>
                                            <template v-else-if="column.key === 'status_logist'">
                                                <a-badge :color="logistOrderStatuses[record.status_logist.status].color" />
                                                {{ logistOrderStatuses[record.status_logist.status].label }}
                                            </template>
                                            <template v-else-if="column.key === 'client_vat'">
                                                {{ vatArr[record.client_vat] }}
                                            </template>
                                            <template v-else>
                                                {{ record[column.key] }}
                                            </template>
                                        </template>
                                    </a-table>
                                </div>
                            </template>
                        </template>
                    </a-table>
                </template>
                <template v-else>
                    У клиента нет заказов
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
