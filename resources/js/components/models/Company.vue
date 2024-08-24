<script setup>
import {reactive, watch} from "vue";

const model = defineModel()
const prop = defineProps({
    loading: { type: Boolean, default: false },
    errors: { type: Object, default: null },
    readOnly: {type: Boolean, default: false }
})

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
    </a-form>
</template>

<style>
input, select, textarea, .ant-select-selector {
    color: #1a202c !important;
}
</style>
