<script setup>
import {reactive, ref, watch} from "vue";
import {message} from "ant-design-vue";

import axios from "axios";

const model = defineModel()
const prop = defineProps({ loading: { type: Boolean, default: false }, errors: { type: Object, default: null } })

const bodyTypesOptionsList = ref([])
const bodyTypesOptionsLoading = ref(false)
const getBodyTypesOptionsList = async () => {
    bodyTypesOptionsLoading.value = true
    try {
        const { data } = await axios.get('api/suggest/car/body-types')
        bodyTypesOptionsList.value = data.map(el => ({value: el.type, label: el.type}))
    } catch (e) {
        message.error('Ошибка загрузки списка')
    } finally {
        bodyTypesOptionsLoading.value = false
    }
}

const err = reactive({
    type: null, name: null, plate_number: null, body_type: null, volume: null, pallets_count: null, tonnage: null,
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
    <a-form-item label="Тип машины" name="type" :validate-status="err.type ? 'error': undefined" :help="err.type">
        <a-select
            placeholder="Тип машины"
            ref="select"
            v-model:value="model.type"
            :style="{ width: '250px' }"
        >
            <a-select-option value="TRUCK">Грузовик</a-select-option>
            <a-select-option value="TRACTOR">Тягач</a-select-option>
            <a-select-option value="TRAILER">Полуприцеп</a-select-option>
        </a-select>
    </a-form-item>
    <template v-if="!!model.type">
        <a-form-item label="Марка/модель" name="name" :validate-status="err.name ? 'error' : undefined" :help="err.name">
            <a-input
                v-model:value="model.name"
                placeholder="Введите марку/модель машины"
            />
        </a-form-item>
        <a-row :gutter="16">
            <a-col :span="model.type === 'TRACTOR' ? 24 : 12">
                <a-form-item label="Госномер" name="plate_number" :validate-status="err.plate_number ? 'error': undefined" :help="err.plate_number">
                    <a-input
                        v-model:value="model.plate_number"
                        placeholder="Госномер машины"
                    />
                </a-form-item>
            </a-col>
            <a-col v-if="model.type !== 'TRACTOR'" :span="12">
                <a-form-item label="Тип кузова" name="body_type" :validate-status="err.body_type ? 'error': undefined" :help="err.body_type">
                    <a-select
                        ref="select"
                        placeholder="Тип кузова"
                        v-model:value="model.body_type"
                        :options="bodyTypesOptionsList"
                        :loading="bodyTypesOptionsLoading"
                        @focus="getBodyTypesOptionsList"
                    />
                </a-form-item>
            </a-col>
        </a-row>
        <template  v-if="model.type !== 'TRACTOR'">
            <a-row :gutter="16">
                <a-col :span="8">
                    <a-form-item label="Объем">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a-input placeholder="Объем" v-model:value="model.volume" /><div>м<sup>3</sup></div>
                        </div>
                    </a-form-item>
                </a-col>
                <a-col :span="8">
                    <a-form-item label="Палет">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a-input placeholder="Палет" v-model:value="model.pallets_count" /><div>шт.</div>
                        </div>
                    </a-form-item>
                </a-col>
                <a-col :span="8">
                    <a-form-item label="Тоннаж">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <a-input v-model:value="model.tonnage" placeholder="Тоннаж" /><div>т.</div>
                        </div>
                    </a-form-item>
                </a-col>
            </a-row>
            <a-form-item label="Загрузка">
                <a-checkbox v-model:checked="model.loading_rear">Задняя</a-checkbox>
                <a-checkbox v-model:checked="model.loading_lateral">Боковая</a-checkbox>
                <a-checkbox v-model:checked="model.loading_upper">Верхняя</a-checkbox>
            </a-form-item>
        </template>
    </template>
</a-form>
</template>

<style scoped>

</style>
