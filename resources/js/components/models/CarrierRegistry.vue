<script setup>
import {logistOrderStatuses, managerOrderStatuses} from "../../helpers/index.js";
import dayjs from "dayjs";
import {reactive, watch} from "vue";

const model = defineModel()
const prop = defineProps({ loading: { type: Boolean, default: false }, errors: { type: Object, default: null } })

const columnsOrders = [
    { key: 'id', title: '#' },
    { key: 'created_at', title: 'Дата заказа' },
    { key: 'status_manager', title: 'Статус менеджер' },
    { key: 'status_logist', title: 'Статус логист' },
    { key: 'carrier_sum', title: 'Сумма заказа' },
    { key: 'carrier_vat', title: 'НДС' },
];

const vatArr = ['Без НДС', 'НДС', 'Нал'];

const makePaid = (v) => {
    if (v) {
        model.value.carrier_paid = model.value.carrier_sum
    }
}

const err = reactive({ date: null, vat: null, carrier_sum: null, carrier_paid: null })
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
    <a-form-item label="Дата реестра" name="date" :validate-status="err.date ? 'error': undefined" :help="err.date">
        <a-date-picker
            v-model:value="model.date"
            format="DD.MM.YYYY"
            style="width: 300px"
        />
    </a-form-item>
    <a-form-item label="НДС" name="vat" :validate-status="err.vat ? 'error': undefined" :help="err.vat">
        <a-select
            v-model:value="model.vat"
            placeholder="Выбор НДС"
        >
            <a-select-option :value="0">Без НДС</a-select-option>
            <a-select-option :value="1">НДС</a-select-option>
            <a-select-option :value="2">Наличные</a-select-option>
        </a-select>
    </a-form-item>
    <a-row :gutter="16">
        <a-col :span="12">
            <a-form-item label="Сумма" name="carrier_sum" :validate-status="err.carrier_sum ? 'error': undefined" :help="err.carrier_sum">
                <a-input-number
                    v-model:value="model.carrier_sum"
                    :min="0"
                    style="width: 100%"
                    placeholder="Сумма к оплате"
                    size="large"
                >
                    <template #addonAfter>₽</template>
                </a-input-number>
            </a-form-item>
        </a-col>
        <a-col :span="12">
            <a-form-item label="Оплачено" name="carrier_paid" :validate-status="err.carrier_paid ? 'error': undefined" :help="err.carrier_paid">
                <a-input-number
                    v-model:value="model.carrier_paid"
                    :min="0"
                    style="width: 100%"
                    placeholder="Оплачено"
                    size="large"
                >
                    <template #addonAfter>₽</template>
                </a-input-number>
            </a-form-item>
        </a-col>
    </a-row>
    <a-row :gutter="16">
        <a-col :span="12" :offset="12">
            <a-switch
                :checked="model.carrier_paid >= model.carrier_sum"
                :disabled="!(model.carrier_sum > 0)"
                @click="makePaid"
            />
            Реестр оплачен полностью
        </a-col>
    </a-row>
    <a-divider orientation="left">Заказы в реестре</a-divider>
    <a-table
        size="small"
        :columns="columnsOrders"
        :data-source="model.orders"
        :pagination="false"
    >
        <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'carrier_sum'">
                {{ parseFloat(record.carrier_sum).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
            </template>
            <template v-else-if="column.key === 'status_manager'">
                <a-badge :color="managerOrderStatuses[record.status_manager.status].color" />
                {{ managerOrderStatuses[record.status_manager.status].label }}
            </template>
            <template v-else-if="column.key === 'status_logist'">
                <a-badge :color="logistOrderStatuses[record.status_logist.status].color" />
                {{ logistOrderStatuses[record.status_logist.status].label }}
            </template>
            <template v-else-if="column.key === 'client_vat'">
                {{ vatArr[record.client_vat] }}
            </template>
            <template v-else-if="column.key === 'created_at'">
                {{ dayjs(record.created_at).format('DD.MM.YYYY HH:mm') }}
            </template>
            <template v-else>
                {{ record[column.key] }}
            </template>
        </template>
    </a-table>
</a-form>
</template>

<style scoped>

</style>
