<script setup>

const model = defineModel()
const prop = defineProps({ loading: { type: Boolean, default: false }, errors: { type: Object, default: null } })

const handlePasswordFocus = () => {
    model.value.password = undefined
}
</script>

<template>
    <a-form layout="vertical" :model="model">
        <a-form-item label="Имя и фамилия пользователя" name="name">
            <a-input
                v-model:value="model.name"
                placeholder="Имя и фамилия"
            />
        </a-form-item>
        <a-form-item label="Адрес электронной почты" name="email">
            <a-input
                v-model:value="model.email"
                placeholder="Электропочта"
                type="email"
            />
        </a-form-item>
        <a-form-item label="Пароль" name="password">
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
