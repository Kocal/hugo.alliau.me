import { Controller } from "@hotwired/stimulus";

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = { key: String };

    connect() {
        this.done = new Set(this._read());
        this._render();
    }

    toggle(event) {
        const nodeId = event.currentTarget.dataset.nodeId;
        if (!nodeId) return;

        if (this.done.has(nodeId)) {
            this.done.delete(nodeId);
        } else {
            this.done.add(nodeId);
        }

        this._write();
        this._render();
    }

    reset() {
        this.done.clear();
        this._write();
        this._render();
    }

    _render() {
        for (const element of this.element.querySelectorAll("[data-node-id]")) {
            const isDone = this.done.has(element.dataset.nodeId);

            element.classList.toggle("is-done", isDone);

            // L'opacité et le barré ne disent rien à un lecteur d'écran.
            if (element.hasAttribute("aria-pressed")) {
                element.setAttribute("aria-pressed", isDone ? "true" : "false");
            }
        }
    }

    _storageKey() {
        return `recipe:${this.keyValue}`;
    }

    _read() {
        try {
            const raw = window.localStorage.getItem(this._storageKey());
            const parsed = raw ? JSON.parse(raw) : [];
            return Array.isArray(parsed) ? parsed : [];
        } catch {
            return [];
        }
    }

    _write() {
        try {
            window.localStorage.setItem(this._storageKey(), JSON.stringify([...this.done]));
        } catch {
            // Mode navigation privée ou quota atteint: la progression n'est simplement pas mémorisée.
        }
    }
}
