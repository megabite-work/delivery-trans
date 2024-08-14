<script setup>
import Layout from '@/layouts/AppLayout.vue';
import {computed, createVNode, h, onBeforeUnmount, onMounted, reactive, ref} from "vue";
import {ArrowRightOutlined, ExclamationCircleOutlined, FilterOutlined, SearchOutlined} from "@ant-design/icons-vue";
import {message, Modal} from "ant-design-vue";
import {useOrdersStore} from "../stores/models/orders.js";
import Drawer from "../components/Drawer.vue";
import Order from "../components/models/Order.vue";
import {isArray} from "radash";
import dayjs from "dayjs";
import {logistOrderStatuses, managerOrderStatuses} from "../helpers/index.js";
import {useAuthStore} from "../stores/auth.js";
import {permissionColumns} from "../helpers/permissions.js";

const showModalCloseConfirm = () => {
    Modal.confirm({
        title: 'Закрыть заявку',
        icon: createVNode(ExclamationCircleOutlined),
        content: createVNode('div', { style: 'color:red;' }, 'Заявка не была сохранена. Закрыть ее?'),
        okText: 'Да, закрыть',
        onOk() {
            mainDrawer.isOpen = false
        }
    });
};

const authStore = useAuthStore()
const ordersStore = useOrdersStore()
const clientHeight = ref(document.documentElement.clientHeight)
const currentOrder = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })
const listLoading = ref(false)
const filterOpen = ref(false)

const openMainDrawer = async (orderId = null) => {
    currentOrder.data = { id: null }
    currentOrder.modified = false
    mainDrawer.isOpen = true
    if (orderId !== null) {
        try {
            mainDrawer.isLoading = true
            currentOrder.data = await ordersStore.getOrder(orderId)
        } catch (e) {
            mainDrawer.isOpen = false
            message.error('Ошибка загрузки')
            console.log(e)
        } finally {
            mainDrawer.isLoading = false
        }
    }
}

const closeMainDrawer = () => {
    if (mainDrawer.isSaving) {
        return
    }
    if (currentOrder.data.id === null && Object.keys(currentOrder.data).length > 1) {
        showModalCloseConfirm()
        return;
    }
    mainDrawer.isOpen = false
    currentOrder.data = { id: null }
}

const saveOrder = async () => {
    mainDrawer.isSaving = true
    try {
        if (currentOrder.data.id === null) {
            currentOrder.data = await ordersStore.createOrder(currentOrder.data)
            currentOrder.modified = false
            message.success('Заказ создан')
            return
        }
        currentOrder.data = await ordersStore.storeOrder(currentOrder.data)
        currentOrder.modified = false
        mainDrawer.isSaving = false
        closeMainDrawer()
    } catch {
        message.error('Ошибка. Не удалось записать заказ')
    } finally {
        mainDrawer.isSaving = false
        await ordersStore.refreshDataList()
    }
}

const deleteOrder = async () => {
    if (currentOrder.data.id === null) {
        return
    }
    try {
        await ordersStore.deleteOrder(currentOrder.data.id)
        message.success('Заказ успешно удален')
        closeMainDrawer()
        await ordersStore.refreshDataList()
    } catch {
        message.error('Ошибка. Не удалось удалить заказ')
    }
}

const setOrderStatus = async (orderId, statusType, status) => {
    try {
        listLoading.value = true
        await ordersStore.setOrderStatus(orderId, statusType, status)
        await ordersStore.refreshDataList()
    } catch {
        message.error("Не удалось устаноывить статус заказа")
    } finally {
        listLoading.value = false
    }
}

const columns = computed(() => {
    return ordersStore.columnsOrders.filter(col => {
        if (authStore.currentRole.permissions.includes("ALL")) {
            return true
        }
        if (col.key === 'id') {
            return true
        }
        if (!col.key) {
            return true
        }
        let colsFromRole = []
        authStore.currentRole.permissions.forEach(perm => {
            if (Object.keys(permissionColumns).includes(perm)) {
                colsFromRole = [...colsFromRole, ...permissionColumns[perm]]
            }
        })
        return colsFromRole.includes(col.key)
    })
})

const handleTableChange = async (pag, filters, sorter) => {
    await ordersStore.setSorter(sorter.column ? sorter.column.key : undefined, sorter.order)
}

