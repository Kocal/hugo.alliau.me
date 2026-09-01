<script setup>
import { computed, onMounted, onUnmounted, provide, reactive, ref, watch } from "vue";
import GridIngredientCell from "./GridIngredientCell.vue";
import GridStepCell from "./GridStepCell.vue";
import {
    addColumn,
    addIngredientRow,
    analyzeGrid,
    buildRenderTable,
    canDeleteIngredientRow,
    canMoveIngredientRow,
    canRemoveColumn,
    computeDragPreview,
    createStepFromDrag,
    deleteIngredientRow,
    gridToTree,
    maximalBlocks,
    moveIngredientRow,
    removeColumn,
    treeToGrid,
    ungroupStep,
} from "./grid.js";

const props = defineProps({
    initial: { type: Object, required: true },
    unitLabels: { type: Object, default: () => ({}) },
});
const emit = defineEmits(["change"]);

provide("unitLabels", props.unitLabels);

const grid = reactive(treeToGrid(structuredClone(props.initial.roots ?? [])));

// Une seule analyse de la grille alimente le tableau, l'arbre et les blocs: pendant un
// glissé la grille ne bouge pas, donc Vue la garde en cache et seul l'aperçu se recalcule.
const analysis = computed(() => analyzeGrid(grid));
const blocks = computed(() => maximalBlocks(grid, analysis.value));
const table = computed(() => buildRenderTable(grid, analysis.value));

const tree = computed(() => gridToTree(grid, analysis.value));

const canRemoveColumnComputed = computed(() => canRemoveColumn(grid));
const removeColumnTitle = computed(() =>
    canRemoveColumnComputed.value
        ? "Retirer la dernière colonne d'étapes"
        : "Impossible : la dernière colonne contient une étape",
);

function addColumnHandler() {
    addColumn(grid);
}

function removeColumnHandler() {
    removeColumn(grid);
}

// { col, anchorRow, hoverRow } pendant un glissé, sinon null.
const drag = ref(null);

const dragPreview = computed(() =>
    drag.value
        ? computeDragPreview(
              blocks.value,
              drag.value.col,
              drag.value.anchorRow,
              drag.value.hoverRow,
          )
        : null,
);

function isInPreview(cell) {
    const preview = dragPreview.value;
    return (
        preview !== null &&
        cell.col === preview.col &&
        cell.rowStart >= preview.rowStart &&
        cell.rowStart < preview.rowStart + preview.rowSpan
    );
}

function isPreviewTopCell(cell) {
    return (
        dragPreview.value !== null &&
        cell.col === dragPreview.value.col &&
        cell.rowStart === dragPreview.value.rowStart
    );
}

function startDrag(event, cell) {
    if (!cell.draggable) {
        return;
    }
    event.preventDefault();
    drag.value = { col: cell.col, anchorRow: cell.rowStart, hoverRow: cell.rowStart };
}

function hoverDrag(cell) {
    if (drag.value) {
        drag.value = { ...drag.value, hoverRow: cell.rowStart };
    }
}

function endDrag() {
    if (dragPreview.value) {
        createStepFromDrag(grid, dragPreview.value);
    }
    drag.value = null;
}

onMounted(() => {
    window.addEventListener("pointerup", endDrag);
    window.addEventListener("pointercancel", endDrag);
});

onUnmounted(() => {
    window.removeEventListener("pointerup", endDrag);
    window.removeEventListener("pointercancel", endDrag);
});

function ungroup(stepId) {
    ungroupStep(grid, stepId);
}

function addRow() {
    addIngredientRow(grid);
}

function deleteRow(index) {
    deleteIngredientRow(grid, index);
}

function moveRow(index, direction) {
    moveIngredientRow(grid, index, direction);
}

function rowKey(rowIndex) {
    return grid.ingredients[rowIndex]?.id ?? rowIndex;
}

function cellKey(cell, rowIndex, cellIndex) {
    return cell.node?.id ?? `gap${rowIndex}-${cellIndex}`;
}

const serialized = computed(() => JSON.stringify({ roots: tree.value }));

watch(serialized, (value) => emit("change", value), { immediate: true });
</script>

