import { describe, expect, it } from "vitest";
import {
    addColumn,
    addIngredientRow,
    analyzeGrid,
    buildRenderTable,
    canDeleteIngredientRow,
    canMoveCell,
    canRemoveColumn,
    computeDragPreview,
    createStepFromDrag,
    deleteIngredientRow,
    gridToTree,
    isValidStepPlacement,
    maximalBlocks,
    moveCell,
    removeColumn,
    treeToGrid,
    ungroupStep,
} from "./grid.js";

function ingredient(id) {
    return { type: "ingredient", id, label: id, min: null, max: null, unit: null };
}

function step(id, children) {
    return { type: "step", id, text: id, children };
}

/**
 *        col 0   col 1   col 2
 * row 0  i1      s1      s2
 * row 1  i2      s1      s2
 * row 2  i3              s2
 * row 3  i4
 */
function nestedTree() {
    return [
        step("s2", [step("s1", [ingredient("i1"), ingredient("i2")]), ingredient("i3")]),
        ingredient("i4"),
    ];
}

function nestedGrid() {
    return treeToGrid(nestedTree());
}

function ids(nodes) {
    return nodes.map((node) => node.id);
}

function spans(grid) {
    return Object.fromEntries(
        grid.steps.map(({ id, col, rowStart, rowSpan }) => [id, { col, rowStart, rowSpan }]),
    );
}

describe("treeToGrid", () => {
    it("lists the ingredients in depth-first order", () => {
        const tree = nestedTree();

        const grid = treeToGrid(tree);

        expect(ids(grid.ingredients)).toEqual(["i1", "i2", "i3", "i4"]);
    });

    it("places each step one column right of its deepest child", () => {
        const tree = nestedTree();

        const grid = treeToGrid(tree);

        expect(spans(grid)).toEqual({
            s1: { col: 1, rowStart: 0, rowSpan: 2 },
            s2: { col: 2, rowStart: 0, rowSpan: 3 },
        });
    });

    it("counts as many step columns as the deepest step", () => {
        const tree = nestedTree();

        const grid = treeToGrid(tree);

        expect(grid.columnCount).toBe(2);
    });

    it("keeps one step column for a tree without step", () => {
        const tree = [ingredient("i1")];

        const grid = treeToGrid(tree);

        expect(grid.columnCount).toBe(1);
    });
});

describe("analyzeGrid", () => {
    it("gives each cell the narrowest step containing it as parent", () => {
        const grid = nestedGrid();

        const { parentOf } = analyzeGrid(grid);

        const parents = Object.fromEntries(
            [...parentOf].map(([id, parent]) => [id, parent?.id ?? null]),
        );
        expect(parents).toEqual({ i1: "s1", i2: "s1", i3: "s2", i4: null, s1: "s2", s2: null });
    });

    it("picks the closest column when two steps cover the same rows", () => {
        const grid = treeToGrid([step("s2", [step("s1", [ingredient("i1")])])]);

        const { parentOf } = analyzeGrid(grid);

        expect(parentOf.get("i1").id).toBe("s1");
    });
});

describe("gridToTree", () => {
    it("rebuilds the tree treeToGrid() came from", () => {
        const grid = nestedGrid();

        const tree = gridToTree(grid, analyzeGrid(grid));

        expect(tree).toEqual(nestedTree());
    });
});

describe("buildRenderTable", () => {
    it("adds the ingredient column to the step columns", () => {
        const grid = nestedGrid();

        const table = buildRenderTable(grid, analyzeGrid(grid));

        expect(table.columnCount).toBe(3);
    });

    it("fills each row up to its parent with one gap per column", () => {
        const grid = nestedGrid();

        const table = buildRenderTable(grid, analyzeGrid(grid));

        const rows = table.rows.map((cells) =>
            cells.map((cell) => `${cell.type}:${cell.node?.id ?? cell.col}`),
        );
        expect(rows).toEqual([
            ["ingredient:i1", "step:s1", "step:s2"],
            ["ingredient:i2"],
            ["ingredient:i3", "gap:1"],
            ["ingredient:i4", "gap:1", "gap:2"],
        ]);
    });

    it("only lets the gaps of a maximal cell start a drag", () => {
        const grid = nestedGrid();

        const table = buildRenderTable(grid, analyzeGrid(grid));

        const gaps = table.rows
            .flat()
            .filter((cell) => cell.type === "gap")
            .map((cell) => [cell.rowStart, cell.col, cell.draggable]);
        expect(gaps).toEqual([
            [2, 1, false],
            [3, 1, true],
            [3, 2, true],
        ]);
    });

    it("has no row for an empty grid", () => {
        const grid = treeToGrid([]);

        const table = buildRenderTable(grid, analyzeGrid(grid));

        expect(table.rows).toEqual([]);
    });
});

describe("maximalBlocks", () => {
    it("returns the cells without parent, sorted by row", () => {
        const grid = nestedGrid();

        const blocks = maximalBlocks(grid);

        expect(ids(blocks)).toEqual(["s2", "i4"]);
    });
});

