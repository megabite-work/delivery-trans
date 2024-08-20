<script setup>
import {computed, ref, watch} from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from "../stores/auth.js";
import { CheckOutlined } from "@ant-design/icons-vue";
import ruRU from 'ant-design-vue/locale/ru_RU';
import 'dayjs/locale/ru';

defineProps({
    title: String,
    subTitle: String,
})

const auth = useAuthStore()
const currentRouteName = ref([useRoute().name])
watch(() => useRoute().name, newRoute => { currentRouteName.value = [newRoute] })

const router = useRouter()

const logout = async () => {
    try {
        await auth.logout()
    } finally {
        router.push({name: 'login'})
    }
}

const avatarUserName = computed(() => auth.user.name.split(/\s/).reduce((response,word)=> response + word.slice(0, 1),''))

const routes = computed(() => {
    const res = [];
    if (auth.userCan('ORDERS_SECTION')) {
        res.push({
            key: 'orders',
            label: 'Заявки',
        })
    }

    if (auth.userCan('CLIENTS_SECTION')) {
        res.push({
            key: 'clients',
            label: 'Заказчики',
        })
    }

    if (auth.userCan('CARRIERS_SECTION')) {
        res.push({
            key: 'carriers',
            label: 'Перевозчики',
        })
    }
    return res
})

const optRoutes = computed(() => {
    const res = []

    if (auth.userCan('PRICES_DIR')) {
        res.push({
            key: 'prices',
            label: 'Прайс-листы'
        })
    }
    if (auth.userCan('BODY_TYPES_DIR')) {
        res.push({
            key: 'body-types',
            label: 'Типы кузовов'
        })
    }
    if (auth.userCan('CAPACITIES_DIR')) {
        res.push({
            key: 'car-capacities',
            label: 'Вместительность авто'
        })
    }
    if (auth.userCan('T_CONDITIONS_DIR')) {
        res.push({
            key: 'tconditions',
            label: 'Температурные условия'
        })
    }
    if (auth.userCan('TONNAGES_DIR')) {
        res.push({
            key: 'tonnages',
            label: 'Тоннаж'
        })
    }
    if (auth.userCan('USERS_DIR')) {
        res.push({
            key: 'users',
            label: 'Пользователи'
        })
    }
    if (auth.userCan('ROLES_DIR')) {
        res.push({
            key: 'roles',
            label: 'Роли'
        })
    }
    return res
})

</script>

<template>
<a-config-provider :locale="ruRU">
    <a-layout>
        <a-layout-header>
            <div class="logo">
                <router-link to="/" :style="{color: '#fff'}">Delivery Trans</router-link>
            </div>
            <a-dropdown :trigger="['click']">
                <a-avatar
                    @click.prevent
                    :style="{cursor: 'pointer', backgroundColor: '#172554'}"
                    :alt="auth.user.name"
                    size="large"
                    class="avatar"
                >{{avatarUserName}}</a-avatar>
                <template #overlay>
                    <a-menu :style="{width: '170px'}">
                        <a-menu-item key="0">
                            <a>{{ auth.user.name }}</a>
                        </a-menu-item>
                        <a-sub-menu :key="auth.user.roles" :title="auth.currentRole.name">
                            <a-menu-item v-for="role in auth.user.roles" :key="`r${role.id}`" @click="() => auth.switchRole(role.id)">
                                <template v-if="auth.currentRole.id === role.id" >
                                    <div style="font-weight: 600; display: flex; gap: 8px">
                                        <CheckOutlined /><div>{{ role.name }}</div>
                                    </div>
                                </template>
                                <template v-else>
                                    {{role.name}}
                                </template>
                            </a-menu-item>
                        </a-sub-menu>
                        <a-menu-divider />
                        <a-menu-item key="1" @click="logout">
                            <a>Выход</a>
                        </a-menu-item>
                    </a-menu>
                </template>
            </a-dropdown>
            <a-menu theme="dark" mode="horizontal" :style="{lineHeight: '64px'}" v-model:selected-keys="currentRouteName">
                <a-menu-item v-for="route in routes" :key="route.key">
                    <router-link :to="{name: route.key}">{{ route.label }}</router-link>
                </a-menu-item>
                <a-sub-menu v-if="optRoutes.length > 0" key="options">
                    <template #title>Справочники</template>
                    <a-menu-item v-for="route in optRoutes" :key="route.key">
                        <router-link :to="{name: route.key}">{{ route.label }}</router-link>
                    </a-menu-item>
                </a-sub-menu>
            </a-menu>
        </a-layout-header>
        <a-layout-content :style="{ backgroundColor: '#FFFFFF', minHeight: 'calc(100vh - 135px)' }">
            <a-page-header
                :title="title"
                :sub-title="subTitle"
            >
                <template v-if="$slots.headerExtra" #extra>
                    <slot name="headerExtra" />
                </template>
            </a-page-header>
            <div>
                <slot />
            </div>
        </a-layout-content>
        <a-layout-footer :style="{ textAlign: 'center' }">
            Delivery Trans ©2024
        </a-layout-footer>
    </a-layout>
</a-config-provider>
</template>

<style>
.logo {
    color: #ffffff;
    font-family: 'Ubuntu', sans-serif;
    font-weight: 600;
    font-size: 18px;
    float: left;
    height: 31px;
    padding-right: 40px;
}
.avatar {
    float: right;
    margin: 12px;
}
</style>
