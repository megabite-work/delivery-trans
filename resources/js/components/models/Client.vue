<script setup>
import {onBeforeUnmount, onMounted, reactive, ref, watch} from "vue";

import {message} from "ant-design-vue";

import Drawer from "../Drawer.vue";
import BankAccount from "./BankAccount.vue";
import Contact from "./Contact.vue";
import {useClientsStore} from "../../stores/models/clients.js";
import {useContactsStore} from "../../stores/models/contacts.js";
import {useBankAccountsStore} from "../../stores/models/bankAccounts.js";

const model = defineModel()
const prop = defineProps({ loading: { type: Boolean, default: false }, errors: { type: Object, default: null } })

const clientsStore = useClientsStore()
const contactsStore = useContactsStore()
const bankAccountsStore = useBankAccountsStore()

const clientHeight = ref(document.documentElement.clientHeight)

const columnsContacts = [
    { key: 'type', title: 'Тип', dataIndex: 'type', width: 50 },
    { title: 'Значение', dataIndex: 'value', width: '100%' },
    { title: 'Описание', dataIndex: 'note', width: 150 },
];

const columnsBankAccounts = [
    { title: '№ счета', dataIndex: 'account_payment', width: '100%' },
    { title: 'Банк', dataIndex: 'bank_name', width: 200 },
    { title: 'Город', dataIndex: 'payment_city', width: 150 },
];

const contactDrawer = reactive({ isOpen: false, isSaving: false })
const accountDrawer = reactive({ isOpen: false, isSaving: false })

const openContactDrawer = (contact = null) => {
    contactDrawer.isOpen = true
    currentContact.modified = false
    currentContact.data = contact === null ? {
        id: null,
        owner_id: model.value.id,
        owner_type: 'App\\Models\\Client',
    } : { ...contact }
}

const openAccountDrawer = (account = null) => {
    accountDrawer.isOpen = true
    currentAccount.modified = false
    currentAccount.data = account === null ? {
        id: null,
        owner_id: model.value.id,
        owner_type: 'App\\Models\\Client',
    } : { ...account }
}

const currentContact = reactive({ data:{}, modified: false })
const currentAccount = reactive({ data:{}, modified: false })

const saveContact = async () => {
    contactDrawer.isSaving = true
    try {
        if (currentContact.data.id === null) {
            currentContact.data  = await contactsStore.createContact(currentContact.data)
            currentContact.modified = false
            message.success('Контакт заказчика создан')
        } else {
            currentContact.data = await contactsStore.storeContact(currentContact.data)
            currentContact.modified = false
            message.success('Контакт заказчика записан')
        }
        model.value.contacts = await contactsStore.getContacts(model.value.id, 'clients')
        contactDrawer.isOpen = false
    } catch (e) {
        message.error(`Ошибка. Не удалось ${model.value.id === null ? 'создать' : 'сохранить'} контакт заказчика`)
    } finally {
        contactDrawer.isSaving = false
    }
}

const saveBankAccount = async () => {
    accountDrawer.isSaving = true
    try {
        if (currentAccount.data.id === null) {
            currentAccount.data  = await bankAccountsStore.createBankAccount(currentAccount.data)
            currentAccount.modified = false
            message.success('Запись счета заказчика создана')
        } else {
            currentAccount.data = await bankAccountsStore.storeBankAccount(currentAccount.data)
            currentAccount.modified = false
            message.success('Запись счета заказчика записана')
        }
        model.value.bank_accounts = await bankAccountsStore.getBankAccounts(model.value.id, 'clients')
        accountDrawer.isOpen = false
    } catch (e) {
        message.error(`Ошибка. Не удалось ${currentAccount.data.id === null ? 'создать' : 'сохранить'} запись банковского счета заказчика`)
    } finally {
        accountDrawer.isSaving = false
    }
}

const deleteClientContact = async () => {
    if (currentContact.data.id === null) {
        return
    }
    try {
        await contactsStore.deleteContact(currentContact.data.id)
        message.success('Запись успешно удалена')
        model.value.contacts = await contactsStore.getContacts(model.value.id, 'clients')
        contactDrawer.isOpen = false
    } catch (e) {
        message.error('Ошибка. Не удалось удалить запись')
    }
}

const deleteClientBankAccount = async () => {
    if (currentAccount.data.id === null) {
        return
    }
    try {
        await bankAccountsStore.deleteBankAccount(currentAccount.data.id)
        message.success('Запись успешно удалена')
        model.value.bank_accounts = await bankAccountsStore.getBankAccounts(model.value.id, 'clients')
        accountDrawer.isOpen = false
    } catch (e) {
        message.error('Ошибка. Не удалось удалить запись')
    }
}

