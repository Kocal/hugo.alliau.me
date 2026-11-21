export function imageSnippet({ alt, key, width, height }) {
    const attributes = width && height ? `{width=${width} height=${height}}` : "";

    return `![${alt}](${key})${attributes}`;
}

export function altFromFilename(name) {
    return name
        .replace(/\.[^.]+$/, "")
        .replace(/[-_]+/g, " ")
        .trim();
}

export function uploadingPlaceholder(name) {
    return `![Uploading ${name}…]()`;
}
