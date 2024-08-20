<script setup>

import {reactive, watch, ref, onMounted, computed} from "vue";
import {useRolesStore} from "../../stores/models/roles.js";
import {isArray} from "radash";

const model = defineModel()
const prop = defineProps({
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: null },
    readOnly: {type: Boolean, default: false }
})

const rolesStore = useRolesStore()

const handlePasswordFocus = () => {
    model.value.password = undefined
}

const rolesList = ref([])

const isRoleChecked = computed(() => (id => isArray(model.value.roles) ? !!model.value.roles.find((v) => v.id === id) : false))
const handleRoleSet = (e) => {
    if (e.target.checked) {
        if (!isArray(model.value.roles)) {
            model.value.roles = []
        }
        model.value.roles.push({id: parseInt(e.target.id)})
        return
    }
    for (let i = 0; i < model.value.roles.length; i++) {
        if (model.value.roles[i].id === parseInt(e.target.id)) {
            model.value.roles.splice(i, 1)
            return
        }
    }
}

const err = reactive({
    name: null, email: null, password: null
})
watch(() => prop.errors, () => {
    Object.keys(err).forEach((key) => {
        if (prop.errors[key]) {
            err[key] = prop.errors[key][0]
            return
        }
        err[key] = null
    })
})

onMounted(async () => {
    rolesList.value = await rolesStore.getRoles()
})
</script>

<template>
    <a-form layout="vertical" :model="model" :disabled="readOnly">
        <a-form-item label="Имя и фамилия пользователя" name="name" :validate-status="err.name ? 'error': undefined" :help="err.name">
            <a-input
                v-model:value="model.name"
                placeholder="Имя и фамилия"
            />
        </a-form-item>
        <a-form-item label="Адрес электронной почты" name="email" :validate-status="err.email ? 'error': undefined" :help="err.email">
            <a-input
                v-model:value="model.email"
                placeholder="Электропочта"
                type="email"
            />
        </a-form-item>
        <a-form-item label="Пароль" name="password" :validate-status="err.password ? 'error': undefined" :help="err.password">
            <a-input-password
                autocomplete="on"
                v-model:value="model.password"
                :placeholder="model.id ? 'Новый пароль' : 'Задайте пароль'"
                @focus="handlePasswordFocus"
            />
        </a-form-item>
        <a-divider orientation="left">Роли пользователя</a-divider>
        <div>
            <div v-if="isRoleChecked(0)" style="padding: 3px 0">
                <a-checkbox id="0" checked>Суперадминистратор</a-checkbox>
            </div>
            <div v-for="role in rolesList" :key="role.id" style="padding: 3px 0">
                <a-checkbox :id="role.id.toString()" :checked="isRoleChecked(role.id)" @change="handleRoleSet">{{role.name}}</a-checkbox>
            </div>
        </div>
    </a-form>
</template>

<style>
input, select, textarea, .ant-select-selector {
    color: #1a202c !important;
}
</style>
