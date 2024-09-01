<script setup>
import {computed, reactive, ref, watch, h} from "vue";
import {debounce} from "radash";
import {useSuggests} from "../../stores/models/suggests.js";
import axios from "axios";
import {UploadOutlined, QuestionOutlined} from "@ant-design/icons-vue";

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
const helpOpened = ref(false)

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
        <a-divider orientation="left">
            Файлы шаблонов заявок&nbsp;&nbsp;
            <a-button size="small" :icon="h(QuestionOutlined)" shape="circle" @click="() => helpOpened = true"/>
        </a-divider>
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
    <a-modal v-model:open="helpOpened" title="Справка для шаблонов">
        <p>Атрибут в .dotx шаблоне задается плейсхолдером <b>${attribute}</b></p>
        <h4>Доступные атрибуты</h4>
            <div>id</div>
            <div>cargo_name</div>
            <div>cargo_weight</div>
            <div>cargo_temp</div>
            <div>cargo_pallets</div>
            <div>car_capacity</div>
            <div>car_body_type</div>
            <div>car_loading</div>
            <div>client_name_short</div>
            <div>client_name_full</div>
            <div>client_inn</div>
            <div>client_kpp</div>
            <div>client_ogrn</div>
            <div>client_vat</div>
            <div>client_bank</div>
            <div>client_bank_bik</div>
            <div>client_bank_name</div>
            <div>client_bank_city</div>
            <div>client_bank_correspondent</div>
            <div>client_bank_account</div>
            <div>client_address_yur</div>
            <div>client_address_real</div>
            <div>client_address_post</div>
            <div>carrier_name_short</div>
            <div>carrier_name_full</div>
            <div>carrier_inn</div>
            <div>carrier_kpp</div>
            <div>carrier_ogrn</div>
            <div>carrier_vat</div>
            <div>carrier_bank</div>
            <div>carrier_bank_bik</div>
            <div>carrier_bank_name</div>
            <div>carrier_bank_city</div>
            <div>carrier_bank_correspondent</div>
            <div>carrier_bank_account</div>
            <div>carrier_address_yur</div>
            <div>carrier_address_real</div>
            <div>carrier_address_post</div>
            <div>driver_name_full</div>
            <div>driver_name_short</div>
            <div>driver_inn</div>
            <div>driver_passport</div>
            <div>driver_passport_issuer</div>
            <div>driver_passport_issue_date</div>
            <div>driver_passport_issuer_code</div>
            <div>driver_phone</div>
            <div>driver_email</div>
            <div>driver_license_number</div>
            <div>driver_license_expiration</div>
            <div>car_name</div>
            <div>car_type</div>
            <div>car_plate</div>
            <div>car_sts_number</div>
            <div>car_sts_date</div>
            <div>car_b_type</div>
            <div>trailer_name</div>
            <div>trailer_type</div>
            <div>trailer_plate</div>
            <div>trailer_sts_number</div>
            <div>trailer_sts_date</div>
            <div>trailer_b_type</div>
            <div>client_tariff_hourly</div>
            <div>client_tariff_min_hours</div>
            <div>client_tariff_hours_for_coming</div>
            <div>client_tariff_mkad_rate</div>
            <div>client_tariff_mkad_price</div>
            <div>carrier_tariff_hourly</div>
            <div>carrier_tariff_min_hours</div>
            <div>carrier_tariff_hours_for_coming</div>
            <div>carrier_tariff_mkad_rate</div>
            <div>carrier_tariff_mkad_price</div>
            <div>client_expenses</div>
            <div>client_discounts</div>
            <div>carrier_expenses</div>
            <div>carrier_fines</div>
            <div>from_location_address</div>
            <div>from_location_contact_name</div>
            <div>from_location_contact_phone</div>
            <div>to_location_address</div>
            <div>to_location_contact_name</div>
            <div>to_location_contact_phone</div>
            <div>additional_service</div>
            <div>client_sum</div>
            <div>carrier_sum</div>
            <div>started_at</div>
            <div>created_at</div>
            <div>updated_at</div>
            <div>ended_at</div>
            <div>client_sum_author</div>
            <div>carrier_sum_author</div>
            <div>margin_sum</div>
            <div>margin_percent</div>
            <div>hours_client</div>
            <div>hours_carrier</div>
            <div>date_start</div>
            <div>date_end</div>
            <div>time_start</div>
            <div>time_end</div>
            <div>company_name_short</div>
            <div>company_name_full</div>
            <div>company_inn</div>
            <div>company_kpp</div>
            <div>company_ogrn</div>
            <div>company_vat</div>
            <div>company_bank</div>
            <div>company_bank_bik</div>
            <div>company_bank_name</div>
            <div>company_bank_city</div>
            <div>company_bank_correspondent</div>
            <div>company_bank_account</div>
            <div>company_sign_position</div>
            <div>company_sign_name</div>
    </a-modal>
</template>

<style>
input, select, textarea, .ant-select-selector {
    color: #1a202c !important;
}
</style>
