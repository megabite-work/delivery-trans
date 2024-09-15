<script setup>
import {logistOrderStatuses, managerOrderStatuses} from "../../helpers/index.js";
import dayjs from "dayjs";
import {h, reactive, watch} from "vue";
import {DeleteOutlined} from "@ant-design/icons-vue";
import {isArray} from "radash";

const model = defineModel()
const prop = defineProps({
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: null },
    readOnly: {type: Boolean, default: false }
})

const columnsOrders = [
    { key: 'id', title: '#' },
    { key: 'created_at', title: 'Дата заказа' },
    { key: 'status_manager', title: 'Статус менеджер' },
    { key: 'status_logist', title: 'Статус логист' },
    { key: 'client_sum', title: 'Сумма заказа' },
    { key: 'client_vat', title: 'НДС' },
    { key: '__delete' },
];

const vatArr = ['Без НДС', 'НДС', 'Нал'];

const makePaid = (v) => {
    if (v) {
        model.value.client_paid = model.value.client_sum
    }
}
const err = reactive({date: null, vat: null, client_sum: null, client_paid: null, bill_date: null, bill_number: null})
watch(() => prop.errors, () => {
    Object.keys(err).forEach((key) => {
        if (prop.errors[key]) {
            err[key] = prop.errors[key][0]
            return
        }
        err[key] = null
    })
})
const deleteOrderFromRegistry = (id, storno) => {
    if (isArray(model.value.orders)) {
        model.value.orders = model.value.orders.filter((order) => order.id !== id)
        model.value.client_sum = parseFloat(model.value.client_sum) - parseFloat(storno)
    }
}
</script>

<template>
<a-form layout="vertical" :model="model" :disabled="readOnly">
    <a-form-item label="Дата реестра" name="date" :validate-status="err.date ? 'error': undefined" :help="err.date">
        <a-date-picker
            v-model:value="model.date"
            format="DD.MM.YYYY"
            style="width: 300px"
        />
    </a-form-item>
    <a-form-item label="НДС" :validate-status="err.vat ? 'error': undefined" :help="err.vat">
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
            <a-form-item label="Номер счета" name="bill_number" :validate-status="err.bill_number ? 'error': undefined" :help="err.bill_number">
                <a-input
                    v-model:value="model.bill_number"
                    style="width: 100%"
                    placeholder="Номер счета"
                />
            </a-form-item>
        </a-col>
        <a-col :span="12">
            <a-form-item label="Дата счета" name="bill_date" :validate-status="err.bill_date ? 'error': undefined" :help="err.bill_date">
                <a-date-picker
                    v-model:value="model.bill_date"
                    format="DD.MM.YYYY"
                    style="width: 100%"
                    placeholder="Дата счета"
                />
            </a-form-item>
        </a-col>
    </a-row>
    <a-row :gutter="16">
        <a-col :span="12">
            <a-form-item label="Сумма" name="client_sum" :validate-status="err.client_sum ? 'error': undefined" :help="err.client_sum">
                <a-input-number
                    v-model:value="model.client_sum"
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
            <a-form-item label="Оплачено" name="client_paid" :validate-status="err.client_paid ? 'error': undefined" :help="err.client_paid">
                <a-input-number
                    v-model:value="model.client_paid"
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
                :checked="model.client_paid >= model.client_sum"
                :disabled="!(model.client_sum > 0) || readOnly"
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
            <template v-if="column.key === 'client_sum'">
                {{ parseFloat(record.client_sum).toLocaleString('ru-RU', {style: 'currency', currency: 'RUB'}) }}
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
            <template v-else-if="column.key === '__delete'">
                <a-button :icon="h(DeleteOutlined)" type="dashed" @click="() => deleteOrderFromRegistry(record.id, record.client_sum)"/>
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
