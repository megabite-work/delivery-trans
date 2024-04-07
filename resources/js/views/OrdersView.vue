<script setup>
import Layout from '@/layouts/AppLayout.vue';
import {onBeforeUnmount, onMounted, reactive, ref} from "vue";
import {message} from "ant-design-vue";
import {useOrdersStore} from "../stores/models/orders.js";
import Drawer from "../components/Drawer.vue";
import Order from "../components/models/Order.vue";

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
            //currentOrder.data = await ordersStore.getOrder(orderId)
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
    currentOrder.data = { id: null }
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

        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="() => {console.log(currentOrder.data)}"
            @delete="() => {}"
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
