<script setup>
import {reactive, ref, watch} from "vue";
import axios from "axios";
import {message} from "ant-design-vue";
import DatePicker from "../DatePicker.vue";
import { useIMask } from "vue-imask";

const model = defineModel()
const prop = defineProps({ loading: { type: Boolean, default: false }, errors: { type: Object, default: null } })
const { el } = useIMask({
    mask: '+{7}(000)000-00-00'
});

const citizenshipOptionsList = ref([])
const citizenshipOptionsLoading = ref(false)
const getCitizenshipOptionsList = async () => {
    citizenshipOptionsLoading.value = true
    try {
        const { data } = await axios.get('api/suggest/countries')
        citizenshipOptionsList.value = data.map(el => ({value: el.code, label: el.name}))
    } catch (e) {
        message.error('Ошибка загрузки списка')
    } finally {
        citizenshipOptionsLoading.value = false
    }
}

const err = reactive({
    surname: null,
    name: null,
    patronymic: null,
    inn: null,
    birthday: null,
    phone: null,
    email: null,
    license_number: null,
    license_expiration: null,
    citizenship: null,
    passport_number: null,
    passport_issuer: null,
    passport_issuer_code: null,
    passport_issue_date: null,
    registration_address: null,
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
    <a-row :gutter="16">
        <a-col :span="20">
            <a-form-item label="Фамилия" name="surname" :validate-status="err.surname ? 'error': undefined" :help="err.surname">
                <a-input v-model:value="model.surname" placeholder="Фамилия водителя"/>
            </a-form-item>
        </a-col>
        <a-col :span="4">
            <a-form-item label="Активен" name="is_active">
                <a-switch v-model:checked="model.is_active" />
            </a-form-item>
        </a-col>
    </a-row>
    <a-form-item label="Имя" name="name" :validate-status="err.name ? 'error': undefined" :help="err.name">
        <a-input v-model:value="model.name" placeholder="Имя водителя" />
    </a-form-item>
    <a-form-item label="Отчество" name="patronymic" :validate-status="err.patronymic ? 'error': undefined" :help="err.patronymic">
        <a-input v-model:value="model.patronymic" placeholder="Отчество водителя" />
    </a-form-item>
    <a-form-item label="ИНН водителя" name="inn" :validate-status="err.inn ? 'error': undefined" :help="err.inn">
        <a-input
            v-model:value="model.inn"
            placeholder="Введите ИНН водителя"
            :maxlength="12"
        />
    </a-form-item>
    <a-form-item label="Дата рождения" name="birthday" :validate-status="err.birthday ? 'error': undefined" :help="err.birthday">
        <DatePicker
            v-model="model.birthday"
            placeholder="Дата рождения"
            style="width: 200px"
        />
    </a-form-item>
    <a-row :gutter="16">
        <a-col :span="12">
            <a-form-item label="Номер телефона" name="phone" :validate-status="err.phone ? 'error': undefined" :help="err.phone">
                <input
                     v-model="model.phone"
                    type="text"
                    ref="el"
                    class="dt-input"
                    placeholder="Номер телефона"
                />
            </a-form-item>
        </a-col>
        <a-col :span="12">
            <a-form-item label="Электропочта" name="email" :validate-status="err.email ? 'error': undefined" :help="err.email">
                <a-input placeholder="Электропочта" v-model:value="model.email" />
            </a-form-item>
        </a-col>
    </a-row>
    <a-divider>Права</a-divider>
    <a-row :gutter="16">
        <a-col :span="12">
            <a-form-item label="Номер водительского" name="license_number" :validate-status="err.license_number ? 'error': undefined" :help="err.license_number">
                <a-input placeholder="Номер удостоверения" v-model:value="model.license_number" />
            </a-form-item>
        </a-col>
        <a-col :span="12">
            <a-form-item label="Действуют по" name="license_expiration" :validate-status="err.license_expiration ? 'error': undefined" :help="err.license_expiration">
                <DatePicker
                    v-model="model.license_expiration"
                    placeholder="Действуют по"
                    style="width: 100%"
                />
            </a-form-item>
        </a-col>
    </a-row>
    <a-divider>Паспортные данные</a-divider>
    <a-form-item label="Гражданство" name="citizenship" :validate-status="err.citizenship ? 'error': undefined" :help="err.citizenship">
        <a-select
            ref="select"
            placeholder="Гражданство"
            v-model:value="model.citizenship"
            :options="citizenshipOptionsList"
            :loading="citizenshipOptionsLoading"
            @focus="getCitizenshipOptionsList"
        />
    </a-form-item>
    <a-form-item label="Серия и номер" name="passport_number" :validate-status="err.passport_number ? 'error': undefined" :help="err.passport_number">
        <a-input placeholder="Серия и номер паспорта" v-model:value="model.passport_number" />
    </a-form-item>
    <a-form-item label="Кем выдан" name="passport_issuer" :validate-status="err.passport_issuer ? 'error': undefined" :help="err.passport_issuer">
        <a-textarea
            :auto-size="true"
            placeholder="Кем выдан паспорт"
            v-model:value="model.passport_issuer"
        />
    </a-form-item>
    <a-row :gutter="16">
        <a-col :span="12">
            <a-form-item label="Код подразделения" name="passport_issuer_code" :validate-status="err.passport_issuer_code ? 'error': undefined" :help="err.passport_issuer_code">
                <a-input placeholder="Код подразделения" v-model:value="model.passport_issuer_code" />
            </a-form-item>
        </a-col>
        <a-col :span="12">
            <a-form-item label="Дата выдачи" name="passport_issue_date" :validate-status="err.passport_issue_date ? 'error': undefined" :help="err.passport_issue_date">
                 <DatePicker
                    v-model="model.passport_issue_date"
                    placeholder="Дата выдачи"
                    style="width: 200px"
                />
            </a-form-item>
        </a-col>
    </a-row>
    <a-form-item label="Адрес регистрации" name="registration_address" :validate-status="err.registration_address ? 'error': undefined" :help="err.registration_address">
        <a-input placeholder="Адрес регистрации" v-model:value="model.registration_address" />
    </a-form-item>
</a-form>
</template>

<style scoped>
.dt-input {
    padding: 4px 11px;
    color: rgba(0, 0, 0, 0.88);
    font-size: 14px;
    width: 100%;
    min-width: 0;
    border-width: 1px;
    border-style: solid;
    border-color: #d9d9d9;
    border-radius: 6px;
    transition: all 0.2s;
    -webkit-appearance: none;
    touch-action: manipulation;
    text-overflow: ellipsis;
}
.dt-input:hover {
    border-color: #4096ff;
    border-inline-end-width: 1px;
}
.dt-input:active {
    border-color: #4096ff;
    box-shadow: 0 0 0 2px rgba(5, 145, 255, 0.1);
    border-inline-end-width: 1px;
    outline: 0;
}
.dt-input:focus {
    border-color: #4096ff;
    box-shadow: 0 0 0 2px rgba(5, 145, 255, 0.1);
    border-inline-end-width: 1px;
    outline: 0;
}
.dt-input::placeholder {
    color: rgb(191, 191, 191);
    font-weight: 400;
}
</style>
