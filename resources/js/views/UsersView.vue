<script setup>
import Drawer from "../components/Drawer.vue";
import Layout from "../layouts/AppLayout.vue";
import User from "../components/models/User.vue";
import {onBeforeUnmount, onMounted, reactive, ref} from "vue";
import { UserOutlined } from "@ant-design/icons-vue";
import {message} from "ant-design-vue";
import {useUsersStore} from "../stores/models/users.js";
import {useAuthStore} from "../stores/auth.js";


const columnsUsers = [
    { key: '__icon', width: 50 },
    { title: 'Имя и фамилия', dataIndex: 'name' },
    { title: 'Email', dataIndex: 'email' },
];

const usersStore = useUsersStore()
const authStore = useAuthStore()
const currentUser = reactive({ data:{ id: null }, modified: false })
const mainDrawer = reactive({ isOpen: false, isSaving: false, isLoading: false })

const openMainDrawer = async (userId = null) => {
    currentUser.data = { id: null }
    currentUser.modified = false
    mainDrawer.isOpen = true
    if (userId !== null) {
        try {
            mainDrawer.isLoading = true
            currentUser.data = await usersStore.getUser(userId)
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
    currentUser.data = { id: null }
}

const saveUser = async () => {
    console.log(currentUser.data)
    mainDrawer.isSaving = true
    try {
        if (currentUser.data.id === null) {
            if (authStore.userCan('USERS_CREATE')){
                currentUser.data  = await usersStore.createUser(currentUser.data)
                currentUser.modified = false
                message.success('Пользователь создан')
            }
        } else {
            if(authStore.userCan('USERS_EDIT')) {
                currentUser.data = await usersStore.storeUser(currentUser.data)
                currentUser.modified = false
                message.success('Пользователь записан')
            }
        }
        mainDrawer.isSaving = false
        closeMainDrawer()
    } catch (e) {
        message.error(`Ошибка. Не удалось ${currentUser.data.id === null ? 'создать' : 'сохранить'} пользователя`)
    } finally {
        mainDrawer.isSaving = false
        await usersStore.refreshDataList()
    }
}

const deleteUser = async () => {
    if (currentUser.data.id === null || !authStore.userCan('USERS_DELETE')) {
        return
    }
    try {
        await usersStore.deleteUser(currentUser.data.id)
        message.success('Пользователь успешно удален')
        await usersStore.refreshDataList()
        closeMainDrawer()
    } catch (e) {
        message.error('Ошибка. Не удалось удалить пользователя')
    }
}

const clientHeight = ref(document.documentElement.clientHeight)
const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }
const tableRowFn = record => ({ onClick: () => openMainDrawer(record.id) })
onMounted(() => {
    usersStore.refreshDataList()
    window.addEventListener('resize', updateClientHeight)
})

onBeforeUnmount(() => {
    window.removeEventListener('resize', updateClientHeight)
})

</script>

<template>
    <Layout title="Пользователи">
        <template v-if="authStore.userCan('USERS_CREATE')" #headerExtra>
            <a-button type="primary" @click="() => openMainDrawer()">Новый пользователь</a-button>
        </template>
        <a-table
            :loading="usersStore.listLoading"
            :custom-row="tableRowFn"
            :columns="columnsUsers"
            :data-source="usersStore.dataList"
            :pagination="false"
            :scroll="{ y: clientHeight - 335 }"
            :row-class-name="() => 'cursor-pointer'"
        >
            <template #bodyCell="{ column, record }">
                <template v-if="column.key === '__icon'">
                    <UserOutlined />
                </template>
            </template>
        </a-table>
        <drawer
            v-model:open="mainDrawer.isOpen"
            @save="saveUser"
            @delete="deleteUser"
            @close="() => {mainDrawer.isOpen = false}"
            :width="736"
            :loading="mainDrawer.isLoading"
            :saving="mainDrawer.isSaving"
            :ok-loading="mainDrawer.isSaving"
            :title="`${currentUser.data.id === null ? 'Новый пользователь' : `Пользователь ${currentUser.data.email}`}${currentUser.modified ? '*' : ''}`"
            ok-text="Сохранить и закрыть"
            :need-ok="authStore.userCan('USERS_CREATE') || authStore.userCan('USERS_EDIT')"
            :need-delete="currentUser.data.id !== null && currentUser.data.id !== authStore.user.id && authStore.userCan('USERS_DELETE')"
            need-deletion-confirm-text="Вы уверены? Пользователь будет удален!"
            delete-text="Удалить"
        >
            <User
                v-model="currentUser.data"
                :loading="mainDrawer.isLoading"
                :errors="usersStore.err?.errors"
                :read-only="(!authStore.userCan('USERS_EDIT') && currentUser.data.id !== null) || (!authStore.userCan('USERS_CREATE') && currentUser.data.id === null)"
            />
        </drawer>
    </Layout>
</template>

<style scoped>

</style>
