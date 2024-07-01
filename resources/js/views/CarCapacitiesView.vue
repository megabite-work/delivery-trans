<script setup>
import Layout from "../layouts/AppLayout.vue";
import DirectoryViewer from "../components/DirectoryViewer.vue";

import { ref } from 'vue';
const directoryRef = ref(null);

const columns = [
    {
        key: 'tonnage',
        type: 'number',
        label: 'Тоннаж'
    },
    {
        key: 'volume',
        type: 'number',
        label: 'Объем'
    },
    {
        key: 'pallets_count',
        type: 'number',
        label: 'Количество палет'
    },
]

</script>

<template>
    <Layout title="Вместительность авто">
        <template #headerExtra>
            <a-button type="primary" @click="() => directoryRef.createNewElement()">Новый тип</a-button>
        </template>
        <DirectoryViewer
            ref="directoryRef"
            dir-name="car_capacities"
            dir-key="id"
            :dir-columns="columns"
        />
    </Layout>
</template>

<style scoped>

</style>
