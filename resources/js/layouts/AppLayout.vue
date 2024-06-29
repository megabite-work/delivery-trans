<script setup>
import { ref, watch} from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from "../stores/auth.js";

import ruRU from 'ant-design-vue/locale/ru_RU';

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

const routes = [
    // {
    //     key: 'dashboard',
    //     label: 'Dashboard',
    // },
    {
        key: 'orders',
        label: 'Заявки',
    },
    {
        key: 'clients',
        label: 'Заказчики',
    },
    {
        key: 'carriers',
        label: 'Перевозчики',
    },
]

const optRoutes = [
    {
        key: 'prices',
        label: 'Прайс-листы'
    }
]

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
                    alt="User Name"
                    :style="{cursor: 'pointer', backgroundColor: '#172554'}"
                    size="large"
                    class="avatar"
                />
                <template #overlay>
                    <a-menu :style="{width: '170px'}">
                        <a-menu-item key="0">
                            <a>{{ auth.user.name }}</a>
                        </a-menu-item>
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
                <a-sub-menu key="options">
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