describe("computeDragPreview", () => {
    it("covers every block between the anchor and the hovered row", () => {
        const blocks = maximalBlocks(nestedGrid());

        const preview = computeDragPreview(blocks, 3, 3, 0);

        expect(preview).toEqual({ col: 3, rowStart: 0, rowSpan: 4 });
    });

    it("stops before a block that is not left of the dragged column", () => {
        const blocks = maximalBlocks(nestedGrid());

        const preview = computeDragPreview(blocks, 2, 3, 0);

        expect(preview).toEqual({ col: 2, rowStart: 3, rowSpan: 1 });
    });

    it("clamps a hover below the last row", () => {
        const blocks = maximalBlocks(nestedGrid());

        const preview = computeDragPreview(blocks, 3, 0, 99);

        expect(preview).toEqual({ col: 3, rowStart: 0, rowSpan: 4 });
    });

    it("refuses a drag anchored on a block that is not left of the column", () => {
        const blocks = maximalBlocks(nestedGrid());

        const preview = computeDragPreview(blocks, 1, 0, 3);

        expect(preview).toBeNull();
    });

    it("refuses a drag on an empty grid", () => {
        const blocks = [];

        const preview = computeDragPreview(blocks, 1, 0, 0);

        expect(preview).toBeNull();
    });
});

describe("isValidStepPlacement", () => {
    it.each([
        ["a span covering whole blocks left of the column", 3, 0, 4, true],
        ["a single block left of the column", 1, 3, 1, true],
        ["a span covering a block of the same column", 2, 0, 4, false],
        ["a span starting in the middle of a block", 3, 1, 3, false],
        ["a span ending in the middle of a block", 3, 0, 2, false],
    ])("%s", (_label, col, rowStart, rowSpan, expected) => {
        const grid = nestedGrid();

        const valid = isValidStepPlacement(grid, col, rowStart, rowSpan);

        expect(valid).toBe(expected);
    });
});

describe("createStepFromDrag", () => {
    it("adds an empty step over the previewed span", () => {
        const grid = nestedGrid();

        const created = createStepFromDrag(grid, { col: 3, rowStart: 0, rowSpan: 4 });

        expect(created).toMatchObject({ text: "", col: 3, rowStart: 0, rowSpan: 4 });
        expect(grid.steps.at(-1)).toBe(created);
    });

    it("gives two steps different ids", () => {
        const grid = treeToGrid([ingredient("i1"), ingredient("i2")]);

        const created = [
            createStepFromDrag(grid, { col: 1, rowStart: 0, rowSpan: 1 }),
            createStepFromDrag(grid, { col: 1, rowStart: 1, rowSpan: 1 }),
        ];

        expect(created[0].id).not.toBe(created[1].id);
    });

    it("leaves the grid untouched for an invalid span", () => {
        const grid = nestedGrid();

        const created = createStepFromDrag(grid, { col: 2, rowStart: 0, rowSpan: 4 });

        expect(created).toBeNull();
        expect(ids(grid.steps)).toEqual(["s1", "s2"]);
    });

    it("leaves the grid untouched without preview", () => {
        const grid = nestedGrid();

        const created = createStepFromDrag(grid, null);

        expect(created).toBeNull();
        expect(ids(grid.steps)).toEqual(["s1", "s2"]);
    });
});

describe("ungroupStep", () => {
    it("hands the children of the removed step over to its parent", () => {
        const grid = nestedGrid();

        const removed = ungroupStep(grid, "s1");

        expect(removed).toBe(true);
        expect(gridToTree(grid, analyzeGrid(grid))).toEqual([
            step("s2", [ingredient("i1"), ingredient("i2"), ingredient("i3")]),
            ingredient("i4"),
        ]);
    });

    it("ignores an unknown step", () => {
        const grid = nestedGrid();

        const removed = ungroupStep(grid, "unknown");

        expect(removed).toBe(false);
        expect(ids(grid.steps)).toEqual(["s1", "s2"]);
    });
});

describe("addColumn", () => {
    it("adds a step column", () => {
        const grid = nestedGrid();

        addColumn(grid);

        expect(grid.columnCount).toBe(3);
    });
});

describe("canRemoveColumn", () => {
    it("refuses when the last column holds a step", () => {
        const grid = nestedGrid();

        const removable = canRemoveColumn(grid);

        expect(removable).toBe(false);
    });

    it("refuses to remove the only step column", () => {
        const grid = treeToGrid([ingredient("i1")]);

        const removable = canRemoveColumn(grid);

        expect(removable).toBe(false);
    });

    it("accepts when the last column is empty", () => {
        const grid = nestedGrid();
        addColumn(grid);

        const removable = canRemoveColumn(grid);

        expect(removable).toBe(true);
    });
});

describe("removeColumn", () => {
    it("removes an empty last column", () => {
        const grid = nestedGrid();
        addColumn(grid);

        const removed = removeColumn(grid);

        expect(removed).toBe(true);
        expect(grid.columnCount).toBe(2);
    });

    it("keeps a last column holding a step", () => {
        const grid = nestedGrid();

        const removed = removeColumn(grid);

        expect(removed).toBe(false);
        expect(grid.columnCount).toBe(2);
    });
});

