<script setup>
import {computed, reactive, ref, watch} from "vue";
import {debounce} from "radash";
import {useSuggests} from "../../stores/models/suggests.js";
import axios from "axios";
import {UploadOutlined} from "@ant-design/icons-vue";

const model = defineModel()
const prop = defineProps({
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: null },
    readOnly: {type: Boolean, default: false }
})

const suggests = useSuggests()

const err = reactive({
    name_short: null, name_full: null
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

const firmOptions = computed(() => {
    let res = [...firmList.value]
    return res.map(el => ({value: el.inn, ...el}))
})

const firmList = ref([])

const handleFirmSearch = debounce({delay: 500}, async q => {
    if (q.length > 3) {
        firmList.value = await suggests.firmSuggest(q)
    }
})

const handleFirmChange = (e) => {
    const firm = firmList.value.find(el => el.inn === e)
    if (firm) {
        model.value.name_short = firm.name_short
        model.value.name_full = firm.name_full
        model.value.inn = firm.inn
        model.value.ogrn = firm.ogrn
        if (firm.type === 'LEGAL') {
            model.value.kpp = firm.kpp
        }
    }
}

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

const uploadFile = async (fileObject) => {
    const formData = new FormData();
    formData.append('file', fileObject.file)
    try {
        const { data } = await axios.post(fileObject.action, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            },
            onUploadProgress(progressEvent) {
                fileObject.onProgress({
                    percent: parseInt((progressEvent.loaded / progressEvent.total) * 100)
                })
            }
        });
        fileObject.onSuccess(data)
    } catch (e) {
        fileObject.onError(e)
        console.log(e)
    } finally {

    }
}

const clientFileObjects = ref([])
const carrierFileObjects = ref([])

const templateClientFileList = computed(() => {
    if (clientFileObjects.value.length > 0) {
        return clientFileObjects.value
    }
    if (model.value.template_client) {
        return [{name: model.value.template_client}]
    }
    return []
})

const templateCarrierFileList = computed(() => {
    if (carrierFileObjects.value.length > 0) {
        return carrierFileObjects.value
    }
    if (model.value.template_carrier) {
        return [{name: model.value.template_carrier}]
    }
    return []
})


const handleTemplateChange = (p, t) => {
    if (t === 'client') {
        if (!!p.file.response) {
            model.value.template_client = p.file.response.name
        }
        clientFileObjects.value = p.fileList
    }
    if (t === 'carrier') {
        if (!!p.file.response) {
            model.value.template_carrier = p.file.response.name
        }
        carrierFileObjects.value = p.fileList
    }
}

const handleTemplateRemove = t => {
    if (t === 'client') {
        model.value.template_client = null
        clientFileObjects.value = []
    }
    if (t === 'carrier') {
        model.value.template_carrier = null
        carrierFileObjects.value = []
    }
}
</script>

