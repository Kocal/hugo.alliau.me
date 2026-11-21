export function imageFilesFrom(dataTransfer) {
    return Array.from(dataTransfer?.files ?? []).filter((file) => file.type.startsWith("image/"));
}
