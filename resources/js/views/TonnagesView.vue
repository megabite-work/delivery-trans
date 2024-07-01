<script setup>
import Layout from "../layouts/AppLayout.vue";
import DirectoryViewer from "../components/DirectoryViewer.vue";

import { ref } from 'vue';
const directoryRef = ref(null);

const columns = [
    {
        key: 'tonnage',
        type: 'number',
        label: 'Тонн'
    }
]

</script>

<template>
    <Layout title="Тоннаж">
        <template #headerExtra>
            <a-button type="primary" @click="() => directoryRef.createNewElement()">Новое значение</a-button>
        </template>
        <DirectoryViewer
            ref="directoryRef"
            dir-name="tonnages"
            dir-key="type"
            :dir-columns="columns"
            :can-edit="false"
        />
    </Layout>
</template>

<style scoped>

</style>
