<script setup>
const model = defineModel({type: Object, required: true})
</script>

<template>
    <div style="display: flex; align-items: center; padding-bottom: 20px">
        <a-typography-title :level="4" style="flex-grow: 1; margin: 0">
            {{ model.car_body_type }}
        </a-typography-title>
        <a-typography-title :level="5" style="margin: 0">
            {{ model.car_capacity.tonnage }}т. – {{ model.car_capacity.volume }}м³ – {{ model.car_capacity.pallets_count }}п.
        </a-typography-title>
    </div>
    <a-form layout="vertical" :model="model">
        <a-form-item label="Ставка" name="hourly">
            <a-input-number
                v-model:value="model.hourly"
                :min="0"
                placeholder="Ставка"
                style="width: 100%"
            >
                <template #addonAfter>
                    ₽ / час
                </template>
            </a-input-number>
        </a-form-item>
        <a-form-item label="Минимум" name="min_hours">
            <a-input-number
                v-model:value="model.min_hours"
                :min="0"
                placeholder="Минимум часов"
                style="width: 100%"
            >
                <template #addonAfter>
                    час.
                </template>
            </a-input-number>
        </a-form-item>
        <a-form-item label="Часов на подачу" name="hours_for_coming">
            <a-input-number
                v-model:value="model.hours_for_coming"
                :min="0"
                placeholder="Часов на подачу"
                style="width: 100%"
            >
                <template #addonAfter>
                    час.
                </template>
            </a-input-number>
        </a-form-item>
        <a-form-item label="Тариф за МКАД" name="mkad_price">
            <a-input-number
                v-model:value="model.mkad_price"
                :min="0"
                placeholder="Тариф поездки за МКАД"
                style="width: 100%"
            >
                <template #addonAfter>
                    ₽ / км.
                </template>
            </a-input-number>
        </a-form-item>
    </a-form>
</template>

<style scoped>

</style>
