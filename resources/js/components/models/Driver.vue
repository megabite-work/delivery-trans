<script setup>
import {reactive, ref} from "vue";
import axios from "axios";
import {message} from "ant-design-vue";

const model = defineModel()
const prop = defineProps({ loading: { type: Boolean, default: false }, errors: { type: Object, default: null } })

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


const err = reactive({})
// watch(() => prop.errors, () => {
//     Object.keys(err).forEach((key) => {
//         if (prop.errors[key]) {
//             err[key] = prop.errors[key][0]
//             return
//         }
//         err[key] = null
//     })
// })
</script>

<template>
<a-form layout="vertical" :model="model">
    <a-row :gutter="16">
        <a-col :span="20">
            <a-form-item label="Фамилия">
                <a-input v-model:value="model.surname" placeholder="Фамилия водителя"/>
            </a-form-item>
        </a-col>
        <a-col :span="4">
            <a-form-item label="Активен" name="is_active" :validate-status="err.is_active ? 'error' : undefined" :help="err.is_active">
                <a-switch v-model:checked="model.is_active" />
            </a-form-item>
        </a-col>
    </a-row>
    <a-form-item label="Имя">
        <a-input v-model:value="model.name" placeholder="Имя водителя" />
    </a-form-item>
    <a-form-item label="Отчество">
        <a-input v-model:value="model.patronymic" placeholder="Отчество водителя" />
    </a-form-item>
    <a-form-item label="Дата рождения">
        <a-date-picker
            v-model:value="model.birthday"
            format="DD.MM.YYYY"
            placeholder="Дата рождения"
            style="width: 200px"
            value-format="string"
        />
    </a-form-item>
    <a-row :gutter="16">
        <a-col :span="12">
            <a-form-item label="Номер телефона">
                <a-input placeholder="Номер телефона" v-model:value="model.phone" />
            </a-form-item>
        </a-col>
        <a-col :span="12">
            <a-form-item label="Электропочта">
                <a-input placeholder="Электропочта" v-model:value="model.email" />
            </a-form-item>
        </a-col>
    </a-row>
    <a-divider>Права</a-divider>
    <a-row :gutter="16">
        <a-col :span="12">
            <a-form-item label="Номер водительского">
                <a-input placeholder="Номер удостоверения" v-model:value="model.license_number" />
            </a-form-item>
        </a-col>
        <a-col :span="12">
            <a-form-item label="Действуют по">
                <a-date-picker
                    v-model:value="model.license_expiration"
                    format="DD.MM.YYYY"
                    placeholder="Действуют по"
                    style="width: 100%"
                    value-format="string"
                />
            </a-form-item>
        </a-col>
    </a-row>
    <a-divider>Паспортные данные</a-divider>
    <a-form-item label="Гражданство">
        <a-select
            ref="select"
            placeholder="Гражданство"
            v-model:value="model.citizenship"
            :options="citizenshipOptionsList"
            :loading="citizenshipOptionsLoading"
            @focus="getCitizenshipOptionsList"
        />
    </a-form-item>
    <a-form-item label="Серия и номер">
        <a-input placeholder="Серия и номер паспорта" v-model:value="model.passport_number" />
    </a-form-item>
    <a-form-item label="Кем выдан">
        <a-textarea
            :auto-size="true"
            placeholder="Кем выдан паспорт"
            v-model:value="model.passport_issuer"
        />
    </a-form-item>
    <a-row :gutter="16">
        <a-col :span="12">
            <a-form-item label="Код подразделения">
                <a-input placeholder="Код подразделения" v-model:value="model.passport_issuer_code" />
            </a-form-item>
        </a-col>
        <a-col :span="12">
            <a-form-item label="Дата выдачи">
                <a-date-picker
                    v-model:value="model.passport_issue_date"
                    format="DD.MM.YYYY"
                    placeholder="Дата выдачи"
                    style="width: 200px"
                />
            </a-form-item>
        </a-col>
    </a-row>
    <a-form-item label="Адрес регистрации">
        <a-input placeholder="Адрес регистрации" v-model:value="model.registration_address" />
    </a-form-item>

</a-form>
</template>

<style scoped>

</style>
