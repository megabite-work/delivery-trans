<script setup>
import {reactive, watch} from "vue";

const model = defineModel()
const prop = defineProps({ errors: { type: Object, default: null } })
const err = reactive({bik: null, account_payment: null, bank_name: null, payment_city: null, account_correspondent: null})

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
        <a-form-item label="БИК" name="bik" :validate-status="err.bik ? 'error': undefined" :help="err.bik">
            <a-input
                v-model:value="model.bik"
                placeholder="БИК банка"
                :maxlength="9"
            />
        </a-form-item>
        <a-form-item label="Номер счета" name="account_payment" :validate-status="err.account_payment ? 'error': undefined" :help="err.account_payment">
            <a-input
                v-model:value="model.account_payment"
                placeholder="Номер счета"
            />
        </a-form-item>
        <a-form-item label="Банк" name="bank_name" :validate-status="err.bank_name ? 'error': undefined" :help="err.bank_name">
            <a-input
                v-model:value="model.bank_name"
                placeholder="Наименование банка"
            />
        </a-form-item>
        <a-form-item label="Город" name="payment_city" :validate-status="err.payment_city ? 'error': undefined" :help="err.payment_city">
            <a-input
                v-model:value="model.payment_city"
                placeholder="Город"
            />
        </a-form-item>
        <a-form-item label="Корреспондентский счет" name="account_correspondent" :validate-status="err.account_correspondent ? 'error': undefined" :help="err.account_correspondent">
            <a-input
                v-model:value="model.account_correspondent"
                placeholder="Корреспондентский счет"
            />
        </a-form-item>
    </a-form>
</template>

<style scoped>

</style>
