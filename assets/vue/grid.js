let idCounter = 0;

function nextId(prefix) {
    idCounter += 1;
    return `${prefix}${Date.now().toString(36)}${idCounter}`;
}

/**
 * Convertit un arbre (roots) en grille éditable: une ligne par ingrédient, une étape par
 * cellule à droite. Porte le même algorithme que GridBuilder::measure() côté PHP: la colonne
 * d'une étape se calcule depuis ses enfants (1 + le max de leur colonne), pas depuis la racine.
 * `columnCount` (nombre de colonnes d'étapes, >= 1) part du maximum nécessaire pour afficher
 * l'arbre chargé; au-delà, il devient un état d'édition libre piloté par +/- colonne.
 */
export function treeToGrid(roots) {
    const ingredients = [];
    const steps = [];

    function visit(node) {
        if (node.type === "ingredient") {
            const rowStart = ingredients.length;
            ingredients.push(node);
            return { col: 0, rowStart, rowSpan: 1 };
        }

        const rowStart = ingredients.length;
        let col = 0;
        for (const child of node.children) {
            const metrics = visit(child);
            col = Math.max(col, metrics.col + 1);
        }

        const rowSpan = ingredients.length - rowStart;
        steps.push({ id: node.id, text: node.text, col, rowStart, rowSpan });

        return { col, rowStart, rowSpan };
    }

    for (const root of roots) {
        visit(root);
    }

    const columnCount = steps.reduce((max, step) => Math.max(max, step.col), 1);

    return { ingredients, steps, columnCount };
}

function cellsOf(grid) {
    const ingredientCells = grid.ingredients.map((node, index) => ({
        id: node.id,
        kind: "ingredient",
        node,
        col: 0,
        rowStart: index,
        rowSpan: 1,
    }));

    const stepCells = grid.steps.map((step) => ({
        id: step.id,
        kind: "step",
        node: step,
        col: step.col,
        rowStart: step.rowStart,
        rowSpan: step.rowSpan,
    }));

    return { ingredientCells, stepCells, allCells: [...ingredientCells, ...stepCells] };
}

function properlyContains(outer, inner) {
    if (outer === inner || outer.col <= inner.col) {
        return false;
    }

    return (
        outer.rowStart <= inner.rowStart &&
        inner.rowStart + inner.rowSpan <= outer.rowStart + outer.rowSpan
    );
}

// Le parent d'une cellule est le conteneur le plus étroit qui la contient: en cas d'égalité de
// portée (chaîne d'étapes à enfant unique), c'est celui dont la colonne est la plus proche.
function findParent(cell, stepCells) {
    let best = null;

    for (const step of stepCells) {
        if (!properlyContains(step, cell)) {
            continue;
        }

        if (
            best === null ||
            step.rowSpan < best.rowSpan ||
            (step.rowSpan === best.rowSpan && step.col < best.col)
        ) {
            best = step;
        }
    }

    return best;
}

export function analyzeGrid(grid) {
    const { allCells, stepCells } = cellsOf(grid);
    const parentOf = new Map();

    for (const cell of allCells) {
        parentOf.set(cell.id, findParent(cell, stepCells));
    }

    return { allCells, parentOf };
}

/**
 * L'inverse de treeToGrid(). Bien définie tant que les portées des cellules forment une famille
 * laminaire et que chaque étape est l'union exacte des portées des cellules qu'elle contient
 * immédiatement — invariant garanti par construction par createStepFromDrag()/ungroupStep(),
 * jamais vérifié ici.
 */
export function gridToTree(grid, analysis) {
    const { allCells, parentOf } = analysis;

    const childrenOf = new Map();
    for (const cell of allCells) {
        if (cell.kind === "step") {
            childrenOf.set(cell.id, []);
        }
    }

    const roots = [];
    for (const cell of allCells) {
        const parent = parentOf.get(cell.id);
        if (parent === null) {
            roots.push(cell);
        } else {
            childrenOf.get(parent.id).push(cell);
        }
    }

    function toNode(cell) {
        if (cell.kind === "ingredient") {
            return cell.node;
        }

        const children = childrenOf
            .get(cell.id)
            .sort((a, b) => a.rowStart - b.rowStart)
            .map(toNode);

        return { type: "step", id: cell.node.id, text: cell.node.text, children };
    }

    return roots.sort((a, b) => a.rowStart - b.rowStart).map(toNode);
}

