<script setup>
import { computed, inject } from "vue";

const props = defineProps({
    node: { type: Object, required: true },
    canMoveUp: { type: Boolean, default: false },
    canMoveDown: { type: Boolean, default: false },
    canDelete: { type: Boolean, default: false },
});

const emit = defineEmits(["move-up", "move-down", "delete"]);

const unitLabels = inject("unitLabels", {});
// Les clés sont l'enum Unit du domaine, sérialisé par le champ EasyAdmin.
const units = Object.keys(unitLabels);

function toNullableNumber(value) {
    if (typeof value === "number") {
        return Number.isFinite(value) ? value : null;
    }

    if (typeof value !== "string") {
        return null;
    }

    const trimmed = value.trim();
    if (trimmed === "") {
        return null;
    }

    const parsed = Number(trimmed);
    return Number.isFinite(parsed) ? parsed : null;
}

const min = computed({
    get: () => props.node.min,
    set: (value) => {
        props.node.min = toNullableNumber(value);
    },
});

const max = computed({
    get: () => props.node.max,
    set: (value) => {
        props.node.max = toNullableNumber(value);
    },
});

const moveUpTitle = computed(() =>
    props.canMoveUp ? "Monter" : "Impossible : cela changerait le contenu d'une étape",
);
const moveDownTitle = computed(() =>
    props.canMoveDown ? "Descendre" : "Impossible : cela changerait le contenu d'une étape",
);
const deleteTitle = computed(() =>
    props.canDelete
        ? "Supprimer cet ingrédient"
        : "Impossible : ce serait le seul ingrédient d'une étape",
);
</script>

<template>
    <div class="grid-ingredient-cell d-flex align-items-center flex-wrap gap-2 py-1">
        <div class="btn-group btn-group-sm" role="group" aria-label="Réordonner">
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
        <input
            v-model.lazy="min"
            type="number"
            step="any"
            min="0"
            placeholder="min"
            class="form-control form-control-sm grid-ingredient-cell__qty"
        />
        <input
            v-model.lazy="max"
            type="number"
            step="any"
            min="0"
            placeholder="max"
            class="form-control form-control-sm grid-ingredient-cell__qty"
        />
        <select v-model="node.unit" class="form-select form-select-sm grid-ingredient-cell__unit">
            <option :value="null">—</option>
            <option v-for="unit in units" :key="unit" :value="unit">
                {{ unitLabels[unit] ?? unit }}
            </option>
        </select>
        <textarea
            v-model="node.label"
            rows="2"
            placeholder="de farine de riz gluant"
            class="form-control form-control-sm grid-ingredient-cell__label"
        ></textarea>
        <button
            type="button"
            class="btn btn-sm btn-outline-danger"
            :disabled="!canDelete"
            :title="deleteTitle"
            @click="emit('delete')"
        >
            ✕
        </button>
    </div>
</template>

<style scoped>
.grid-ingredient-cell__qty {
    width: 3.5rem;
    appearance: textfield;
}

/* Les flèches d'incrément mangent une quinzaine de pixels sur un champ
   déjà étroit, et n'ont pas d'usage pour une quantité de recette. */
.grid-ingredient-cell__qty::-webkit-outer-spin-button,
.grid-ingredient-cell__qty::-webkit-inner-spin-button {
    margin: 0;
    appearance: none;
}

/* Les libellés d'unité sont longs ("cuillère à soupe"): on les borne
   et on laisse le menu déroulant montrer le texte complet. */
.grid-ingredient-cell__unit {
    flex: 1 1 6rem;
    min-width: 0;
}

.grid-ingredient-cell__label {
    flex: 1 1 8rem;
    resize: vertical;
}
</style>