const currentTab = ref('contacts')
const updateClientHeight = () => { clientHeight.value = document.documentElement.clientHeight }
onMounted(() => { window.addEventListener('resize', updateClientHeight) })
onBeforeUnmount(() => { window.removeEventListener('resize', updateClientHeight) })
const contactsTableRowFn = record => ({ onClick: () => openContactDrawer(record) })
const accountsTableRowFn = record => ({ onClick: () => openAccountDrawer(record) })

const err = reactive({
    type: null, inn: null, kpp: null, ogrn: null, name_short: null, name_full: null
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
    <drawer
        v-model:open="contactDrawer.isOpen"
        @close="contactDrawer.isOpen = false"
        @save="saveContact"
        @delete="deleteClientContact"
        :saving="contactDrawer.isSaving"
        delete-text=""
        ok-text="Сохранить и закрыть"
        need-deletion-confirm-text="Вы уверены? Контакт будет удален!"
        :title="currentContact.data.id === null ? 'Новый контакт' : `Контакт #${currentContact.data.id}`"
        :width="500"
    >
        <Contact v-model="currentContact.data" :errors="clientsStore.clientContactErr?.errors" />
    </drawer>

    <drawer
        v-model:open="accountDrawer.isOpen"
        @close="accountDrawer.isOpen = false"
        @save="saveBankAccount"
        @delete="deleteClientBankAccount"
        :saving="accountDrawer.isSaving"
        delete-text=""
        ok-text="Сохранить и закрыть"
        need-deletion-confirm-text="Вы уверены? Запись будет удалена!"
        :title="currentAccount.data.id === null ? 'Новый реквизит' : `Реквизит #${currentAccount.data.id}`"
        :width="500"
    >
        <BankAccount v-model="currentAccount.data" :errors="clientsStore.clientAccountErr?.errors"/>
    </drawer>

    <a-form layout="vertical" :model="model">
        <a-form-item label="Тип" name="type" :validate-status="err.type ? 'error': undefined" :help="err.type">
            <a-select
                placeholder="Тип заказчика"
                ref="select"
                style="width: 220px"
                v-model:value = "model.type"
            >
                <a-select-option value="LEGAL">
                    <div :style="{display: 'flex', alignItems: 'center'}">
                        <svg :style="{width: '16px', height: '16px', color: '#6b7280', marginRight: '8px'}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 21v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m0 0h4.5V3.545M12.75 21h7.5V10.75M2.25 21h1.5m18 0h-18M2.25 9l4.5-1.636M18.75 3l-1.5.545m0 6.205 3 1m1.5.5-1.5-.5M6.75 7.364V3h-3v18m3-13.636 10.5-3.819" />
                        </svg>
                        Юридическое лицо
                    </div>
                </a-select-option>

                <a-select-option value="INDIVIDUAL">
                    <div :style="{display: 'flex', alignItems: 'center'}">
                        <svg :style="{width: '16px', height: '16px', color: '#6b7280', marginRight: '8px'}" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        Предприниматель
                    </div>
                </a-select-option>
            </a-select>
        </a-form-item>
        <a-row :gutter="16">
            <a-col :span="model.type === 'LEGAL' ? 12 : 24">
                <a-form-item label="ИНН" name="inn" :validate-status="err.inn ? 'error': undefined" :help="err.inn">
                    <a-input
                        v-model:value="model.inn"
                        placeholder="Введите ИНН заказчика"
                        :maxlength="model.type === 'LEGAL' ? 10 : 12"
                    />
                </a-form-item>
            </a-col>
            <a-col v-if="model.type === 'LEGAL'" :span="12">
                <a-form-item label="КПП" name="kpp" :validate-status="err.kpp ? 'error': undefined" :help="err.kpp">
                    <a-input
                        v-model:value="model.kpp"
                        placeholder="Введите КПП заказчика"
                        :maxlength="9"
                    />
                </a-form-item>
            </a-col>
        </a-row>
        <a-form-item :label="model.type === 'LEGAL' ? 'ОГРН' : 'ОГРНИП'" name="ogrn" :validate-status="err.ogrn ? 'error': undefined" :help="err.ogrn">
            <a-input
                v-model:value="model.ogrn"
                placeholder="Введите ОГРН заказчика"
                :maxlength="model.type === 'LEGAL' ? 13 : 15"
            />
        </a-form-item>
        <a-form-item label="Краткое наименование" name="name_short" :validate-status="err.name_short ? 'error': undefined" :help="err.name_short">
            <a-input v-model:value="model.name_short" placeholder="Введите краткое наименование заказчика" />
        </a-form-item>
        <a-form-item label="Полное наименование" name="name_full" :validate-status="err.name_full ? 'error': undefined" :help="err.name_full">
            <a-input v-model:value="model.name_full" placeholder="Введите полное наименование заказчика" />
        </a-form-item>
    </a-form>
    <a-tabs v-model:activeKey="currentTab" >
        <a-tab-pane key="contacts" tab="Контакты">
            <a-table
                size="small"
                :custom-row="contactsTableRowFn"
                :columns="columnsContacts"
                :data-source="model.contacts"
                :pagination="false"
                :scroll="{ y: clientHeight - 686}"
                :row-class-name="() => 'cursor-pointer'"
            >
                <template #bodyCell="{ column, record }">
                    <template v-if="column.key === 'type'">
                        <div :style="{display: 'flex', color: 'rgba(0, 0, 0, 0.88)'}">
                            <a-tooltip>
                                <template v-if="record.type === 'PHONE'" #title>Телефон</template>
                                <template v-else-if="record.type === 'EMAIL'" #title>Email</template>
                                <template v-else-if="record.type === 'PERSON'" #title>Контактное лицо</template>
                                <template v-else-if="record.type === 'MESSENGER'" #title>Месседжер</template>
                                <template v-else-if="record.type === 'ADDRESS'" #title>Адрес</template>
                                <template v-else-if="record.type === 'WEB'" #title>Сайт</template>
                                <!-- ------------------------ -->
                                <template v-if="record.type === 'PHONE'">
                                    <svg :style="{width: '16px', height: '16px'}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd" d="m3.855 7.286 1.067-.534a1 1 0 0 0 .542-1.046l-.44-2.858A1 1 0 0 0 4.036 2H3a1 1 0 0 0-1 1v2c0 .709.082 1.4.238 2.062a9.012 9.012 0 0 0 6.7 6.7A9.024 9.024 0 0 0 11 14h2a1 1 0 0 0 1-1v-1.036a1 1 0 0 0-.848-.988l-2.858-.44a1 1 0 0 0-1.046.542l-.534 1.067a7.52 7.52 0 0 1-4.86-4.859Z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <template v-else-if="record.type === 'EMAIL'">
                                    <svg :style="{width: '16px', height: '16px'}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd" d="M11.89 4.111a5.5 5.5 0 1 0 0 7.778.75.75 0 1 1 1.06 1.061A7 7 0 1 1 15 8a2.5 2.5 0 0 1-4.083 1.935A3.5 3.5 0 1 1 11.5 8a1 1 0 0 0 2 0 5.48 5.48 0 0 0-1.61-3.889ZM10 8a2 2 0 1 0-4 0 2 2 0 0 0 4 0Z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <template v-else-if="record.type === 'PERSON'">
                                    <svg :style="{width: '16px', height: '16px'}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4">
                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6ZM12.735 14c.618 0 1.093-.561.872-1.139a6.002 6.002 0 0 0-11.215 0c-.22.578.254 1.139.872 1.139h9.47Z" />
                                    </svg>
                                </template>
                                <template v-else-if="record.type === 'MESSENGER'">
                                    <svg :style="{width: '16px', height: '16px'}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4">
                                        <path d="M1 8.849c0 1 .738 1.851 1.734 1.947L3 10.82v2.429a.75.75 0 0 0 1.28.53l1.82-1.82A3.484 3.484 0 0 1 5.5 10V9A3.5 3.5 0 0 1 9 5.5h4V4.151c0-1-.739-1.851-1.734-1.947a44.539 44.539 0 0 0-8.532 0C1.738 2.3 1 3.151 1 4.151V8.85Z" />
                                        <path d="M7 9a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2h-.25v1.25a.75.75 0 0 1-1.28.53L9.69 12H9a2 2 0 0 1-2-2V9Z" />
                                    </svg>
                                </template>
                                <template v-else-if="record.type === 'ADDRESS'">
                                    <svg :style="{width: '16px', height: '16px'}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd" d="m7.539 14.841.003.003.002.002a.755.755 0 0 0 .912 0l.002-.002.003-.003.012-.009a5.57 5.57 0 0 0 .19-.153 15.588 15.588 0 0 0 2.046-2.082c1.101-1.362 2.291-3.342 2.291-5.597A5 5 0 0 0 3 7c0 2.255 1.19 4.235 2.292 5.597a15.591 15.591 0 0 0 2.046 2.082 8.916 8.916 0 0 0 .189.153l.012.01ZM8 8.5a1.5 1.5 0 1 0 0-3 1.5 1.5 0 0 0 0 3Z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <template v-else-if="record.type === 'WEB'">
                                    <svg :style="{width: '16px', height: '16px'}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd" d="M3.757 4.5c.18.217.376.42.586.608.153-.61.354-1.175.596-1.678A5.53 5.53 0 0 0 3.757 4.5ZM8 1a6.994 6.994 0 0 0-7 7 7 7 0 1 0 7-7Zm0 1.5c-.476 0-1.091.386-1.633 1.427-.293.564-.531 1.267-.683 2.063A5.48 5.48 0 0 0 8 6.5a5.48 5.48 0 0 0 2.316-.51c-.152-.796-.39-1.499-.683-2.063C9.09 2.886 8.476 2.5 8 2.5Zm3.657 2.608a8.823 8.823 0 0 0-.596-1.678c.444.298.842.659 1.182 1.07-.18.217-.376.42-.586.608Zm-1.166 2.436A6.983 6.983 0 0 1 8 8a6.983 6.983 0 0 1-2.49-.456 10.703 10.703 0 0 0 .202 2.6c.72.231 1.49.356 2.288.356.798 0 1.568-.125 2.29-.356a10.705 10.705 0 0 0 .2-2.6Zm1.433 1.85a12.652 12.652 0 0 0 .018-2.609c.405-.276.78-.594 1.117-.947a5.48 5.48 0 0 1 .44 2.262 7.536 7.536 0 0 1-1.575 1.293Zm-2.172 2.435a9.046 9.046 0 0 1-3.504 0c.039.084.078.166.12.244C6.907 13.114 7.523 13.5 8 13.5s1.091-.386 1.633-1.427c.04-.078.08-.16.12-.244Zm1.31.74a8.5 8.5 0 0 0 .492-1.298c.457-.197.893-.43 1.307-.696a5.526 5.526 0 0 1-1.8 1.995Zm-6.123 0a8.507 8.507 0 0 1-.493-1.298 8.985 8.985 0 0 1-1.307-.696 5.526 5.526 0 0 0 1.8 1.995ZM2.5 8.1c.463.5.993.935 1.575 1.293a12.652 12.652 0 0 1-.018-2.608 7.037 7.037 0 0 1-1.117-.947 5.48 5.48 0 0 0-.44 2.262Z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                                <template v-else>
                                    <svg :style="{width: '16px', height: '16px'}" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="w-4 h-4">
                                        <path fill-rule="evenodd" d="M15 11a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v6ZM7.25 7.5a.5.5 0 0 0-.5-.5H3a.5.5 0 0 0-.5.5V8a.5.5 0 0 0 .5.5h3.75a.5.5 0 0 0 .5-.5v-.5Zm1.5 3a.5.5 0 0 1 .5-.5H13a.5.5 0 0 1 .5.5v.5a.5.5 0 0 1-.5.5H9.25a.5.5 0 0 1-.5-.5v-.5ZM13.5 8v-.5A.5.5 0 0 0 13 7H9.25a.5.5 0 0 0-.5.5V8a.5.5 0 0 0 .5.5H13a.5.5 0 0 0 .5-.5Zm-6.75 3.5a.5.5 0 0 0 .5-.5v-.5a.5.5 0 0 0-.5-.5H3a.5.5 0 0 0-.5.5v.5a.5.5 0 0 0 .5.5h3.75Z" clip-rule="evenodd" />
                                    </svg>
                                </template>
                            </a-tooltip>
                        </div>
                    </template>
                </template>
            </a-table>
        </a-tab-pane>
        <a-tab-pane key="accounts" tab="Банковские реквизиты">
            <a-table
                size="small"
                :columns="columnsBankAccounts"
                :custom-row="accountsTableRowFn"
                :data-source="model.bank_accounts"
                :pagination="false"
                :scroll="{ y: clientHeight - 686 }"
                :row-class-name="() => 'cursor-pointer'"
            >

            </a-table>
        </a-tab-pane>
        <template v-if="model.id !== null" #rightExtra>
            <a-button v-if="currentTab === 'contacts'" @click="() => openContactDrawer()">Новый контакт</a-button>
            <a-button v-if="currentTab === 'accounts'" @click="() => openAccountDrawer()">Новый счет</a-button>
        </template>
    </a-tabs>
    <a-alert v-if="model.id === null && !loading"
             description="Создание контактов и банковских реквизитов заказчика доступно после сохранения нового заказчика."
             type="info"
    />
</template>

<style scoped>

</style>
