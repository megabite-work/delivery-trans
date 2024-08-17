<script setup>
import Layout from "../layouts/AppLayout.vue";
import router from  "../router";
</script>

<template>
    <Layout>
        <a-result status="404" title="404" sub-title="Извините, у нас нет такой страницы">
            <template #extra>
                <a-button type="primary" @click="() => router.push({ path: '/' })">Перейти на главную</a-button>
            </template>
        </a-result>
    </Layout>
</template>

<style scoped>

</style>
