<script setup>
import {computed, reactive, ref, watch} from "vue";
import {debounce} from "radash";
import {useSuggests} from "../../stores/models/suggests.js";

const model = defineModel()
const prop = defineProps({ errors: { type: Object, default: null } })
const suggests = useSuggests()


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

const bankList = ref([])

const handleBankSearch = debounce({delay: 500}, async q => {
    if (q.length > 3) {
        bankList.value = await suggests.bankSuggest(q)
    }
})

const handleBankChange = (e) => {
    const bank = bankList.value.find(el => el.bik === e)
    if (bank) {
        model.value.bik = bank.bik
        model.value.bank_name = bank.bank_name
        model.value.payment_city = bank.payment_city
        model.value.account_correspondent = bank.account_correspondent
    }
}

const bankOptions = computed(() => {
    let res = [...bankList.value]
    return res.map(el => ({value: el.bik, ...el}))
})

</script>

<template>
    <a-form layout="vertical" :model="model">
        <a-form-item label="БИК" name="bik" :validate-status="err.bik ? 'error': undefined" :help="err.bik">
            <a-select
                show-search
                v-model:value="model.bik"
                placeholder="БИК банка"
                :filter-option="false"
                :not-found-content="suggests.isLoading ? undefined : null"
                @search="handleBankSearch"
                @change="handleBankChange"
                :options="bankOptions"
            >
                <template #option="rec">
                    <div style="display: flex; justify-content: space-between; align-items: center">
                        <div>{{ rec.bank_name }}</div>
                        <div style="font-size: 11px; font-weight: 500">
                            {{ rec.bik }}
                        </div>
                    </div>
                </template>
                <template v-if="suggests.isLoading" #notFoundContent>
                    <div style="display: flex; justify-content: center; align-items: center; min-height: 100px">
                        <a-spin size="small" />
                    </div>
                </template>
            </a-select>
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
