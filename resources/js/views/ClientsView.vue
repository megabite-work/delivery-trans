<script setup>
import {onMounted, onBeforeUnmount, ref, reactive, computed, h} from 'vue';
import { message } from "ant-design-vue";
import dayjs from "dayjs";

import { useClientsStore } from "../stores/models/clients.js";
import { useRegistriesStore } from "../stores/models/registries.js";
import Layout from '../layouts/AppLayout.vue';
import Drawer from "../components/Drawer.vue";
import Client from "../components/models/Client.vue";
import Registry from "../components/models/Registry.vue";

import { DownloadOutlined } from "@ant-design/icons-vue";
import { UserIcon, BuildingOfficeIcon } from '@heroicons/vue/20/solid';
import {managerOrderStatuses, logistOrderStatuses, decl} from "../helpers/index.js";
import {useAuthStore} from "../stores/auth.js";


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
    { key: '__download' }
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

const authStore = useAuthStore()
const clientsStore = useClientsStore()
const registriesStore = useRegistriesStore()

const clientHeight = ref(document.documentElement.clientHeight)
const currentClient = reactive({ data:{ id: null }, modified: false })
const currentRegistry = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })
const registryDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const registrySelectionState = ref({})

const clientHasSelectedOrders = computed(() => (clientId => registrySelectionState.value[clientId] && registrySelectionState.value[clientId].length > 0))
const clientSelectedRowKeys = computed(() => (clientId => {
    if (registrySelectionState.value[clientId]) {
        return registrySelectionState.value[clientId]
    }
    return []
}))
const clientSelectedRowsSum = computed(() => (clientId => {
    return (registrySelectionState.value[clientId] ?? []).reduce((acc, cur) => {
        const c = clientsList.value.find(clientRecord => clientRecord.id === clientId)
        if (c) {
            const o = c.orders.find((order) => order.id === cur)
            if (o) {
                return acc + parseFloat(o.client_sum)
            }
        }
        return acc
    }, 0);
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
        const res = [
            ...c.registries.map(clientRecord => ({
                ...clientRecord,
                key: clientRecord.id,
                date: dayjs(clientRecord.date).format('DD.MM.YYYY'),
                orders_count: clientRecord.orders.length,
                client_sum: parseFloat(clientRecord.client_sum),
                client_paid: parseFloat(clientRecord.client_paid),
            }))
        ]
        if (c.orders.filter(orderRecord => orderRecord.registry_id === null).length > 0) {
            res.unshift({
                key: 0,
                id: 0,
                client_id: clientId,
                status: 'Без реестра',
                orders_count: c.orders.filter(orderRecord => orderRecord.registry_id === null).length,
                client_sum: c.orders.reduce((acc, cur) => {
                    return acc + (cur.registry_id === null ? parseFloat(cur.client_sum) : 0)
                }, 0),
                orders: c.orders.filter(orderRecord => orderRecord.registry_id === null)
            })
        }
        return res
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

const openRegistryDrawer = async (registryId = null, clientId = null) => {
    if (registryId === null && clientId !== null) {
        const c = clientsList.value.find(clientRecord => clientRecord.id === clientId)
        const ro = (registrySelectionState.value[clientId] ?? []).map(oid => {
            if (c) {
                const o = c.orders.find((order) => order.id === oid)
                if (o) {
                    return o
                }
            }
        });
        currentRegistry.data = {
            id: null,
            client_id: clientId,
            date: dayjs(),
            orders: ro,
            vat: c.vat,
            order_ids: registrySelectionState.value[clientId],
            client_sum: clientSelectedRowsSum.value(clientId),
        }
        currentRegistry.modified = false
        registryDrawer.isOpen = true
    }
    if (registryId !== null) {
        try {
            registryDrawer.isLoading = true
            registryDrawer.isOpen = true
            currentRegistry.data = await registriesStore.getRegistry(registryId)
        } catch (e) {
            registryDrawer.isOpen = false
            message.error('Ошибка загрузки')
        } finally {
            registryDrawer.isLoading = false
        }
    }
}

const closeRegistryDrawer = () => {
    registryDrawer.isOpen = false
    currentRegistry.data = { id: null }
}

const saveClient = async () => {
    mainDrawer.isSaving = true
    try {
        if (currentClient.data.id === null) {
            currentClient.data  = await clientsStore.createClient(currentClient.data)
            currentClient.modified = false
            message.success('Карточка заказчика создана')
            if (!authStore.userCan('CLIENTS_EDIT')){
                closeMainDrawer()
            }
            return
        }
        if (authStore.userCan('CLIENTS_EDIT')) {
            currentClient.data = await clientsStore.storeClient(currentClient.data)
            currentClient.modified = false
            message.success('Карточка заказчика записана')
            mainDrawer.isSaving = false
            closeMainDrawer()
        }
    } catch (e) {
        message.error(`Ошибка. Не удалось ${currentClient.data.id === null ? 'создать' : 'сохранить'} карточку заказчика`)
    } finally {
        mainDrawer.isSaving = false
        await clientsStore.refreshDataList()
    }
}

const saveRegistry = async () => {
    registryDrawer.isSaving = true
    try {
        if (currentRegistry.data.id === null && authStore.userCan('CLIENTS_REGISTRIES_CREATE')) {
            currentRegistry.data  = await registriesStore.createRegistry(currentRegistry.data)
            currentRegistry.modified = false
            closeRegistryDrawer()
            message.success('Реестр создан')
            return
        }
        if (authStore.userCan('CLIENTS_REGISTRIES_EDIT')) {
            currentRegistry.data = await registriesStore.storeRegistry(currentRegistry.data)
            currentRegistry.modified = false
            message.success('Изменения записаны')
            registriesStore.isSaving = false
        }
        closeRegistryDrawer()
    } catch (e) {
        message.error(`Ошибка. Не удалось ${currentClient.data.id === null ? 'создать' : 'сохранить'} реестр`)
    } finally {
        registryDrawer.isSaving = false
        await clientsStore.refreshDataList()
    }
}

const deleteClient = async () => {
    if (currentClient.data.id === null || !authStore.userCan('CLIENTS_DELETE')) {
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

const deleteRegistry = async () => {
    if (currentRegistry.data.id === null || !authStore.userCan('CLIENTS_REGISTRIES_DELETE')) {
        return
    }
    try {
        await registriesStore.deleteRegistry(currentRegistry.data.id)
        message.success('Реестр успешно удален')
        await clientsStore.refreshDataList()
        closeRegistryDrawer()
    } catch (e) {
        message.error('Ошибка. Не удалось удалить реестр')
    }
}

const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }

const tableRowFn = record => ({ onClick: () => {
        if (authStore.userCan('CLIENTS_VIEW')){
            openMainDrawer(record.id)
        }
    } })
const registryTableRowFn = record => ({ onClick: () =>
    {
        if (record.id > 0 && authStore.userCan('CLIENTS_REGISTRIES_VIEW')) {
            openRegistryDrawer(record.id)
        }
    }
})

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
            <template v-if="authStore.userCan('CLIENTS_ADD')">
                <a-divider type="vertical" />
                <a-button type="primary" @click="() => openMainDrawer()">Новый заказчик</a-button>
            </template>
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
            <template v-if="authStore.userCan('CLIENTS_REGISTRIES_VIEW')" #expandedRowRender="{ record }">
                <template v-if="record.orders && record.orders.length > 0">
                    <a-table
                        :columns="columnsRegitries"
                        :data-source="registriesList(record.id)"
                        :pagination="false"
                        :custom-row="registryTableRowFn"
                        :row-class-name="() => 'cursor-pointer'"
                        size="small">
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
                                <template v-if="parseFloat(record.client_sum) > 0 && parseFloat(record.client_paid) === 0">
                                    <a-badge status="error" />
                                    Не оплачен
                                </template>
                                <template v-else-if="parseFloat(record.client_sum) > 0 && parseFloat(record.client_sum) === parseFloat(record.client_paid)">
                                    <a-badge status="success" />
                                    Оплачен
                                </template>
                                <template v-else-if="parseFloat(record.client_sum) > 0 && parseFloat(record.client_sum) > parseFloat(record.client_paid)">
                                    <a-badge status="warning" />
                                    Частично оплачен
                                </template>
                                <template v-else-if="parseFloat(record.client_sum) > 0 && parseFloat(record.client_sum) < parseFloat(record.client_paid)">
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
                            <template v-if="column.key === '__download' && authStore.userCan('CLIENTS_REGISTRIES_DOWNLOAD') && record.id !== 0">
                                <a-button :icon="h(DownloadOutlined)" type="dashed" @click="(e) => {e.stopPropagation(); registriesStore.downloadRegistry(record.id)}"/>
                            </template>
                        </template>
                        <template #expandedRowRender="{ record }">
                            <template v-if="record.orders && record.orders.length > 0">
                                <div :style="{ paddingTop: record.id === 0 ? '8px' : '0' }">
                                    <a-table
                                        size="small"
                                        :columns="columnsOrders"
                                        :data-source="registryOrdersList(record)"
                                        :pagination="false"
                                        :row-selection="record.id === 0 && (authStore.userCan('CLIENTS_REGISTRIES_CREATE')) ? { selectedRowKeys: clientSelectedRowKeys(record.client_id), onChange: v => onClientOrderSelectChange(record.client_id, v) } : undefined"
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
                                    <div v-if="record.id === 0" style="margin-top: 30px; margin-left: 32px; margin-bottom: 8px; display: flex; align-items: center;">
                                        <a-button
                                            type="primary"
                                            :disabled="!clientHasSelectedOrders(record.client_id) || !authStore.userCan('CLIENTS_REGISTRIES_CREATE')"
                                            :loading="false"
                                            @click="() => openRegistryDrawer(null, record.client_id)"
                                        >
                                            Создать реестр
                                        </a-button>
                                        <span style="margin-left: 8px">
                                            <template v-if="clientHasSelectedOrders(record.client_id)">
                                              {{ `${decl(clientSelectedRowKeys(record.client_id).length, ['Вабран', 'Вабрано', 'Вабрано'])} ${clientSelectedRowKeys(record.client_id).length} ${decl(clientSelectedRowKeys(record.client_id).length, ['заказ', 'заказа', 'заказов'])}` }}
                                                {{ `на сумму ${clientSelectedRowsSum(record.client_id).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'})}` }}
                                            </template>
                                            <template v-else>
                                                Ничего не выбрано
                                            </template>
                                        </span>
                                    </div>
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
            :width="800"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`${currentClient.data.id === null ? 'Новый заказчик' : `Заказчик #${currentClient.data.id}`}${currentClient.modified ? '*' : ''}`"
            :ok-text="currentClient.data.id === null ? 'Сохранить' : 'Сохранить и закрыть'"
            :need-ok="authStore.userCan('CLIENTS_ADD') || authStore.userCan('CLIENTS_EDIT')"
            :need-delete="authStore.userCan('CLIENTS_DELETE') && currentClient.data.id !== null"
            need-deletion-confirm-text="Вы уверены? Заказчик будет удален!"
            delete-text="Удалить"
        >
            <template v-if="(!authStore.userCan('CLIENTS_EDIT') && currentClient.data.id !== null) || (!authStore.userCan('ORDERS_ADD') && currentClient.data.id === null)" #extra>
                <div style="color: #9ca3af">Только для просмотра</div>
            </template>
            <Client
                v-model="currentClient.data"
                :loading="mainDrawer.isLoading"
                :errors="clientsStore.err?.errors"
                :read-only="(!authStore.userCan('CLIENTS_EDIT') && currentClient.data.id !== null) || (!authStore.userCan('CLIENTS_ADD') && currentClient.data.id === null)"
            />
        </drawer>

        <drawer
            v-model:open="registryDrawer.isOpen"
            @save="saveRegistry"
            @delete="deleteRegistry"
            @close="() => {registryDrawer.isOpen = false}"
            :width="736"
            :loading="registryDrawer.isLoading"
            :saving="registryDrawer.isSaving"
            :ok-loading="registryDrawer.isSaving"
            :title="`${currentRegistry.data.id === null ? 'Новый реестр' : `Реестр #${currentRegistry.data.id}`}${currentRegistry.modified ? '*' : ''}`"
            ok-text="Сохранить и закрыть"
            :need-ok="authStore.userCan('CLIENTS_REGISTRIES_CREATE') || authStore.userCan('CLIENTS_REGISTRIES_EDIT')"
            :need-delete="currentRegistry.data.id !== null && authStore.userCan('CLIENTS_REGISTRIES_DELETE')"
            need-deletion-confirm-text="Вы уверены? Реестр будет удален!"
            delete-text="Удалить"
        >
            <Registry
                v-model="currentRegistry.data"
                :loading="registryDrawer.isLoading"
                :errors="registriesStore.err?.errors"
                :read-only="(!authStore.userCan('CLIENTS_REGISTRIES_EDIT') && currentRegistry.data.id !== null) || (!authStore.userCan('CLIENTS_REGISTRIES_CREATE') && currentRegistry.data.id === null)"
            />
        </drawer>
    </Layout>
</template>
