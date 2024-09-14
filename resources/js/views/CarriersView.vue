<script setup>
import Layout from '../layouts/AppLayout.vue';
import {useCarriersStore} from "../stores/models/carriers.js";
import {computed, h, onBeforeUnmount, onMounted, reactive, ref} from "vue";
import { UserIcon, BuildingOfficeIcon } from '@heroicons/vue/20/solid';
import Drawer from "../components/Drawer.vue";
import {message} from "ant-design-vue";
import Carrier from "../components/models/Carrier.vue";
import {useCarrierRegistriesStore} from "../stores/models/carrierRegistries.js";
import dayjs from "dayjs";
import {decl, logistOrderStatuses, managerOrderStatuses} from "../helpers/index.js";
import CarrierRegistry from "../components/models/CarrierRegistry.vue";
import {useAuthStore} from "../stores/auth.js";
import {DownloadOutlined} from "@ant-design/icons-vue";

const columnsCarriers = [
    { key: 'type', width: 50 },
    { title: 'ИНН', dataIndex: 'inn', width: 150 },
    { title: 'Наименование', dataIndex: 'name_short', width: '100%' },
    { title: 'Заказов', key: 'orders_count', width: 150},
];

const columnsRegitries = [
    { key: 'id', title: 'Номер' },
    { key: 'date', title: 'Дата реестра' },
    { key: 'bill', title: 'Счет' },
    { key: 'orders_count', title: 'Заказов'},
    { key: 'status', title: 'Стaтус' },
    { key: 'carrier_sum', title: 'Сумма, ₽' },
    { key: 'carrier_paid', title: 'Оплачено, ₽' },
    { key: 'carrier_vat', title: 'НДС' },
    { key: '__download' },
];

const columnsOrders = [
    { key: 'id', title: 'Номер' },
    { key: 'created_at', title: 'Дата заказа' },
    { key: 'status_manager', title: 'Статус менеджер' },
    { key: 'status_logist', title: 'Статус логист' },
    { key: 'carrier_sum', title: 'Сумма' },
    { key: 'carrier_vat', title: 'НДС' },
];

const vatArr = ['Без НДС', 'НДС', 'Нал'];

const authStore = useAuthStore()
const carriersStore = useCarriersStore()
const registriesStore = useCarrierRegistriesStore()

const currentCarrier = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })
const currentRegistry = reactive({ data:{ id: null }, modified: false })
const registryDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const registrySelectionState = ref({})
const carrierHasSelectedOrders = computed(() => (carrierId => registrySelectionState.value[carrierId] && registrySelectionState.value[carrierId].length > 0))
const carrierSelectedRowKeys = computed(() => (carrierId => {
    if (registrySelectionState.value[carrierId]) {
        return registrySelectionState.value[carrierId]
    }
    return []
}))
const carriersList = computed(() => {
    return carriersStore.dataList.map(carrier => ({ ...carrier, key: carrier.id}))
})
const carrierSelectedRowsSum = computed(() => (carrierId => {
    return (registrySelectionState.value[carrierId] ?? []).reduce((acc, cur) => {
        const c = carriersList.value.find(carrierRecord => carrierRecord.id === carrierId)
        if (c) {
            const o = c.orders.find((order) => order.id === cur)
            if (o) {
                return acc + parseFloat(o.carrier_sum)
            }
        }
        return acc
    }, 0);
}))
const onCarrierOrderSelectChange = (carrierId, selectedRowKeys) => {
    registrySelectionState.value[carrierId] = selectedRowKeys
};

const registriesList = computed(() => (carrierId => {
    const c = carriersList.value.find(carrierRecord => carrierRecord.id === carrierId)
    if (c) {
        const res = [
            ...c.registries.map(carrierRecord => ({
                ...carrierRecord,
                key: carrierRecord.id,
                date: dayjs(carrierRecord.date).format('DD.MM.YYYY'),
                orders_count: carrierRecord.orders.length,
                client_sum: parseFloat(carrierRecord.carrier_sum),
                client_paid: parseFloat(carrierRecord.carrier_paid),
            }))
        ]
        if (c.orders.filter(orderRecord => orderRecord.carrier_registry_id === null).length > 0) {
            res.unshift({
                key: 0,
                id: 0,
                carrier_id: carrierId,
                status: 'Без реестра',
                orders_count: c.orders.filter(orderRecord => orderRecord.carrier_registry_id === null).length,
                carrier_sum: c.orders.reduce((acc, cur) => {
                    return acc + (cur.carrier_registry_id === null ? parseFloat(cur.carrier_sum) : 0)
                }, 0),
                orders: c.orders.filter(orderRecord => orderRecord.carrier_registry_id === null)
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
            carrier_sum: parseFloat(o.carrier_sum),
        }))
    }
    return null
}))

