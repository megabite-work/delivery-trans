<script setup>
import {reactive, ref} from "vue";

const priceTypeOptions = reactive([
    { label: "Для заказчика", value: "CLIENT" },
    { label: "Для перевозчика", value: "CARRIER" },
])

const priceType = ref(priceTypeOptions[0].value)
</script>

<template>
 <a-segmented v-model:value="priceType" :options="priceTypeOptions"/>
</template>

<style scoped>

</style>