/**
 * Calcule le tableau à afficher, à partir de l'état vivant de la grille. `node` référence
 * directement les objets réactifs de la grille, pour que les champs édités dans le tableau
 * écrivent dans le même état que celui sérialisé par gridToTree(). Chaque case vide (« gap »)
 * occupe exactement une colonne — jamais fusionnée sur plusieurs — pour que chaque colonne
 * d'étape soit une cible de glissé indépendante; `draggable` marque celles qui proviennent
 * d'une cellule maximale (seules candidates à devenir enfants d'une nouvelle étape).
 */
export function buildRenderTable(grid, analysis) {
    const { allCells, parentOf } = analysis;

    const columnCount = 1 + grid.columnCount;
    const rowCount = grid.ingredients.length;
    const cellsByRow = Array.from({ length: Math.max(rowCount, 1) }, () => []);

    for (const cell of allCells) {
        const parent = parentOf.get(cell.id);
        const maximal = parent === null;
        const parentCol = parent ? parent.col : columnCount;

        cellsByRow[cell.rowStart].push({
            type: cell.kind,
            col: cell.col,
            rowStart: cell.rowStart,
            rowSpan: cell.rowSpan,
            node: cell.node,
        });

        for (let col = cell.col + 1; col < parentCol; col += 1) {
            cellsByRow[cell.rowStart].push({
                type: "gap",
                col,
                rowStart: cell.rowStart,
                rowSpan: cell.rowSpan,
                node: null,
                draggable: maximal,
            });
        }
    }

    const rows = cellsByRow
        .slice(0, rowCount)
        .map((cells) => [...cells].sort((a, b) => a.col - b.col));

    return { rows, columnCount };
}

/**
 * Les blocs maximaux (cellules sans parent) triés par ligne. Par construction de
 * createStepFromDrag()/ungroupStep(), ils recouvrent toujours 0..rowCount-1 sans trou ni
 * chevauchement: c'est la partition dans laquelle le glissé accroche.
 */
export function maximalBlocks(grid, analysis = analyzeGrid(grid)) {
    const { allCells, parentOf } = analysis;

    return allCells
        .filter((cell) => parentOf.get(cell.id) === null)
        .sort((a, b) => a.rowStart - b.rowStart);
}

function blockIndexForRow(blocks, row) {
    return blocks.findIndex(
        (block) => row >= block.rowStart && row < block.rowStart + block.rowSpan,
    );
}

/**
 * Calcule, pendant un glissé démarré en (col, anchorRow) et actuellement survolé à hoverRow,
 * la portée qui serait couverte si le pointeur relâchait maintenant — ou null si le glissé
 * est irrecevable dans cette colonne. Deux règles font qu'une étape invalide est inatteignable:
 *
 * 1. Seuls les blocs maximaux de colonne < col peuvent devenir enfants d'une étape en
 *    colonne col (propertyContains exige outer.col > inner.col) — les autres blocs ne sont
 *    donc jamais candidats.
 * 2. L'extension du glissé s'arrête net, sans jamais le franchir, au premier bloc de
 *    colonne >= col rencontré dans la direction du survol — elle ne peut donc jamais
 *    inclure un tel bloc, même partiellement.
 *
 * Le résultat est toujours l'union d'une suite contiguë de blocs maximaux tous de colonne
 * < col: exactement l'invariant que gridToTree() suppose déjà vrai.
 */
export function computeDragPreview(blocks, col, anchorRow, hoverRow) {
    if (blocks.length === 0) {
        return null;
    }

    const anchorIndex = blockIndexForRow(blocks, anchorRow);
    if (anchorIndex === -1 || blocks[anchorIndex].col >= col) {
        return null;
    }

    const lastBlock = blocks[blocks.length - 1];
    const clampedHover = Math.max(
        0,
        Math.min(hoverRow, lastBlock.rowStart + lastBlock.rowSpan - 1),
    );
    let hoverIndex = blockIndexForRow(blocks, clampedHover);
    if (hoverIndex === -1) {
        hoverIndex = anchorIndex;
    }

    const step = hoverIndex >= anchorIndex ? 1 : -1;
    let reached = anchorIndex;
    for (let i = anchorIndex + step; step > 0 ? i <= hoverIndex : i >= hoverIndex; i += step) {
        if (blocks[i].col >= col) {
            break;
        }
        reached = i;
    }

    const lo = Math.min(anchorIndex, reached);
    const hi = Math.max(anchorIndex, reached);

    return {
        col,
        rowStart: blocks[lo].rowStart,
        rowSpan: blocks[hi].rowStart + blocks[hi].rowSpan - blocks[lo].rowStart,
    };
}

