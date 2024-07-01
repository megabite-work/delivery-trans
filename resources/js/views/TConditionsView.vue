<script setup>
import Layout from "../layouts/AppLayout.vue";
import DirectoryViewer from "../components/DirectoryViewer.vue";

import { ref } from 'vue';
const directoryRef = ref(null);

const columns = [
    {
        key: 'min',
        type: 'number',
        label: 'Минимум'
    },
    {
        key: 'max',
        type: 'number',
        label: 'Максимум'
    }
]

</script>

<template>
    <Layout title="Температурные условия">
        <template #headerExtra>
            <a-button type="primary" @click="() => directoryRef.createNewElement()">Новый диапазон температур</a-button>
        </template>
        <DirectoryViewer
            ref="directoryRef"
            dir-name="tconditions"
            dir-key="type"
            :dir-columns="columns"
            :can-edit="false"
        />
    </Layout>
</template>

<style scoped>

</style>