const openMainDrawer = async (carrierId = null) => {
    currentCarrier.data = { id: null, is_active: true }
    currentCarrier.modified = false
    mainDrawer.isOpen = true
    if (carrierId !== null) {
        try {
            mainDrawer.isLoading = true
            currentCarrier.data = await carriersStore.getCarrier(carrierId)
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
    currentCarrier.data = { id: null, is_active: true }
}

const openRegistryDrawer = async (registryId = null, carrierId = null) => {
    if (registryId === null && carrierId !== null) {
        const c = carriersList.value.find(carrierRecord => carrierRecord.id === carrierId)
        const ro = (registrySelectionState.value[carrierId] ?? []).map(oid => {
            if (c) {
                const o = c.orders.find((order) => order.id === oid)
                if (o) {
                    return o
                }
            }
        });
        currentRegistry.data = {
            id: null,
            carrier_id: carrierId,
            date: dayjs(),
            orders: ro,
            vat: c.vat,
            order_ids: registrySelectionState.value[carrierId],
            carrier_sum: carrierSelectedRowsSum.value(carrierId),
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

const saveCarrier = async () => {
    mainDrawer.isSaving = true
    try {
        if (currentCarrier.data.id === null) {
            currentCarrier.data  = await carriersStore.createCarrier(currentCarrier.data)
            currentCarrier.modified = false
            message.success('Карточка перевозчика создана')
            if (!authStore.userCan('CARRIERS_EDIT')){
                closeMainDrawer()
            }
            return
        }
        if (authStore.userCan('CARRIERS_EDIT')) {
            currentCarrier.data = await carriersStore.storeCarrier(currentCarrier.data)
            currentCarrier.modified = false
            message.success('Карточка перевозчика записана')
            mainDrawer.isSaving = false
            closeMainDrawer()
        }
    } catch (e) {
        message.error(`Ошибка. Не удалось ${currentCarrier.data.id === null ? 'создать' : 'сохранить'} карточку перевозчика`)
    } finally {
        mainDrawer.isSaving = false
        await carriersStore.refreshDataList()
    }
}

const saveRegistry = async () => {
    registryDrawer.isSaving = true
    try {
        if (currentRegistry.data.id === null && authStore.userCan('CARRIERS_REGISTRIES_CREATE')) {
            currentRegistry.data  = await registriesStore.createRegistry(currentRegistry.data)
            currentRegistry.modified = false
            closeRegistryDrawer()
            message.success('Реестр создан')
            return
        }
        if (authStore.userCan('CARRIERS_REGISTRIES_EDIT')) {
            currentRegistry.data.order_ids = currentRegistry.data.orders.map((order) => order.id)
            currentRegistry.data = await registriesStore.storeRegistry(currentRegistry.data)
            currentRegistry.modified = false
            message.success('Изменения записаны')
            registriesStore.isSaving = false
        }
        closeRegistryDrawer()
    } catch (e) {
        message.error(`Ошибка. Не удалось ${currentCarrier.data.id === null ? 'создать' : 'сохранить'} реестр`)
    } finally {
        registryDrawer.isSaving = false
        await carriersStore.refreshDataList()
    }
}

const deleteCarrier = async () => {
    if (currentCarrier.data.id === null || !authStore.userCan('CARRIERS_DELETE')) {
        return
    }
    try {
        await carriersStore.deleteCarrier(currentCarrier.data.id)
        message.success('Перевозчик успешно удален')
        await carriersStore.refreshDataList()
        closeMainDrawer()
    } catch (e) {
        message.error('Ошибка. Не удалось удалить перевозчика')
    }
}

const deleteRegistry = async () => {
    if (currentRegistry.data.id === null || !authStore.userCan('CARRIERS_REGISTRIES_DELETE')) {
        return
    }
    try {
        await registriesStore.deleteRegistry(currentRegistry.data.id)
        message.success('Реестр успешно удален')
        await carriersStore.refreshDataList()
        closeRegistryDrawer()
    } catch (e) {
        message.error('Ошибка. Не удалось удалить реестр')
    }
}

const clientHeight = ref(document.documentElement.clientHeight)
const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }
const tableRowFn = record => ({ onClick: () => {
        if (authStore.userCan('CARRIERS_VIEW')){
            openMainDrawer(record.id)
        }
    } })
const registryTableRowFn = record => ({ onClick: () =>
    {
        if (record.id > 0 && authStore.userCan('CARRIERS_REGISTRIES_VIEW')) {
            openRegistryDrawer(record.id)
        }
    }
})
onMounted(() => {
    carriersStore.refreshDataList()
    window.addEventListener('resize', updateClientHeight)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateClientHeight)
})
</script>

<template>
    <Layout title="Перевозчики">
        <template #headerExtra>
            <a-input-search
                v-model:value="carriersStore.filter"
                placeholder="Поиск по перевозчикам"
                style="width: 400px"
                :allow-clear="true"
                @search="carriersStore.applyFilter()"
            />
            <template v-if="authStore.userCan('CARRIERS_VIEW')">
                <a-divider type="vertical" />
                <a-button type="primary" @click="() => openMainDrawer()">Новый перевозчик</a-button>
            </template>
        </template>
        <a-table
            :loading="carriersStore.listLoading"
            :custom-row="tableRowFn"
            :columns="columnsCarriers"
            :data-source="carriersList"
            :pagination="{
                ...carriersStore.paginator,
                showSizeChanger: true,
                pageSizeOptions: ['15', '30', '50', '100'],
                style: {marginRight: '10px'},
                buildOptionText: size => `${size.value} / стр.`,
                onChange: page => carriersStore.setPage(page),
                onShowSizeChange: (page, size) => carriersStore.setPageSize(page, size)
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

                            <UserIcon v-if="record.type === 'INDIVIDUAL'" :style="{ position: 'relative' }" />
                            <BuildingOfficeIcon v-else-if="record.type === 'LEGAL'" :style="{ position: 'relative' }" />
                        </a-tooltip>
                    </div>
                </template>
                <template v-if="column.key === 'orders_count'">
                    {{ record.orders.length === 0 ? '–' : record.orders.length}}
                </template>
            </template>
            <template v-if="authStore.userCan('CARRIERS_REGISTRIES_VIEW')" #expandedRowRender="{ record }">
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
                            <template v-if="column.key === 'bill'">
                                <template v-if="record.id !== 0">
                                    {{ record.bill_number ? `#${record.bill_number}` : 'б/н' }}{{ record.bill_date ? ` от ${dayjs(record.bill_date).format('DD.MM.YY')}` : '' }}
                                </template>
                                <template v-else>
                                    –
                                </template>
                            </template>
                            <template v-if="column.key === 'orders_count'">
                                {{ record.orders_count }}
                            </template>
                            <template v-if="column.key === 'status'">
                                <template v-if="parseFloat(record.carrier_sum > 0) && parseFloat(record.carrier_paid) === 0">
                                    <a-badge status="error" />
                                    Не оплачен
                                </template>
                                <template v-else-if="parseFloat(record.carrier_sum > 0) && parseFloat(record.carrier_sum) === parseFloat(record.carrier_paid)">
                                    <a-badge status="success" />
                                    Оплачен
                                </template>
                                <template v-else-if="parseFloat(record.carrier_sum > 0) && parseFloat(record.carrier_sum) > parseFloat(record.carrier_paid)">
                                    <a-badge status="warning" />
                                    Частично оплачен
                                </template>
                                <template v-else-if="parseFloat(record.carrier_sum > 0) && parseFloat(record.carrier_sum) < parseFloat(record.carrier_paid)">
                                    <a-badge color="blue" />
                                    Переплата
                                </template>
                                <template v-else>
                                    <a-badge status="default" />
                                    Нет статуса
                                </template>
                            </template>
                            <template v-if="column.key === 'carrier_sum'">
                                {{ parseFloat(record.carrier_sum).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
                            </template>
                            <template v-if="column.key === 'carrier_paid'">
                                <template v-if="record.id !== 0">
                                    {{ parseFloat(record.carrier_paid).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
                                </template>
                                <template v-else>–</template>
                            </template>
                            <template v-if="column.key === 'carrier_vat'">
                                <template v-if="record.id !== 0">
                                    {{ vatArr[record.vat] }}
                                </template>
                                <template v-else>–</template>
                            </template>
                            <template v-if="column.key === '__download' && authStore.userCan('CARRIERS_REGISTRIES_DOWNLOAD') && record.id !== 0">
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
                                        :row-selection="record.id === 0 && (authStore.userCan('CARRIERS_REGISTRIES_CREATE')) ? { selectedRowKeys: carrierSelectedRowKeys(record.carrier_id), onChange: v => onCarrierOrderSelectChange(record.carrier_id, v) } : undefined"
                                    >
                                        <template #bodyCell="{ column, record }">
                                            <template v-if="column.key === 'carrier_sum'">
                                                {{ parseFloat(record.carrier_sum).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
                                            </template>
                                            <template v-else-if="column.key === 'status_manager'">
                                                <a-badge :color="managerOrderStatuses[record.status_manager.status].color" />
                                                {{ managerOrderStatuses[record.status_manager.status].label }}
                                            </template>
                                            <template v-else-if="column.key === 'status_logist'">
                                                <a-badge :color="logistOrderStatuses[record.status_logist.status].color" />
                                                {{ logistOrderStatuses[record.status_logist.status].label }}
                                            </template>
                                            <template v-else-if="column.key === 'carrier_vat'">
                                                {{ vatArr[record.carrier_vat] }}
                                            </template>
                                            <template v-else>
                                                {{ record[column.key] }}
                                            </template>
                                        </template>
                                    </a-table>
                                    <div v-if="record.id === 0" style="margin-top: 30px; margin-left: 32px; margin-bottom: 8px; display: flex; align-items: center;">
                                        <a-button
                                            type="primary"
                                            :disabled="!carrierHasSelectedOrders(record.carrier_id) || !authStore.userCan('CARRIERS_REGISTRIES_CREATE')"
                                            :loading="false"
                                            @click="() => openRegistryDrawer(null, record.carrier_id)"
                                        >
                                            Создать реестр
                                        </a-button>
                                        <span style="margin-left: 8px">
                                            <template v-if="carrierHasSelectedOrders(record.carrier_id)">
                                              {{ `${decl(carrierSelectedRowKeys(record.carrier_id).length, ['Вабран', 'Вабрано', 'Вабрано'])} ${carrierSelectedRowKeys(record.carrier_id).length} ${decl(carrierSelectedRowKeys(record.carrier_id).length, ['заказ', 'заказа', 'заказов'])}` }}
                                                {{ `на сумму ${carrierSelectedRowsSum(record.carrier_id).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'})}` }}
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
                    У перевозчика нет заказов
                </template>
            </template>
        </a-table>
        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="saveCarrier"
            @delete="deleteCarrier"
            @close="() => {mainDrawer.isOpen = false}"
            :width="736"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`${currentCarrier.data.id === null ? 'Новый перевозчик' : `Перевозчик #${currentCarrier.data.id}`}${currentCarrier.modified ? '*' : ''}`"
            :ok-text="currentCarrier.data.id === null ? 'Сохранить' : 'Сохранить и закрыть'"
            :need-ok="authStore.userCan('CARRIERS_ADD') || authStore.userCan('CARRIERS_EDIT')"
            :need-delete="authStore.userCan('CARRIERS_DELETE') && currentCarrier.data.id !== null"
            need-deletion-confirm-text="Вы уверены? Перевозчик будет удален!"
            delete-text="Удалить"
        >
            <template v-if="(!authStore.userCan('CARRIERS_EDIT') && currentCarrier.data.id !== null) || (!authStore.userCan('CARRIERS_ADD') && currentCarrier.data.id === null)" #extra>
                <div style="color: #9ca3af">Только для просмотра</div>
            </template>
            <Carrier
                v-model="currentCarrier.data"
                :loading="mainDrawer.isLoading"
                :errors="carriersStore.err?.errors"
                :read-only="(!authStore.userCan('CARRIERS_EDIT') && currentCarrier.data.id !== null) || (!authStore.userCan('CARRIERS_ADD') && currentCarrier.data.id === null)"
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
            :need-ok="authStore.userCan('CARRIERS_REGISTRIES_CREATE') || authStore.userCan('CARRIERS_REGISTRIES_EDIT')"
            :title="`${currentRegistry.data.id === null ? 'Новый реестр' : `Реестр #${currentRegistry.data.id}`}${currentRegistry.modified ? '*' : ''}`"
            ok-text="Сохранить и закрыть"
            :need-delete="currentRegistry.data.id !== null && authStore.userCan('CARRIERS_REGISTRIES_DELETE')"
            need-deletion-confirm-text="Вы уверены? Реестр будет удален!"
            delete-text="Удалить"
        >
            <template v-if="authStore.userCan('CARRIERS_REGISTRIES_DOWNLOAD') && currentRegistry.data.id !== 0" #extra>
                <a-button :icon="h(DownloadOutlined)" type="dashed" @click="(e) => {e.stopPropagation(); registriesStore.downloadRegistry(currentRegistry.data.id)}">Скачать</a-button>
            </template>
            <CarrierRegistry
                v-model="currentRegistry.data"
                :loading="registryDrawer.isLoading"
                :errors="registriesStore.err?.errors"
                :read-only="(!authStore.userCan('CARRIERS_REGISTRIES_EDIT') && currentRegistry.data.id !== null) || (!authStore.userCan('CARRIERS_REGISTRIES_CREATE') && currentRegistry.data.id === null)"
            />
        </drawer>
    </Layout>
</template>
