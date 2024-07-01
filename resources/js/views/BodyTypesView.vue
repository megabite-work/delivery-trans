<script setup>
import Layout from "../layouts/AppLayout.vue";
import DirectoryViewer from "../components/DirectoryViewer.vue";

import { ref } from 'vue';
const directoryRef = ref(null);

const columns = [
    {
        key: 'type',
        type: 'string',
        label: 'Тип кузова'
    }
]

</script>

<template>
    <Layout title="Типы кузовов">
        <template #headerExtra>
            <a-button type="primary" @click="() => directoryRef.createNewElement()">Новый тип кузова</a-button>
        </template>
        <DirectoryViewer
            ref="directoryRef"
            dir-name="car-body-types"
            dir-key="type"
            :dir-columns="columns"
            :can-edit="false"
        />
    </Layout>
</template>

<style scoped>

</style>
