<script setup>
const model = defineModel()
</script>

<template>
    <a-form layout="vertical" :model="model">
        <a-form-item label="БИК" name="bik">
            <a-input
                v-model:value="model.bik"
                placeholder="БИК банка"
                :maxlength="9"
            />
        </a-form-item>
        <a-form-item label="Номер счета" name="account_payment">
            <a-input
                v-model:value="model.account_payment"
                placeholder="Номер счета"
            />
        </a-form-item>
        <a-form-item label="Банк" name="bank_name">
            <a-input
                v-model:value="model.bank_name"
                placeholder="Наименование банка"
            />
        </a-form-item>
        <a-form-item label="Город" name="payment_city">
            <a-input
                v-model:value="model.payment_city"
                placeholder="Город"
            />
        </a-form-item>
        <a-form-item label="Корреспондентский счет" name="account_correspondent">
            <a-input
                v-model:value="model.account_correspondent"
                placeholder="Корреспондентский счет"
            />
        </a-form-item>
    </a-form>
</template>

<style scoped>

</style>
