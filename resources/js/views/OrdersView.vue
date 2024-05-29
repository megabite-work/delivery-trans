<script setup>
import Layout from '@/layouts/AppLayout.vue';
import {onBeforeUnmount, onMounted, reactive, ref} from "vue";
import {message} from "ant-design-vue";
import {useOrdersStore} from "../stores/models/orders.js";
import Drawer from "../components/Drawer.vue";
import Order from "../components/models/Order.vue";
import dayjs from "dayjs";

const columnsOrders = [
    {
        title: '#',
        key: 'id',
        width: 50,
    },
    {
        title: 'Дата',
        key: 'created_at'
    },
    {
        title: 'Поездка',
        children: [
            { title: 'Дата', dataIndex: 'date_first' },
            { title: 'Время', dataIndex: 'time_first' }
        ]
    },
    {
        title: 'Статус заявки',
        children: [
            { title: 'Логист', dataIndex: 'status_logist' },
            { title: 'Менеджер', dataIndex: 'status_manager' }
        ]
    },
    {
        title: 'Заказчик',
        key: 'client',
        // children: [
        //     { title: 'Заказчик', dataIndex: 'client' },
        //     { title: 'Адрес подачи', dataIndex: 'address_first' },
        // ]
    },
    {
        title: 'Перевозчик',
        key: 'carrier',
        // children: [
        //     { title: 'Перевозчик', dataIndex: 'carrier' },
        //     { title: 'Водитель', dataIndex: 'driver' },
        // ]
    },
    {
        title: 'Авто',
        key: 'vehicle',
        // children: [
        //     { title: 'Тип', key: 'vehicle_body_type' },
        //     { title: 'Номер', key: 'vehicle_plate_number' },
        // ]
    },
    { title: 'Вес груза', key: 'weight' },
    { title: 'Сумма', key: 'client_sum', fixed: 'right' },
    { title: 'Себестоимость', key: 'carrier_sum', fixed: 'right' },
    { title: 'Маржа ₽', key: 'margin_sum', fixed: 'right' },
    { title: 'Маржа %', key: 'margin_percent', fixed: 'right' },
]



const ordersStore = useOrdersStore()
const clientHeight = ref(document.documentElement.clientHeight)
const currentOrder = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })
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
        <template #headerExtra><a-button type="primary" @click="() => openMainDrawer()">Новая заявка</a-button></template>
        <a-table
            :loading="ordersStore.listLoading"
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
            size="small"
        >
            <template #headerCell="cell">
                <div style="font-size: 12px; text-wrap: none; white-space: nowrap; text-align: center">
                    {{cell.title}}
                </div>
            </template>
            <template #bodyCell="{ column, record }">
                <template v-if="column.key === 'id'">
                    <div style="text-align: right; font-size: 12px">{{ record.id }}</div>
                </template>
                <template v-if="column.key === 'created_at' || column.key === 'updated_at'">
                    <div style="font-size: 12px; text-align: right">
                        {{ dayjs(record[column.key]).format('DD.MM.YY') }}
                        {{ dayjs(record[column.key]).format('HH:mm') }}
                    </div>
                </template>
                <template v-if="column.key === 'client'">
                    <div style="text-align: right; font-size: 12px">{{ record.client ? record.client.name_short : '–' }}</div>
                </template>
                <template v-if="column.key === 'carrier'">
                    <div style="text-align: right; font-size: 12px">{{ record.carrier ? record.carrier.name_short : '–' }}</div>
                </template>
                <template v-if="column.key === 'vehicle'">
                    <div style="text-align: right; font-size: 12px">
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