const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }
const tableRowFn = record => ({ onClick: () => openMainDrawer(record.id) })

onMounted(() => {
    ordersStore.refreshDataList()
    window.addEventListener('resize', updateClientHeight)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateClientHeight)
})
</script>

<template>
    <Layout title="Заявки">
        <template #headerExtra>
            <a-badge :dot="ordersStore.filter.isFiltered">
                <a-button :type="filterOpen ? 'primary' : 'dashed'" :icon="h(FilterOutlined)" @click="() => filterOpen = !filterOpen">Фильтры</a-button>
            </a-badge>
            <a-button type="primary" @click="() => openMainDrawer()">Новая заявка</a-button>
        </template>
        <div v-if="filterOpen" style="padding: 20px 24px; background-color: #fafafa; border-bottom: 1px solid #f0f0f0">
            <a-form layout="inline" style="width: 600px">
                <div style="display: flex; flex-direction: column; gap: 16px; width: 100%">
                    <div style="display: flex; width: 100%">
                        <a-form-item label="Номер">
                            <a-input-number min="1" v-model:value="ordersStore.filter.id" placeholder="#"/>
                        </a-form-item>
                        <a-form-item :colon="false">
                            <template #label>
                                <search-outlined />
                            </template>
                            <a-input v-model:value="ordersStore.filter.text" placeholder="Поисковая строка" style="width: 395px"/>
                        </a-form-item>
                    </div>
                    <div>
                        <a-form-item label="Дата заявки">
                            <a-range-picker v-model:value="ordersStore.filter.date" style="width: 100%" />
                        </a-form-item>
                    </div>
                    <a-divider style="margin: 0"/>
                    <div>
                        <a-form-item label="Статус менеджера">
                            <a-select style="width: 100%" placeholder="Статус менеджера" v-model:value="ordersStore.filter.status_manager" :allow-clear="true">
                                <a-select-option :value="idx" v-for="(mStatus, idx) in managerOrderStatuses" :key="idx">
                                    <div style="display: flex; flex-direction: row; align-items: center">
                                        <div :style="{
                                                width: '12px',
                                                height: '12px',
                                                backgroundColor: mStatus.color,
                                                borderRadius: '8px',
                                                marginRight: '8px'
                                                }"></div>
                                        {{mStatus.label}}
                                    </div>
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </div>
                    <div>
                        <a-form-item label="Дата статуса менеджера">
                            <a-range-picker v-model:value="ordersStore.filter.status_manager_date" style="width: 100%" />
                        </a-form-item>
                    </div>
                    <a-divider style="margin: 0"/>
                    <div>
                        <a-form-item label="Статус логиста">
                            <a-select style="width: 100%" placeholder="Статус логиста" v-model:value="ordersStore.filter.status_logist" :allow-clear="true">
                                <a-select-option :value="idx" v-for="(lStatus, idx) in logistOrderStatuses" :key="idx">
                                    <div style="display: flex; flex-direction: row; align-items: center">
                                        <div :style="{
                                            width: '12px',
                                            height: '12px',
                                            backgroundColor: lStatus.color,
                                            borderRadius: '8px',
                                            marginRight: '8px'
                                            }"></div>
                                        {{lStatus.label}}
                                    </div>
                                </a-select-option>
                            </a-select>
                        </a-form-item>
                    </div>
                    <div>
                        <a-form-item label="Дата статуса логиста">
                            <a-range-picker v-model:value="ordersStore.filter.status_logist_date" style="width: 100%" />
                        </a-form-item>
                    </div>
                    <a-divider style="margin: 0"/>
                    <div>
                        <a-form-item label="Дата поездки">
                            <a-range-picker v-model:value="ordersStore.filter.arrive_date" style="width: 100%" />
                        </a-form-item>
                    </div>
                    <div style="display: flex; gap: 8px; justify-content: right">
                        <a-button @click="ordersStore.resetFilter">Сбросить фильтр</a-button>
                        <a-button type="primary" @click="ordersStore.applyFilter">Применить</a-button>
                    </div>
                </div>
            </a-form>
        </div>
        <a-table
            :loading="ordersStore.listLoading || listLoading"
            :custom-row="tableRowFn"
            :columns="columns"
            :data-source="ordersStore.dataList"
            :pagination="{
                ...ordersStore.paginator,
                showSizeChanger: true,
                pageSizeOptions: ['15', '30', '50', '100'],
                style: {marginRight: '10px'},
                buildOptionText: size => `${size.value} / стр.`,
                onChange: page => ordersStore.setPage(page),
                onShowSizeChange: (page, size) => ordersStore.setPageSize(page, size)
            }"
            :scroll="{ y: clientHeight - 335, x: 1500 }"
            :row-class-name="() => 'cursor-pointer'"
            :row-expandable="record => (record.from_locations && record.from_locations.length > 0) || (record.to_locations && record.to_locations.length > 0)"
            row-key="id"
            expand-fixed="id"
            @change="handleTableChange"
            size="small"
        >
            <template #headerCell="cell">
                <div v-if="!isArray(cell.title)" style="font-size: 12px; white-space: nowrap; text-align: center">
                    {{cell.title}}
                </div>
            </template>
            <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'id'">
                    <div style="text-align: right; font-size: 12px">{{ record.id }}</div>
                </template>
                <template v-if="column.key === 'created_at' || column.key === 'updated_at' || column.key === 'started_at'">
                    <div v-if="record[column.key]" style="font-size: 12px; text-align: right">
                        {{ dayjs(record[column.key]).format('DD.MM.YY HH:mm') }}
                    </div>
                    <div v-else style="font-size: 12px; text-align: center">–</div>
                </template>
                <template v-if="column.key === 'status_logist'">
                    <a-dropdown v-if="record.status_logist" :trigger="['contextmenu']">
                        <div
                            style="font-size: 12px; border-radius: 4px; text-align: center; padding: 3px 3px"
                            :style="{
                            color: logistOrderStatuses[record.status_logist.status].color,
                            backgroundColor: logistOrderStatuses[record.status_logist.status].backgroundColor,
                        }">
                            {{ logistOrderStatuses[record.status_logist.status].label }}
                        </div>
                        <template v-if="authStore.userCan('ORDER_CARRIER_STATUS_CHANGE')" #overlay>
                            <a-menu>
                                <template v-for="(v, key) in logistOrderStatuses">
                                    <a-menu-item v-if="key !== record.status_logist.status" @click="() => setOrderStatus(record.id, 'LOGIST', key)">
                                        <div style="display: flex; flex-direction: row; align-items: center">
                                            <div :style="{
                                            width: '12px',
                                            height: '12px',
                                            backgroundColor: v.color,
                                            borderRadius: '8px'
                                            }"></div>
                                            <div style="padding-left: 8px">{{ v.label }}</div>
                                        </div>
                                    </a-menu-item>
                                </template>
                            </a-menu>
                        </template>
                    </a-dropdown>
                    <div v-else style="font-size: 12px; text-align: center">–</div>
                </template>
                <template v-if="column.key === 'status_manager'">
                    <a-dropdown v-if="record.status_manager" :trigger="['contextmenu']">
                        <div
                            style="font-size: 12px; border-radius: 4px; text-align: center; padding: 3px 3px"
                            :style="{
                            color: managerOrderStatuses[record.status_manager.status].color,
                            backgroundColor: managerOrderStatuses[record.status_manager.status].backgroundColor,
                        }">
                            {{ managerOrderStatuses[record.status_manager.status].label }}
                        </div>
                        <template v-if="authStore.userCan('ORDER_MANAGER_STATUS_CHANGE')" #overlay>
                            <a-menu>
                                <template v-for="(v, key) in managerOrderStatuses">
                                    <a-menu-item v-if="key !== record.status_manager.status" @click="() => setOrderStatus(record.id, 'MANAGER', key)">
                                        <div style="display: flex; flex-direction: row; align-items: center">
                                            <div :style="{
                                            width: '12px',
                                            height: '12px',
                                            backgroundColor: v.color,
                                            borderRadius: '8px'
                                            }"></div>
                                            <div style="padding-left: 8px">{{ v.label }}</div>
                                        </div>
                                    </a-menu-item>
                                </template>
                            </a-menu>
                        </template>
                    </a-dropdown>
                    <div v-else style="font-size: 12px; text-align: center">–</div>
                </template>
                <template v-if="column.key === 'client'">
                    <div style="text-align: right; font-size: 12px">{{ record.client ? record.client.name_short : '–' }}</div>
                </template>
                <template v-if="column.key === 'carrier'">
                    <div style="text-align: right; font-size: 12px">{{ record.carrier ? record.carrier.name_short : '–' }}</div>
                </template>
                <template v-if="column.key === 'driver'">
                    <div style="text-align: right; font-size: 12px">
                        {{ record.carrier_driver ? `${record.carrier_driver.surname} ${record.carrier_driver.name}` : '–' }}
                        <template v-if="record.carrier_driver && record.carrier_driver.phone">
                            <br/>{{record.carrier_driver.phone}}
                        </template>
                        <template v-else-if="record.carrier_driver && record.carrier_driver.email">
                            <br/>{{record.carrier_driver.email}}
                        </template>
                    </div>
                </template>
                <template v-if="column.key === 'vehicle'">
                    <div v-if="record.carrier_car && record.carrier_car.body_type" style="text-align: right; font-size: 12px">
                        {{ record.carrier_car.body_type }}<br v-if="record.carrier_car.body_type"/>
                        {{ record.carrier_car ? record.carrier_car.plate_number : '–'}}
                    </div>
                    <div v-else style="text-align: right; font-size: 12px">
                        {{ record.vehicle_body_type }}<br v-if="record.vehicle_body_type"/>
                        {{ record.carrier_car ? record.carrier_car.plate_number : '–'}}
                    </div>
                </template>
                <template v-if="column.key === 'weight'">
                    <div style="text-align: right; font-size: 12px">{{ record.cargo_weight / 1000 }} т.</div>
                </template>

                <template v-if="column.key === 'client_sum' || column.key === 'carrier_sum' || column.key === 'margin_sum'">
                    <div style="text-align: right; font-size: 12px; white-space: nowrap ">{{ parseFloat(record[column.key]).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB', minimumFractionDigits: 0}) }}</div>
                </template>
                <template v-if="column.key === 'margin_percent'">
                    <div style="text-align: right; font-size: 12px; white-space: nowrap ">{{ parseFloat(record[column.key]).toLocaleString('ru-RU', {style: 'percent', minimumFractionDigits: 0}) }}</div>
                </template>
            </template>
            <template v-if="authStore.userCan('ORDERS_LST_COLUMN_FROM') || authStore.userCan('ORDERS_LST_COLUMN_TO')" #expandedRowRender="{ record }">
                <div style="display: flex; gap: 16px;">
                    <div v-if="authStore.userCan('ORDERS_LST_COLUMN_FROM')" v-for="(loc, i) in record.from_locations" :key="i" style="display: flex; gap: 16px; font-size: 13px">
                        <div>{{ dayjs(loc.arrive_date).format("DD.MM.YYYY") }} {{ dayjs(loc.arrive_time).format("HH:mm") }}</div>
                        <div>{{ loc.address }}</div>
                    </div>
                    <div v-if="authStore.userCan('ORDERS_LST_COLUMN_TO') && record.to_locations && record.to_locations.length > 0">
                        <ArrowRightOutlined />
                    </div>
                    <div v-if="authStore.userCan('ORDERS_LST_COLUMN_TO')" v-for="(loc, i) in record.to_locations" :key="i" style="display: flex; gap: 16px; font-size: 13px">
                        <div>{{ dayjs(loc.arrive_date).format("DD.MM.YYYY") }} {{ dayjs(loc.arrive_time).format("HH:mm") }}</div>
                        <div>{{ loc.address }}</div>
                    </div>
                </div>
            </template>
        </a-table>
        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="saveOrder"
            @delete="deleteOrder"
            @close="closeMainDrawer"
            :width="900"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`${currentOrder.data.id === null ? 'Новая заявка' : `Заявка #${currentOrder.data.id}`}${currentOrder.modified ? '*' : ''}`"
            :ok-text="currentOrder.data.id === null ? 'Сохранить' : 'Сохранить и закрыть'"
            :need-delete="currentOrder.data.id !== null"
            need-deletion-confirm-text="Вы уверены? Заявка будет удалена!"
            delete-text="Удалить"
            :maskClosable="currentOrder.data.id !== null"
            :closable="currentOrder.data.id !== null"
        >
            <Order v-model="currentOrder.data" :loading="mainDrawer.isLoading" :errors="ordersStore.err?.errors"/>
        </drawer>
    </Layout>
</template>
