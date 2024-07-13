<script setup>
import Layout from '@/layouts/AppLayout.vue';
import {onBeforeUnmount, onMounted, reactive, ref, h} from "vue";
import {message} from "ant-design-vue";
import {useOrdersStore} from "../stores/models/orders.js";
import Drawer from "../components/Drawer.vue";
import Order from "../components/models/Order.vue";
import {FilterOutlined, SearchOutlined, ArrowRightOutlined} from "@ant-design/icons-vue";
import {isArray} from "radash";

import dayjs from "dayjs";
import {managerOrderStatuses, logistOrderStatuses} from "../helpers/index.js";

const columnsOrders = ref([
    { title: '#', key: 'id', width: 50, sorter: true, defaultSortOrder: 'descend' },
    { title: 'Дата', key: 'created_at', sorter: true },
    { title: 'Старт поездки', key: 'started_at', sorter: true },
    {
        title: 'Статус заявки',
        children: [
            { title: 'Менеджер', key: 'status_manager' },
            { title: 'Логист', key: 'status_logist' }
        ]
    },
    { title: 'Заказчик', key: 'client' },
    { title: 'Перевозчик', key: 'carrier' },
    { title: 'Авто', key: 'vehicle' },
    { title: 'Вес груза', key: 'weight'},
    { title: 'Сумма', key: 'client_sum', fixed: 'right'},
    { title: 'Себестоимость', key: 'carrier_sum', fixed: 'right'},
    { title: 'Маржа ₽', key: 'margin_sum', fixed: 'right'},
    { title: 'Маржа %', key: 'margin_percent', fixed: 'right'},
])



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
            <a-form layout="inline">
                <div style="display: flex; flex-direction: column; gap: 16px">
                    <div style="display: flex; margin-left: 47px">
                        <a-form-item label="Номер">
                            <a-input-number min="1" v-model:value="ordersStore.filter.id" placeholder="#"/>
                        </a-form-item>
                        <search-outlined style="margin-right: 10px"/><a-input v-model:value="ordersStore.filter.text" placeholder="Поисковая строка" />
                    </div>
                    <div style="margin-left: 46px">
                        <a-form-item label="Статус">
                            <a-space>
                                <a-select style="width: 240px" placeholder="Статус менеджера" v-model:value="ordersStore.filter.status_manager" :allow-clear="true">
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
                                <a-select style="width: 240px" placeholder="Статус логиста" v-model:value="ordersStore.filter.status_logist" :allow-clear="true">
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
                            </a-space>
                        </a-form-item>
                    </div>
                    <div style="display: flex; margin-left: 10px">
                        <a-form-item label="Дата заявки">
                            <a-range-picker v-model:value="ordersStore.filter.date" style="width: 300px" />
                        </a-form-item>
                    </div>
                    <div>
                        <a-form-item label="Дата поездки">
                            <a-range-picker v-model:value="ordersStore.filter.arrive_date" style="width: 300px" />
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
            :columns="columnsOrders"
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
            :default-expand-all-rows="true"
            row-key="id"
            expand-fixed="id"
            @change="handleTableChange"
            size="small"
        >
            <template #headerCell="cell">
                <div v-if="!isArray(cell.title)" style="font-size: 12px; text-wrap: none; white-space: nowrap; text-align: center">
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
                        <template #overlay>
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
                        <template #overlay>
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
            <template #expandedRowRender="{ record }">
                <div style="display: flex; gap: 16px;">
                    <div v-for="(loc, i) in record.from_locations" :key="i" style="display: flex; gap: 16px; font-size: 13px">
                        <div>{{ dayjs(loc.arrive_date).format("DD.MM.YYYY") }} {{ dayjs(loc.arrive_time).format("HH:mm") }}</div>
                        <div>{{ loc.address }}</div>
                    </div>
                    <div v-if="record.to_locations && record.to_locations.length > 0">
                        <ArrowRightOutlined />
                    </div>
                    <div v-for="(loc, i) in record.to_locations" :key="i" style="display: flex; gap: 16px; font-size: 13px">
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
            @close="() => {mainDrawer.isOpen = false}"
            :width="900"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`${currentOrder.data.id === null ? 'Новая заявка' : `Заявка #${currentOrder.data.id}`}${currentOrder.modified ? '*' : ''}`"
            :ok-text="currentOrder.data.id === null ? 'Сохранить' : 'Сохранить и закрыть'"
            :need-delete="currentOrder.data.id !== null"
            need-deletion-confirm-text="Вы уверены? Заявка будет удалена!"
            delete-text="Удалить"
        >
            <Order v-model="currentOrder.data" :loading="mainDrawer.isLoading" :errors="ordersStore.err?.errors"/>
        </drawer>
    </Layout>
</template>
