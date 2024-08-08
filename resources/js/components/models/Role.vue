<script setup>
import {computed, reactive, watch} from "vue";
import {permissionsSections, permissions} from "../../helpers/permissions.js";
import {isArray} from "radash";

const model = defineModel()
const prop = defineProps({ loading: { type: Boolean, default: false }, errors: { type: Object, default: null } })

const err = reactive({
    name: null, permissions: null
})
watch(() => prop.errors, () => {
    Object.keys(err).forEach((key) => {
        if (prop.errors[key]) {
            err[key] = prop.errors[key][0]
            return
        }
        err[key] = null
    })
})

const permissionsModel = computed(() => (pid => model.value.permissions && isArray(model.value.permissions) && model.value.permissions.includes(pid)))
const handlePermissionSet = (e) => {
    if (e.target.checked) {
        if (!isArray(model.value.permissions)) {
            model.value.permissions = []
        }
        model.value.permissions.push(e.target.id)
        return
    }
    const idx = model.value.permissions.indexOf(e.target.id)
    if (idx > -1) {
        model.value.permissions.splice(idx, 1)
    }
}
</script>

<template>
    <a-form layout="vertical" :model="model">
        <a-form-item label="Наименование роли" name="name" :validate-status="err.name ? 'error': undefined" :help="err.name">
            <a-input
                v-model:value="model.name"
                placeholder="Наименование роли"
            />
        </a-form-item>
        <a-divider>Разрешения</a-divider>
        <template v-for="section in permissionsSections">
            <a-divider orientation="left" orientation-margin="0px">{{section.label}}</a-divider>
            <div>
                <div v-for="p in section.permissions" :key="p" style="padding: 3px 0">
                    <a-checkbox :id="p" :checked="permissionsModel(p)" @change="handlePermissionSet">{{permissions[p]}}</a-checkbox>
                </div>
            </div>
        </template>
    </a-form>
</template>

<style scoped>

</style>