describe("addIngredientRow", () => {
    it("appends an empty ingredient", () => {
        const grid = nestedGrid();

        const added = addIngredientRow(grid);

        expect(added).toMatchObject({
            type: "ingredient",
            label: "",
            min: null,
            max: null,
            unit: null,
        });
        expect(grid.ingredients.at(-1)).toBe(added);
    });
});

describe("canDeleteIngredientRow", () => {
    it("refuses the only row of a step", () => {
        const grid = treeToGrid([step("s1", [ingredient("i1")]), ingredient("i2")]);

        const deletable = canDeleteIngredientRow(grid, 0);

        expect(deletable).toBe(false);
    });

    it("accepts a row whose steps keep another row", () => {
        const grid = nestedGrid();

        const deletable = canDeleteIngredientRow(grid, 0);

        expect(deletable).toBe(true);
    });
});

describe("deleteIngredientRow", () => {
    it("shrinks the steps covering the deleted row", () => {
        const grid = nestedGrid();

        const deleted = deleteIngredientRow(grid, 0);

        expect(deleted).toBe(true);
        expect(ids(grid.ingredients)).toEqual(["i2", "i3", "i4"]);
        expect(spans(grid)).toEqual({
            s1: { col: 1, rowStart: 0, rowSpan: 1 },
            s2: { col: 2, rowStart: 0, rowSpan: 2 },
        });
    });

    it("shifts up the steps below the deleted row", () => {
        const grid = treeToGrid([
            ingredient("i1"),
            step("s1", [ingredient("i2"), ingredient("i3")]),
        ]);

        const deleted = deleteIngredientRow(grid, 0);

        expect(deleted).toBe(true);
        expect(spans(grid)).toEqual({ s1: { col: 1, rowStart: 0, rowSpan: 2 } });
    });

    it("leaves the steps above the deleted row alone", () => {
        const grid = nestedGrid();

        const deleted = deleteIngredientRow(grid, 3);

        expect(deleted).toBe(true);
        expect(spans(grid)).toEqual({
            s1: { col: 1, rowStart: 0, rowSpan: 2 },
            s2: { col: 2, rowStart: 0, rowSpan: 3 },
        });
    });

    it("keeps the only row of a step", () => {
        const grid = treeToGrid([step("s1", [ingredient("i1")]), ingredient("i2")]);

        const deleted = deleteIngredientRow(grid, 0);

        expect(deleted).toBe(false);
        expect(ids(grid.ingredients)).toEqual(["i1", "i2"]);
    });
});

describe("canMoveCell", () => {
    it.each([
        ["an ingredient down to its sibling", "i1", 1, true],
        ["the first ingredient of a step up", "i1", -1, false],
        ["an ingredient up over a sibling step", "i3", -1, true],
        ["a root step down over a root ingredient", "s2", 1, true],
        ["the last root down", "i4", 1, false],
        ["an unknown cell", "unknown", 1, false],
    ])("%s", (_label, cellId, direction, expected) => {
        const grid = nestedGrid();

        const movable = canMoveCell(grid, cellId, direction);

        expect(movable).toBe(expected);
    });
});

describe("moveCell", () => {
    it("moves a step with everything it contains", () => {
        const grid = nestedGrid();

        const moved = moveCell(grid, "s2", 1);

        expect(moved).toBe(true);
        expect(gridToTree(grid, analyzeGrid(grid))).toEqual([
            ingredient("i4"),
            step("s2", [step("s1", [ingredient("i1"), ingredient("i2")]), ingredient("i3")]),
        ]);
    });

    it("swaps an ingredient with a sibling step without changing their parent", () => {
        const grid = nestedGrid();

        const moved = moveCell(grid, "i3", -1);

        expect(moved).toBe(true);
        expect(gridToTree(grid, analyzeGrid(grid))).toEqual([
            step("s2", [ingredient("i3"), step("s1", [ingredient("i1"), ingredient("i2")])]),
            ingredient("i4"),
        ]);
    });

    it("shifts the steps of both swapped cells by the span of the other one", () => {
        const grid = treeToGrid([
            step("s1", [ingredient("i1"), ingredient("i2")]),
            step("s2", [ingredient("i3")]),
        ]);

        const moved = moveCell(grid, "s2", -1);

        expect(moved).toBe(true);
        expect(ids(grid.ingredients)).toEqual(["i3", "i1", "i2"]);
        expect(spans(grid)).toEqual({
            s1: { col: 1, rowStart: 1, rowSpan: 2 },
            s2: { col: 1, rowStart: 0, rowSpan: 1 },
        });
    });

    it("leaves the grid untouched when there is no sibling in that direction", () => {
        const grid = nestedGrid();

        const moved = moveCell(grid, "i4", 1);

        expect(moved).toBe(false);
        expect(gridToTree(grid, analyzeGrid(grid))).toEqual(nestedTree());
    });
});
