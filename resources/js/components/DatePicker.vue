<script setup>
import dayjs from 'dayjs';

const dateFormat = 'DD.MM.YYYY'
const model = defineModel()

const handleDateChange = val => {
    model.value = val ? val.format('YYYY-MM-DD') : null
}
</script>

<template>
    <a-date-picker
        :format="dateFormat"
        :value="model === null ? undefined : dayjs(model)"
        @change="handleDateChange"
    />
</template>

<style scoped>

</style>