/**
 * Rejoué juste avant la mutation, indépendamment de l'aperçu de glissé: la portée doit être
 * exactement l'union d'une suite contiguë de blocs maximaux, tous de colonne < col. Ne fait
 * confiance à aucun état intermédiaire de l'UI.
 */
export function isValidStepPlacement(grid, col, rowStart, rowSpan) {
    const blocks = maximalBlocks(grid);
    const rowEnd = rowStart + rowSpan;

    let index = blocks.findIndex((block) => block.rowStart === rowStart);
    if (index === -1) {
        return false;
    }

    let cursor = rowStart;
    while (cursor < rowEnd) {
        const block = blocks[index];
        if (!block || block.rowStart !== cursor || block.col >= col) {
            return false;
        }
        cursor += block.rowSpan;
        index += 1;
    }

    return cursor === rowEnd;
}

/**
 * Crée une étape à partir d'un aperçu de glissé (ou de toute portée {col, rowStart, rowSpan}
 * calculée ailleurs). Ne touche à rien d'autre: les cellules couvertes restent dans la
 * grille, elles cessent seulement d'être maximales.
 */
export function createStepFromDrag(grid, preview) {
    if (!preview || !isValidStepPlacement(grid, preview.col, preview.rowStart, preview.rowSpan)) {
        return null;
    }

    const step = {
        id: nextId("s"),
        text: "",
        col: preview.col,
        rowStart: preview.rowStart,
        rowSpan: preview.rowSpan,
    };
    grid.steps.push(step);

    return step;
}

/**
 * Dissout une étape: ses enfants (déjà présents dans la grille) redeviennent maximaux, ou
 * remontent au parent de l'étape dissoute si elle en avait un — dans les deux cas, cette
 * réaffectation est purement géométrique, il n'y a rien d'autre à faire.
 */
export function ungroupStep(grid, stepId) {
    const index = grid.steps.findIndex((step) => step.id === stepId);
    if (index === -1) {
        return false;
    }

    grid.steps.splice(index, 1);

    return true;
}

export function addColumn(grid) {
    grid.columnCount += 1;
}

// Refuser: la dernière colonne contiendrait une étape, la retirer la détruirait.
export function canRemoveColumn(grid) {
    return grid.columnCount > 1 && !grid.steps.some((step) => step.col === grid.columnCount);
}

export function removeColumn(grid) {
    if (!canRemoveColumn(grid)) {
        return false;
    }

    grid.columnCount -= 1;

    return true;
}

export function addIngredientRow(grid) {
    const ingredient = {
        type: "ingredient",
        id: nextId("i"),
        label: "",
        min: null,
        max: null,
        unit: null,
    };

    grid.ingredients.push(ingredient);

    return ingredient;
}

// Refuser: une étape dont l'unique ligne serait celle-ci se retrouverait sans enfant.
export function canDeleteIngredientRow(grid, index) {
    return !grid.steps.some((step) => step.rowStart === index && step.rowSpan === 1);
}

export function deleteIngredientRow(grid, index) {
    if (!canDeleteIngredientRow(grid, index)) {
        return false;
    }

    grid.ingredients.splice(index, 1);

    for (const step of grid.steps) {
        if (step.rowStart > index) {
            step.rowStart -= 1;
        } else if (step.rowStart + step.rowSpan > index) {
            step.rowSpan -= 1;
        }
    }

    return true;
}

// Refuser: si une étape contient l'une des deux lignes mais pas l'autre, l'échange changerait
// ce que cette étape consomme.
export function canMoveIngredientRow(grid, index, direction) {
    const target = index + direction;
    if (target < 0 || target >= grid.ingredients.length) {
        return false;
    }

    const inRange = (step, row) => row >= step.rowStart && row < step.rowStart + step.rowSpan;

    return !grid.steps.some((step) => inRange(step, index) !== inRange(step, target));
}

export function moveIngredientRow(grid, index, direction) {
    if (!canMoveIngredientRow(grid, index, direction)) {
        return false;
    }

    const target = index + direction;
    const [row] = grid.ingredients.splice(index, 1);
    grid.ingredients.splice(target, 0, row);

    return true;
}
