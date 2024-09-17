<script setup>
import {ref} from "vue";
import {useSuggests} from "../../stores/models/suggests.js";
import {debounce, isArray} from "radash";
import axios from "axios";

const prop = defineProps({
    modelValue: {type: Object, default: {}},
    clientId: {type: Number},
})
const emit = defineEmits(['update:modelValue'])
const addressOptions = ref([])
const suggests = useSuggests()

const onAddressSelect = (val) => {
    emit('update:modelValue', {
        ...prop.modelValue, address: val
    })
}
const onAddressSearch = debounce({delay: 500}, async (q = '') => {
    let suggest = []
    if (q !== '') {
        suggest = await suggests.addressSuggest(q)
    }
    let clientAddresses = await getClientSuggest("ADDRESS", q)
    if (!isArray(clientAddresses)) {
        clientAddresses = []
    }
    addressOptions.value = [...clientAddresses, ...suggest.map(el => ({value: el, note: 'Из адресного классификатора'}))].map((el, idx) => ({key: idx, ...el}))
})

const contactOptions = ref([])
const phoneOptions = ref([])

const searchContact = debounce({delay: 500}, async q => {
    contactOptions.value = await getClientSuggest("PERSON", q)
})

const searchPhone = debounce({delay: 500}, async q => {
    phoneOptions.value = await getClientSuggest("PHONE", q)
})


const getClientSuggest = async (type, q) => {
    if (!!prop.clientId) {
        const {data} = await axios.get('api/suggest/client-contact', {
            params: {
                client_id: prop.clientId,
                q, type
            }
        })
        return data.map(el => ({value: el.value, text: el.value, note: el.note}))
    }
    return null
}

</script>

<template>
<a-form layout="vertical">
    <a-form-item label="Адрес">
        <a-space-compact size="large" block>
            <a-auto-complete
                :value="modelValue.address"
                @change="(e) => emit('update:modelValue', {
                    ...modelValue, address: e
                })"
                :options="addressOptions"
                placeholder="Введите адрес"
                @select="onAddressSelect"
                @search="onAddressSearch"
                @focus="() => onAddressSearch(modelValue.address ?? '')"
            >
                <template #option="opt">
                    <div>
                        <div style="font-weight: 500">{{ opt.value }}</div>
                        <div style="font-size: 12px">{{ opt.note }}</div>
                    </div>
                </template>
            </a-auto-complete>
            <a-button type="primary">
                Карта
            </a-button>
        </a-space-compact>
    </a-form-item>
    <a-row :gutter="10">
        <a-col style="width: 50%">
            <a-form-item label="Контактное лицо">
                <a-auto-complete
                    :value="modelValue.contact_person"
                    @change="(e) => emit('update:modelValue', {
                        ...modelValue, contact_person: e
                    })"
                    :options="contactOptions"
                    @search="searchContact"
                    @focus="() => searchContact(modelValue.contact_person ?? '')"
                >
                    <template #option="opt">
                        <div>
                            <div style="font-weight: 500">{{ opt.value }}</div>
                            <div style="font-size: 12px">{{ opt.note }}</div>
                        </div>
                    </template>
                </a-auto-complete>
<!--                <a-input-->
<!--                    :value="modelValue.contact_person"-->
<!--                    @change="(e) => emit('update:modelValue', {-->
<!--                        ...modelValue, contact_person: e.target.value-->
<!--                    })"-->
<!--                />-->
            </a-form-item>
        </a-col>
        <a-col style="width: 50%">
            <a-form-item label="Номер телефона">
                <a-auto-complete
                    :value="modelValue.contact_phone"
                    @change="(e) => emit('update:modelValue', {
                        ...modelValue, contact_phone: e
                    })"
                    :options="phoneOptions"
                    @search="searchPhone"
                    @focus="() => searchPhone(modelValue.contact_phone ?? '')"
                >
                    <template #option="opt">
                        <div>
                            <div style="font-weight: 500">{{ opt.value }}</div>
                            <div style="font-size: 12px">{{ opt.note }}</div>
                        </div>
                    </template>
                </a-auto-complete>
<!--                <a-input-->
<!--                    :value="modelValue.contact_phone"-->
<!--                    @change="(e) => emit('update:modelValue', {-->
<!--                        ...modelValue, contact_phone: e.target.value-->
<!--                    })"-->
<!--                />-->
            </a-form-item>
        </a-col>
    </a-row>
    <a-form-item label="Дата прибытия">
        <a-date-picker
            :value="modelValue.arrive_date"
            style="width: 250px"
            placeholder="Дата прибытия"
            format="DD.MM.YYYY"
            @change="(e) => emit('update:modelValue', {
                ...modelValue, arrive_date: e
            })"
        />
    </a-form-item>
    <a-form-item label="Время прибытия">
        <a-space direction="vertical">
            <div>
                <a-space>
                    <a-switch
                        :checked="!!modelValue.is_time_interval"
                        @change="(e) => emit('update:modelValue', {
                        ...modelValue, is_time_interval: e
                    })"
                        size="small"
                    /> интервал
                </a-space>
            </div>
            <div>
                <a-space>
                    <template v-if="!modelValue.is_time_interval">
                        <a-time-picker
                            :value="!!modelValue.arrive_time ? modelValue.arrive_time[0] : undefined"
                            style="width: 170px"
                            :show-second="false"
                            @change="(e) => emit('update:modelValue', {
                                ...modelValue, arrive_time: [e]
                            })"
                        />
                    </template>
                    <template v-if="modelValue.is_time_interval">
                        <a-range-picker
                            :value="modelValue.arrive_time"
                            picker="time"
                            :show-second="false"
                            @change="(e) => emit('update:modelValue', {
                                ...modelValue, arrive_time: e
                            })"
                        />
                    </template>
                </a-space>
            </div>
        </a-space>
    </a-form-item>
    <a-form-item label="Комментарий">
        <a-textarea
            :value="modelValue.comment"
            :auto-size="{ minRows: 3 }"
            placeholder="Комментарий"
            @change="(e) => emit('update:modelValue', {
                ...modelValue, comment: e.target.value
            })"
        />
    </a-form-item>
</a-form>
</template>

<style scoped>

</style>
