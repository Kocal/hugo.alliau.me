import {Controller} from '@hotwired/stimulus';
import {getHighlighterCore} from 'shiki'
import {
    transformerCompactLineOptions,
    transformerNotationDiff,
    transformerNotationErrorLevel,
    transformerNotationFocus,
    transformerNotationHighlight,
} from '@shikijs/transformers'

// `shiki/wasm` contains the wasm binary inlined as base64 string.
import getWasm from 'shiki/wasm'

/**
 * Thanks to VitePress, because I can't use the `transformerMetaHighlight` transformer directly.
 * @https://github.com/vuejs/vitepress/blob/main/src/node/markdown/plugins/highlight.ts
 */
const attrsToLines = (attrs) => {
    attrs = attrs.replace(/^(?:\[.*?\])?.*?([\d,-]+).*/, '$1').trim()
    const result= []
    if (!attrs) {
        return []
    }
    attrs
        .split(',')
        .map((v) => v.split('-').map((v) => parseInt(v, 10)))
        .forEach(([start, end]) => {
            if (start && end) {
                result.push(
                    ...Array.from({ length: end - start + 1 }, (_, i) => start + i)
                )
            } else {
                result.push(start)
            }
        })
    return result.map((v) => ({
        line: v,
        classes: ['highlighted']
    }))
}

const highlighter = await getHighlighterCore({
    themes: [
        import('shiki/themes/github-light.mjs')
    ],
    langs: [
        () => import('shiki/langs/javascript.mjs'),
        () => import('shiki/langs/php.mjs'),
        () => import('shiki/langs/shell.mjs'),
        () => import('shiki/langs/yaml.mjs'),
        () => import('shiki/langs/bash.mjs'),
        () => import('shiki/langs/sql.mjs'),
    ],
    loadWasm: getWasm,
})

const transformers = [
    transformerNotationDiff(),
    transformerNotationFocus({
        classActiveLine: 'has-focus',
        classActivePre: 'has-focused-lines'
    }),
    transformerNotationHighlight(),
    transformerNotationErrorLevel(),
    {
        name: 'app:clean-up',
        pre: (node) => {
            // We need to remove the `style` attribute added by Shiki, and let Tailwind handle the styling.
            node.properties.style = '';

            node.properties.class = node.properties.class || '';
            node.properties.class += ' no-prose';
        }
    },
];

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = {
        language: String,
        meta: String,
    }

    static targets = ['pre', 'code'];

    connect() {
        const language = this.languageValue;
        const meta = this.metaValue;

        const lineOptions = attrsToLines(meta)
        const code = this.codeTarget.textContent;

        this.preTarget.outerHTML = highlighter.codeToHtml(code, {
            lang: language,
            theme: 'github-light',
            meta: {
                __raw: meta,
            },
            transformers: [
                ...transformers,
                transformerCompactLineOptions(lineOptions),
                {
                    name: 'app:language',
                    code(node) {
                        node.properties['data-language'] = language;
                    }
                }
            ]
        });
    }
}
