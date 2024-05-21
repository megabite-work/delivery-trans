<script setup>
import {h, reactive, ref} from "vue";
import {
    CommentOutlined,
    DownOutlined,
    EditOutlined,
    PlusCircleOutlined,
    UpOutlined
} from "@ant-design/icons-vue";
import {isArray} from "radash";
import Drawer from "./Drawer.vue";
import Address from "./models/Address.vue";

const model = defineModel("modelValue", {
    type: Array,
    default: []
})

const emit = defineEmits(["change"]);

const props = defineProps({
    title: {type: String},
    addButtonText: {type: String}
})

const addressDrawer = reactive({ isOpen: false, isSaving: false })
const currentAddress = reactive({ data:{}, modified: false, header: 'Новая точка' })
const currentAddressIdx = ref(-1)

const openAddressDrawer = (address = null) => {
    currentAddress.header = address === null ? 'Новая точка' : address.address
    addressDrawer.isOpen = true
    currentAddress.modified = false
    currentAddress.data = address === null ? {} : { ...address }
}

const saveAddress = () => {
    if (currentAddressIdx.value >= 0) {
        model.value[currentAddressIdx.value] = currentAddress.data
    } else {
        model.value = Array(...model.value, currentAddress.data)
    }
    addressDrawer.isOpen = false
    currentAddress.data = {}
    currentAddressIdx.value = -1
    emit('change')
}

const deleteAddress = () => {
    if (currentAddressIdx.value >= 0) {
        model.value.splice(currentAddressIdx.value, 1)
    }
    addressDrawer.isOpen = false
    currentAddress.data = {}
    currentAddressIdx.value = -1
    emit('change')
}
</script>

<template>
    <a-card :title="title" style="width: 100%">
        <template #extra>
            <a-button
                type="link"
                :icon="h(PlusCircleOutlined)"
                @click="() => openAddressDrawer(null)"
            >
                {{addButtonText}}
            </a-button>
        </template>
    </a-card>
    <a-list :data-source="isArray(model) ? model : []">
        <template #renderItem="item">
            <a-list-item>
                <div style="width: 100%">
                    <div style="font-size: 15px; font-weight: 600; color: #334155; display: flex; justify-content: space-between">
                        <div>{{ item.item.arrive_date.format('DD.MM.YYYY') }}</div>
                        <div v-if="isArray(item.item.arrive_time)">
                            <template v-if="item.item.is_time_interval">
                                с {{ item.item.arrive_time[0].format('HH:mm') }} до {{ item.item.arrive_time[1].format('HH:mm') }}
                            </template>
                            <template v-if="!item.item.is_time_interval && item.item.arrive_time[0]">
                                строго к {{ item.item.arrive_time[0].format('HH:mm') }}
                            </template>
                        </div>
                    </div>
                    <div style="padding-top: 5px">
                        {{ item.item.address }}
                    </div>
                    <a-divider style="margin: 5px 0 0 0" dashed />
                    <div style="padding-top: 5px">
                        {{ item.item.contact_person }}
                    </div>
                    <div>
                        {{ item.item.contact_phone }}
                    </div>
                    <div style="padding-top: 5px ;width: 100%; display: flex; justify-content: space-between">
                        <div>
                            <a-tooltip
                                v-if="model[item.index].comment"
                                :title="model[item.index].comment"
                            >
                                <a-button size="small">
                                    <CommentOutlined style="color: #6b7280" />
                                </a-button>
                            </a-tooltip>
                        </div>
                        <a-space-compact size="small" align="end">
                            <a-tooltip v-if="item.index > 0" title="Переместить выше">
                                <a-button @click="() => [model[item.index], model[item.index-1]] = [model[item.index-1], model[item.index]]">
                                    <UpOutlined style="color: #6b7280" />
                                </a-button>
                            </a-tooltip>
                            <a-tooltip v-if="item.index < model.length - 1" title="Переместить ниже">
                                <a-button @click="() => [model[item.index], model[item.index+1]] = [model[item.index+1], model[item.index]]">
                                    <DownOutlined style="color: #6b7280" />
                                </a-button>
                            </a-tooltip>
                            <a-tooltip title="Редактировать">
                                <a-button @click="() => {
                                            currentAddressIdx = item.index
                                            openAddressDrawer(item.item)
                                        }">
                                    <EditOutlined style="color: #6b7280" />
                                </a-button>
                            </a-tooltip>
                        </a-space-compact>
                    </div>
                </div>
            </a-list-item>
        </template>
    </a-list>
    <drawer
        v-model:open="addressDrawer.isOpen"
        @close="addressDrawer.isOpen = false"
        @save="saveAddress"
        @delete="deleteAddress"
        :saving="addressDrawer.isSaving"
        ok-text="Сохранить"
        need-deletion-confirm-text="Адрес будет удален! Вы уверены?"
        :width="700"
        :title="currentAddress.header"
    >
        <Address v-model="currentAddress.data" />
    </drawer>
</template>

<style scoped>

</style>
