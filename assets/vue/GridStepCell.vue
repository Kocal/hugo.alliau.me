<script setup>
import { computed } from "vue";

const props = defineProps({
    node: { type: Object, required: true },
    canMoveUp: { type: Boolean, default: false },
    canMoveDown: { type: Boolean, default: false },
});

const emit = defineEmits(["ungroup", "move-up", "move-down"]);

const moveUpTitle = computed(() =>
    props.canMoveUp
        ? "Monter cette étape et tout ce qu'elle contient"
        : "Impossible : pas de voisin à ce niveau",
);
const moveDownTitle = computed(() =>
    props.canMoveDown
        ? "Descendre cette étape et tout ce qu'elle contient"
        : "Impossible : pas de voisin à ce niveau",
);
</script>

<template>
    <div class="grid-step-cell d-flex align-items-center flex-wrap gap-1 p-1 h-100">
        <span class="badge text-bg-primary">Étape</span>
        <div class="btn-group btn-group-sm" role="group" aria-label="Réordonner l'étape">
            <button
                type="button"
                class="btn btn-outline-secondary"
                :disabled="!canMoveUp"
                :title="moveUpTitle"
                @click="emit('move-up')"
            >
                ▲
            </button>
            <button
                type="button"
                class="btn btn-outline-secondary"
                :disabled="!canMoveDown"
                :title="moveDownTitle"
                @click="emit('move-down')"
            >
                ▼
            </button>
        </div>
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
