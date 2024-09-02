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
            <div><b>id</b> - Номер заявки</div>
            <div><b>cargo_name</b> - Наименование груза</div>
            <div><b>cargo_weight</b> - Вес груза</div>
            <div><b>cargo_temp</b> - Температурные условия</div>
            <div><b>cargo_pallets</b> - Количество палет</div>
            <div><b>car_capacity</b> - Параметры вместимости авто (объем / вес / палет)</div>
            <div><b>car_body_type</b> - Тип авто</div>
            <div><b>car_loading</b> - Варианты загрузки</div>
            <div><b>client_name_short</b> - Краткое наименование клиента</div>
            <div><b>client_name_full</b> - Полное наименование клиента</div>
            <div><b>client_inn</b> - ИНН клиента</div>
            <div><b>client_kpp</b> - КПП клиента</div>
            <div><b>client_ogrn</b> - ОГРН клиента</div>
            <div><b>client_vat</b> - Условия НДС клиента</div>
            <div><b>client_bank</b> - Банковские реквизиты клиента строкой</div>
            <div><b>client_bank_bik</b> - БИК банка клиента</div>
            <div><b>client_bank_name</b> - Наименование банка клиента</div>
            <div><b>client_bank_city</b> - Город банка</div>
            <div><b>client_bank_correspondent</b> - Корреспондентский счет банка клиента</div>
            <div><b>client_bank_account</b> - Номер счета клиента</div>
            <div><b>client_address_yur</b> - Юридический адрес клиента</div>
            <div><b>client_address_real</b> - Фактически адрес клиента</div>
            <div><b>client_address_post</b> - Почтовый адрес клиента</div>
            <div><b>carrier_name_short</b> - Краткое наименование перевозчика</div>
            <div><b>carrier_name_full</b> - Полное наименование перевозчика</div>
            <div><b>carrier_inn</b> - ИНН перевозчика</div>
            <div><b>carrier_kpp</b> - КПП перевозчика</div>
            <div><b>carrier_ogrn</b> - ОГРН перевозчика</div>
            <div><b>carrier_vat</b> - Условия НДС перевозчика</div>
            <div><b>carrier_bank</b> - Банковские реквизиты перевозчика строкой</div>
            <div><b>carrier_bank_bik</b> - БИК банка перевозчика</div>
            <div><b>carrier_bank_name</b> - Наименование банка перевозчика</div>
            <div><b>carrier_bank_city</b> - Город банка</div>
            <div><b>carrier_bank_correspondent</b> - Корреспондентский счет банка перевозчика</div>
            <div><b>carrier_bank_account</b> - Номер счета перевозчика</div>
            <div><b>carrier_address_yur</b> - Юридический адрес перевозчика</div>
            <div><b>carrier_address_real</b> - Фактический адрес перевозчика</div>
            <div><b>carrier_address_post</b> - Почтовый адрес перевозчика</div>
            <div><b>driver_name_full</b> - ФИО водителя</div>
            <div><b>driver_name_short</b> - Фимилия И.О. водителя</div>
            <div><b>driver_inn</b> - ИНН водителя</div>
            <div><b>driver_passport</b> - Паспортные данные водителя</div>
            <div><b>driver_passport_issuer</b> - Кем выдан паспорт</div>
            <div><b>driver_passport_issue_date</b> - Когда выдан паспорт</div>
            <div><b>driver_passport_issuer_code</b> - Код подразделения</div>
            <div><b>driver_phone</b> - Номер телефона водителя</div>
            <div><b>driver_email</b> - Электропочта водителя</div>
            <div><b>driver_license_number</b> - Номер водительского удостоверения</div>
            <div><b>driver_license_expiration</b> - Срок действия водительского удостоверения</div>
            <div><b>car_name</b> - Наименование авто</div>
            <div><b>car_type</b> - Тип авто</div>
            <div><b>car_plate</b> - Госномер авто</div>
            <div><b>car_sts_number</b> - Номер СТС</div>
            <div><b>car_sts_date</b> - Дата СТС</div>
            <div><b>car_b_type</b> - Тип кузова</div>
            <div><b>trailer_name</b> - Наименование прицепа</div>
            <div><b>trailer_type</b> - Тип прицепа</div>
            <div><b>trailer_plate</b> - Госномер прицепа</div>
            <div><b>trailer_sts_number</b> - Номер СТС прицепа</div>
            <div><b>trailer_sts_date</b> - Дата СТС прицепа</div>
            <div><b>trailer_b_type</b> - Тип кузова прицепа</div>
            <div><b>client_tariff_hourly</b> - Тариф для клиента в час</div>
            <div><b>client_tariff_min_hours</b> - Минимум часов в тарифе</div>
            <div><b>client_tariff_hours_for_coming</b> - Чесов на подачу</div>
            <div><b>client_tariff_mkad_rate</b> - Количество км. за МКАД</div>
            <div><b>client_tariff_mkad_price</b> - Тариф за км за МКАД</div>
            <div><b>carrier_tariff_hourly</b> - Тариф для поставщика в час</div>
            <div><b>carrier_tariff_min_hours</b> - Минимум часов в тарифе</div>
            <div><b>carrier_tariff_hours_for_coming</b> - Часов на подачу</div>
            <div><b>carrier_tariff_mkad_rate</b> - Количество км. за МКАД</div>
            <div><b>carrier_tariff_mkad_price</b> - Тариф за км за МКАД</div>
            <div><b>client_expenses</b> - Расходы клиента</div>
            <div><b>client_discounts</b> - Скидки клиента</div>
            <div><b>carrier_expenses</b> - Расходы перевозчика</div>
            <div><b>carrier_fines</b> - Штрафы перевозчика</div>
            <div><b>from_location_address</b> - Адрез отправки</div>
            <div><b>from_location_contact_name</b> - Контактное лицо</div>
            <div><b>from_location_contact_phone</b> - Телефон контактного лица</div>
            <div><b>to_location_address</b> - Адрес доставки</div>
            <div><b>to_location_contact_name</b> - Контактное лицо</div>
            <div><b>to_location_contact_phone</b> - Телефон контактного лица</div>
            <div><b>additional_service</b> - Дополнительные услуги</div>
            <div><b>client_sum</b> - Сумма для клиента</div>
            <div><b>carrier_sum</b> - Сумма для поставщика</div>
            <div><b>started_at</b> - Дата и время старта</div>
            <div><b>created_at</b> - Дата и время заявки</div>
            <div><b>updated_at</b> - Дата и время последнего изменения заявки</div>
            <div><b>ended_at</b> - Дата и время завершения</div>
            <div><b>client_sum_author</b> - Автор суммы для клиента</div>
            <div><b>carrier_sum_author</b> - Автор суммы для поставщика</div>
            <div><b>margin_sum</b> - Маржа ₽</div>
            <div><b>margin_percent</b> - Маржа в %</div>
            <div><b>hours_client</b> - Часов к оплате клиенту</div>
            <div><b>hours_carrier</b> - Часов к оплате перевозчику</div>
            <div><b>date_start</b> - Дата начала выполнения</div>
            <div><b>date_end</b> - Дата окончания выполнения</div>
            <div><b>time_start</b> - Время начала выполнеия</div>
            <div><b>time_end</b> - Время окончания выполнения</div>
            <div><b>company_name_short</b> - Краткое наименовании компании</div>
            <div><b>company_name_full</b> - Полное наименование компании</div>
            <div><b>company_inn</b> - ИНН компании</div>
            <div><b>company_kpp</b> - КПП компании</div>
            <div><b>company_ogrn</b> - ОГРН компании</div>
            <div><b>company_vat</b> - Налоговый режим компании</div>
            <div><b>company_bank</b> - Банковские реквизиты компании</div>
            <div><b>company_bank_bik</b> - БИК банка</div>
            <div><b>company_bank_name</b> - Наименование банка</div>
            <div><b>company_bank_city</b> - Город банка</div>
            <div><b>company_bank_correspondent</b> - Корреспондентский счет</div>
            <div><b>company_bank_account</b> - Расчетный счет</div>
            <div><b>company_sign_position</b> - Должность подписанта</div>
            <div><b>company_sign_name</b> - Имя подписанта</div>
    </a-modal>
</template>

<style>
input, select, textarea, .ant-select-selector {
    color: #1a202c !important;
}
</style>
