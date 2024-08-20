<script setup>
import Layout from "../layouts/AppLayout.vue";
import router from  "../router";
</script>

<template>
    <Layout>
        <a-result status="403" title="403" sub-title="Извините, у вас нет доступа к этой странице">
            <template #extra>
                <a-button type="primary" @click="() => router.push({ path: '/' })">Перейти на главную</a-button>
            </template>
        </a-result>
    </Layout>
</template>

<style scoped>

</style>