<template>
    <div class="recipe-editor">
        <p v-if="grid.ingredients.length === 0" class="text-body-secondary small">
            Aucun ingrédient.
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="addRow">
                + ingrédient
            </button>
        </p>

        <div v-else class="table-responsive">
            <table
                class="table table-bordered table-sm align-middle mb-0 recipe-editor__table"
                :class="{ 'recipe-editor__table--dragging': drag !== null }"
            >
                <colgroup>
                    <col class="recipe-editor__col--ingredient" />
                    <col v-for="n in table.columnCount - 1" :key="n" />
                </colgroup>
                <thead>
                    <tr>
                        <th scope="col">
                            <div class="recipe-editor__header">
                                <span>Ingrédient</span>
                                <button
                                    type="button"
                                    class="btn btn-sm btn-outline-secondary recipe-editor__header-action"
                                    title="Ajouter un ingrédient"
                                    @click="addRow"
                                >
                                    + ingrédient
                                </button>
                            </div>
                        </th>
                        <th
                            v-if="table.columnCount > 1"
                            scope="col"
                            :colspan="table.columnCount - 1"
                        >
                            <div class="recipe-editor__header">
                                <span>Étapes</span>
                                <span
                                    class="btn-group btn-group-sm recipe-editor__header-action"
                                    role="group"
                                    aria-label="Colonnes d'étapes"
                                >
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        title="Ajouter une colonne d'étapes"
                                        @click="addColumnHandler"
                                    >
                                        + colonne
                                    </button>
                                    <button
                                        type="button"
                                        class="btn btn-outline-secondary"
                                        :disabled="!canRemoveColumnComputed"
                                        :title="removeColumnTitle"
                                        @click="removeColumnHandler"
                                    >
                                        − colonne
                                    </button>
                                </span>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(row, rowIndex) in table.rows" :key="rowKey(rowIndex)">
                        <template
                            v-for="(cell, cellIndex) in row"
                            :key="cellKey(cell, rowIndex, cellIndex)"
                        >
                            <td v-if="cell.type === 'ingredient'" class="p-1">
                                <GridIngredientCell
                                    :node="cell.node"
                                    :can-move-up="canMoveIngredientRow(grid, rowIndex, -1)"
                                    :can-move-down="canMoveIngredientRow(grid, rowIndex, 1)"
                                    :can-delete="canDeleteIngredientRow(grid, rowIndex)"
                                    @move-up="moveRow(rowIndex, -1)"
                                    @move-down="moveRow(rowIndex, 1)"
                                    @delete="deleteRow(rowIndex)"
                                />
                            </td>
                            <td
                                v-else-if="cell.type === 'step'"
                                :rowspan="cell.rowSpan"
                                class="p-1 recipe-editor__step"
                                @pointerenter="hoverDrag(cell)"
                            >
                                <GridStepCell :node="cell.node" @ungroup="ungroup(cell.node.id)" />
                            </td>
                            <td
                                v-else
                                :rowspan="cell.rowSpan"
                                class="recipe-editor__dropzone"
                                :class="{
                                    'recipe-editor__dropzone--draggable': cell.draggable,
                                    'recipe-editor__dropzone--preview': isInPreview(cell),
                                }"
                                @pointerdown="startDrag($event, cell)"
                                @pointerenter="hoverDrag(cell)"
                            >
                                <span
                                    v-if="isPreviewTopCell(cell)"
                                    class="recipe-editor__preview-label"
                                >
                                    {{ dragPreview.rowSpan }} ligne(s) → colonne
                                    {{ dragPreview.col }}
                                </span>
                            </td>
                        </template>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.recipe-editor__table {
    table-layout: fixed;
}

/* Les colonnes d'étapes n'ont pas de largeur: en table-layout fixed,
   elles se partagent à parts égales ce que la colonne ingrédient laisse. */
.recipe-editor__col--ingredient {
    width: 22rem;
}

.recipe-editor__table td {
    vertical-align: top;
}

/* `table-warning` de Bootstrap fige un jaune clair et du texte noir, quel que
   soit le thème: en sombre, ça donnait un bloc criard. Une teinte translucide
   se pose sur le fond du moment, donc elle suit le thème par construction. */
.recipe-editor__step {
    background-color: color-mix(in srgb, var(--bs-warning) 10%, transparent);
}

.recipe-editor__header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem;
}

/* Masqués tant qu'on ne survole pas, mais toujours atteignables au clavier. */
.recipe-editor__header-action {
    opacity: 0;
    transition: opacity 0.1s ease-in-out;
}

.recipe-editor__header:hover .recipe-editor__header-action,
.recipe-editor__header:focus-within .recipe-editor__header-action {
    opacity: 1;
}

.recipe-editor__table--dragging {
    user-select: none;
}

.recipe-editor__dropzone {
    height: 100%;
    min-height: 2.5rem;
}

.recipe-editor__dropzone--draggable {
    cursor: grab;
    background: repeating-linear-gradient(
        135deg,
        var(--bs-tertiary-bg),
        var(--bs-tertiary-bg) 6px,
        var(--bs-secondary-bg) 6px,
        var(--bs-secondary-bg) 12px
    );
    transition: background-color 0.1s ease-in-out;
}

.recipe-editor__dropzone--draggable:hover {
    background: var(--bs-primary-bg-subtle);
}

.recipe-editor__dropzone--preview {
    background: var(--bs-primary-bg-subtle) !important;
    outline: 2px solid var(--bs-primary);
    outline-offset: -2px;
}

.recipe-editor__preview-label {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    padding: 0.25rem;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--bs-primary-text-emphasis);
    text-align: center;
}
</style>
