<script setup>
defineProps({
    node: { type: Object, required: true },
});

const emit = defineEmits(["ungroup"]);
</script>

<template>
    <div class="grid-step-cell d-flex align-items-center flex-wrap gap-1 p-1 h-100">
        <span class="badge text-bg-primary">Étape</span>
        <textarea
            v-model="node.text"
            rows="2"
            placeholder="Mélanger farine et eau"
            class="form-control form-control-sm grid-step-cell__text"
        ></textarea>
        <button
            type="button"
            class="btn btn-sm btn-outline-secondary"
            title="Dissoudre cette étape : ses ingrédients et étapes reviennent au niveau précédent"
            @click="emit('ungroup')"
        >
            Dégrouper
        </button>
    </div>
</template>

<style scoped>
.grid-step-cell__text {
    flex: 1 0 100%;
    resize: vertical;
}
</style>