<template>
    <a-form layout="vertical" :model="model" :disabled="readOnly">
        <a-form-item label="Краткое наименование" name="name_short" :validate-status="err.name_short ? 'error': undefined" :help="err.name_short">
            <a-input
                v-model:value="model.name_short"
                placeholder="Краткое наименование"
            />
        </a-form-item>
        <a-form-item label="Полное наименование" name="name_full" :validate-status="err.name_full ? 'error': undefined" :help="err.name_full">
            <a-input
                v-model:value="model.name_full"
                placeholder="Полное наименование"
            />
        </a-form-item>
        <a-form-item label="ИНН" name="inn" :validate-status="err.inn ? 'error': undefined" :help="err.inn">
            <a-select
                show-search
                v-model:value="model.inn"
                placeholder="Введите ИНН компании"
                :filter-option="false"
                :not-found-content="suggests.isLoading ? undefined : null"
                @search="handleFirmSearch"
                @change="handleFirmChange"
                :options="firmOptions"
            >
                <template #option="rec">
                    <div style="display: flex; justify-content: space-between; align-items: center">
                        <div>{{ rec.name_short }}</div>
                        <div style="font-size: 11px; font-weight: 500">
                            {{ rec.inn }}
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
        <a-form-item label="КПП" name="kpp" :validate-status="err.kpp ? 'error': undefined" :help="err.kpp">
            <a-input
                v-model:value="model.kpp"
                placeholder="КПП компании"
                :maxlength="9"
            />
        </a-form-item>
        <a-form-item label="ОГРН" name="ogrn" :validate-status="err.ogrn ? 'error': undefined" :help="err.ogrn">
            <a-input
                v-model:value="model.ogrn"
                placeholder="ОГРН компании"
                :maxlength="9"
            />
        </a-form-item>
        <a-divider orientation="left">Подписант</a-divider>
        <a-form-item label="ФИО" name="sign_name" :validate-status="err.sign_name ? 'error': undefined" :help="err.sign_name">
            <a-input
                v-model:value="model.sign_name"
                placeholder="ФИО подписанта"
            />
        </a-form-item>
        <a-form-item label="Должность" name="sign_position" :validate-status="err.sign_position ? 'error': undefined" :help="err.sign_position">
            <a-input
                v-model:value="model.sign_position"
                placeholder="Должность"
            />
        </a-form-item>
        <a-divider orientation="left">Банковские реквизиты</a-divider>
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
        <a-form-item label="Наименование банка" name="bank_name" :validate-status="err.bank_name ? 'error': undefined" :help="err.bank_name">
            <a-input
                v-model:value="model.bank_name"
                placeholder="Наименование банка"
            />
        </a-form-item>
        <a-form-item label="Город" name="payment_city" :validate-status="err.payment_city ? 'error': undefined" :help="err.payment_city">
            <a-input
                v-model:value="model.payment_city"
                placeholder="Город банка"
            />
        </a-form-item>
        <a-form-item label="Корреспондентский счет" name="account_correspondent" :validate-status="err.account_correspondent ? 'error': undefined" :help="err.account_correspondent">
            <a-input
                v-model:value="model.account_correspondent"
                placeholder="Корреспондентский счет"
            />
        </a-form-item>
        <a-form-item label="Расчетный счет" name="account_payment" :validate-status="err.account_payment ? 'error': undefined" :help="err.account_payment">
            <a-input
                v-model:value="model.account_payment"
                placeholder="Расчетный счет"
            />
        </a-form-item>
        <a-divider orientation="left">Файлы шаблонов заявок</a-divider>
        <a-row :gutter="16">
            <a-col :span="12">
                <a-form-item label="Шаблон для клиента" name="template_client" :validate-status="err.template_client ? 'error': undefined" :help="err.template_client">
                    <a-upload
                        action="/upload/order-template"
                        accept="*.dotx"
                        list-type="text"
                        :file-list="templateClientFileList"
                        :with-credentials="true"
                        :custom-request="uploadFile"
                        @change="(files) => handleTemplateChange(files, 'client')"
                        @remove="() => handleTemplateRemove('client')"
                    >
                        <a-button v-if="!model.template_client">
                            <UploadOutlined /> Загрузить шаблон
                        </a-button>
                    </a-upload>
                </a-form-item>
            </a-col>
            <a-col :span="12">
                <a-form-item label="Шаблон для перевозчика" name="template_carrier" :validate-status="err.template_carrier ? 'error': undefined" :help="err.template_carrier">
                    <a-upload
                        action="/upload/order-template"
                        accept="*.dotx"
                        list-type="text"
                        :file-list="templateCarrierFileList"
                        :with-credentials="true"
                        :custom-request="uploadFile"
                        @change="(files) => handleTemplateChange(files, 'carrier')"
                        @remove="() => handleTemplateRemove('carrier')"
                    >
                        <a-button v-if="!model.template_carrier">
                            <UploadOutlined /> Загрузить шаблон
                        </a-button>
                    </a-upload>
                </a-form-item>
            </a-col>
        </a-row>
    </a-form>
</template>

<style>
input, select, textarea, .ant-select-selector {
    color: #1a202c !important;
}
</style>
