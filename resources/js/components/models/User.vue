<script setup>

import {reactive, watch} from "vue";

const model = defineModel()
const prop = defineProps({ loading: { type: Boolean, default: false }, errors: { type: Object, default: null } })

const handlePasswordFocus = () => {
    model.value.password = undefined
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
</script>

<template>
    <a-form layout="vertical" :model="model">
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
                v-model:value="model.password"
                :placeholder="model.id ? 'Новый пароль' : 'Задайте пароль'"
                @focus="handlePasswordFocus"
            />
        </a-form-item>
    </a-form>
</template>

<style scoped>

</style>
