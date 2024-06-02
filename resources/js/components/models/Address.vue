<script setup>
import {ref} from "vue";
import {useSuggests} from "../../stores/models/suggests.js";
import {debounce} from "radash";

const prop = defineProps({
    modelValue: {type: Object, default: {}}
})
const emit = defineEmits(['update:modelValue'])
const addressOptions = ref([])
const suggests = useSuggests()

const onAddressSelect = (val) => {
    emit('update:modelValue', {
        ...prop.modelValue, address: val
    })
}
const onAddressSearch = debounce({delay: 500}, async (q) => {
    const suggest = await suggests.addressSuggest(q)
    addressOptions.value = suggest.map(el => ({value: el}))
})

</script>

<template>
<a-form layout="vertical">
    <a-form-item label="Адрес">
        <a-space-compact size="large" block>
<!--                <a-input-->
<!--                    :value="modelValue.address"-->
<!--                    @change="(e) => emit('update:modelValue', {-->
<!--                        ...modelValue, address: e.target.value-->
<!--                    })"-->
<!--                />-->
            <a-auto-complete
                :value="modelValue.address"
                @change="(e) => emit('update:modelValue', {
                    ...modelValue, address: e
                })"
                :options="addressOptions"
                placeholder="Введите адрес"
                @select="onAddressSelect"
                @search="onAddressSearch"
            />
            <a-button type="primary">
                Карта
            </a-button>
        </a-space-compact>
    </a-form-item>
    <a-row :gutter="10">
        <a-col style="width: 50%">
            <a-form-item label="Контактное лицо">
                <a-input
                    :value="modelValue.contact_person"
                    @change="(e) => emit('update:modelValue', {
                        ...modelValue, contact_person: e.target.value
                    })"
                />
            </a-form-item>
        </a-col>
        <a-col style="width: 50%">
            <a-form-item label="Номер телефона">
                <a-input
                    :value="modelValue.contact_phone"
                    @change="(e) => emit('update:modelValue', {
                        ...modelValue, contact_phone: e.target.value
                    })"
                />
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
