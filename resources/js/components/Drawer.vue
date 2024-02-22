<script setup>
import {h} from "vue";
import {DeleteFilled} from "@ant-design/icons-vue";

defineProps({
    title: { type: String, default: '' },
    width: { type: Number, default: 500 },
    loading: { type: Boolean, default: false },
    saving: { type: Boolean, default: false },
    okText: { type: String, default: 'Ok' },
    needOk: { type: Boolean, default: true },
    okLoading: {type: Boolean, default: false },
    cancelText: { type: String, default: 'Отмена' },
    needCancel: { type: Boolean, default: true },
    deleteText: { type: String, default: 'Удалить' },
    needDelete: { type: Boolean,  default: true },
    needDeletionConfirm: { type: Boolean, default: true },
    needDeletionConfirmText: { type: String, default: 'Вы уверены?' },
    needDeletionConfirmOkText: { type: String, default: 'Удалить' },
    needDeletionConfirmCancelText: { type: String, default: 'Отмена' }
})
defineEmits(['close', 'delete', 'save'])

const open = defineModel('open')

</script>

<template>
    <a-drawer
        v-model:open="open"
        :title="loading ? 'Загрузка...' : title"
        :width="width"
        @close="$emit('close')"
    >
        <a-spin :spinning="loading" tip="Загрузка...">
            <slot />
        </a-spin>

        <template #footer>
            <div :style="{ display: 'flex', justifyContent: 'space-between' }">
                <div>
                    <a-popconfirm v-if="needDelete && needDeletionConfirm"
                        :title="needDeletionConfirmText"
                        :ok-text="needDeletionConfirmOkText"
                        :cancel-text="needDeletionConfirmCancelText"
                        @confirm="$emit('delete')"
                    >
                        <a-button danger
                                  :icon="deleteText !== '' ? null : h(DeleteFilled)"
                                  :disabled="loading"
                        >
                            {{ deleteText }}
                        </a-button>
                    </a-popconfirm>
                    <a-button v-if="needDelete && !needDeletionConfirm" danger
                              :icon="deleteText !== '' ? null : h(DeleteFilled)"
                              :disabled="loading"
                    >
                        {{ deleteText }}
                    </a-button>
                </div>
                <div>
                    <a-button v-if="needCancel"
                              style="margin-right: 8px"
                              @click="$emit('close')"
                              :disabled="loading"
                    >
                        {{ cancelText }}
                    </a-button>
                    <a-button v-if="needOk"
                              type="primary"
                              :loading="saving"
                              @click="$emit('save')"
                              :disabled="loading">
                        {{ okText }}
                    </a-button>
                </div>
            </div>
        </template>
    </a-drawer>
</template>

<style scoped>

</style>
