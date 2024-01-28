<script setup>
import {reactive, onMounted, ref} from 'vue';
import { useRoute, useRouter } from "vue-router";
import axios from 'axios';
import {useAuthStore} from "../../stores/auth.js";
import {message} from "ant-design-vue";
import Logo from "../../components/icons/Logo.vue";

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

const loginForm = reactive({
    email: '',
    password: '',
    remember: false
})

const isLoading = ref(false)
const isShaking = ref(false)
const hasError = ref(false)
const errorText = ref('')

const onFinish = (cred) => {
    isLoading.value = true
    axios.post('login', cred)
        .then(async (res) => {
            await authStore.refreshState()
            hasError.value = false
            router.replace({path: route.query.redirect || '/'})
        })
        .catch((err) => {
            errorText.value = err.response.data.message
            hasError.value = true
            onFinishFailed()
        })
        .finally(() => {
            isLoading.value = false
        })
}

const onFinishFailed = () => {
    isShaking.value = true
    setTimeout(() => {
        isShaking.value = false
    }, 1000)
}

onMounted(async () => {
    await axios.get('sanctum/csrf-cookie').catch(() => {
        message.error("Ошибка получения CSRF-токена")
    })
})
</script>

<template>
    <div class="login_layout">
        <div class="login_form" :class="{ shake: isShaking }">
            <div class="login_header">
                Delivery Trans
            </div>
            <a-form
                :model="loginForm"
                name="login"
                :label-col="{ span: 4 }"
                :wrapper-col="{ span: 20 }"
                autocomplete="off"
                @finish="onFinish"
                @finishFailed="onFinishFailed"
                :hideRequiredMark="true"
            >
                <a-form-item
                    label="Email"
                    name="email"
                    :rules="[{ required: true, message: 'Пожалуйста, введите почту' }]"
                    :style="{ width: '400px' }"
                >
                    <a-input v-model:value="loginForm.email" />
                </a-form-item>

                <a-form-item
                    label="Пароль"
                    name="password"
                    :rules="[{ required: true, message: 'Пожалуйста, введите ваш пароль' }]"
                >
                    <a-input-password v-model:value="loginForm.password" :visibility-toggle="false" />
                </a-form-item>

                <a-form-item v-if="hasError" :wrapper-col="{ offset: 4, span: 20 }">
                    <a-alert :message="errorText" type="error" />
                </a-form-item>

                <a-form-item name="remember" :wrapper-col="{ offset: 4, span: 20 }">
                    <a-checkbox v-model:checked="loginForm.remember">Запомнить меня</a-checkbox>
                </a-form-item>

                <div :style="{ display: 'flex', justifyContent: 'right' }">
                    <a-button type="primary" html-type="submit" :loading="isLoading">Войти</a-button>
                </div>
            </a-form>
        </div>
    </div>
</template>

<style>
.login_layout {
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    background: #f9fbff;
}

.login_form {
    padding: 40px 80px 20px;
    background: #ffffff;
    border: 1px solid #edf1f6;
    border-radius: 8px;
    box-shadow: 5px 5px 11px rgba(0, 0, 0, 0.05);
}

.login_header {
    font-family: 'Ubuntu', sans-serif;
    color: rgba(0, 0, 0, 0.88);
    font-size: 25px;
    font-weight: 600;
    padding-bottom: 25px;
    text-align: center;
}

.shake {
    animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
    transform: translate3d(0, 0, 0);
}
@keyframes shake {
    10%, 90% {
        transform: translate3d(-1px, 0, 0);
    }
    20%, 80% {
        transform: translate3d(2px, 0, 0);
    }
    30%, 50%, 70% {
        transform: translate3d(-4px, 0, 0);
    }
    40%, 60% {
        transform: translate3d(4px, 0, 0);
    }
}
</style>
