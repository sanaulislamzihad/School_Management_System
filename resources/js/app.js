import Alpine from 'alpinejs';

window.Alpine = Alpine;

/** Add/edit dialog state for generic resource pages. */
Alpine.data('resourcePage', (config) => ({
    open: false,
    deleting: null,
    editingId: config.reopenId ?? null,
    form: { ...config.blank },
    options: config.options,
    init() {
        if (config.reopen) {
            this.form = { ...config.blank, ...config.old };
            this.open = true;
        }
    },
    get action() {
        return this.editingId ? `${config.baseUrl}/${this.editingId}` : config.baseUrl;
    },
    add() {
        this.editingId = null;
        this.form = { ...config.blank };
        this.open = true;
    },
    edit(row) {
        this.editingId = row.id;
        this.form = { ...config.blank };
        for (const key of Object.keys(config.blank)) {
            this.form[key] = row[key] ?? '';
        }
        this.open = true;
    },
    choices(field) {
        const list = this.options[field.name] ?? [];
        if (!field.depends) return list;
        return list.filter((o) => String(o.parent) === String(this.form[field.depends]));
    },
}));

/** Theme toggle kept per browser. */
Alpine.data('themeToggle', () => ({
    dark: document.documentElement.classList.contains('dark'),
    toggle() {
        this.dark = !this.dark;
        document.documentElement.classList.toggle('dark', this.dark);
        try {
            localStorage.setItem('abc-theme', this.dark ? 'dark' : 'light');
        } catch {
            // Storage can be unavailable in private windows; the toggle still works for this page.
        }
    },
}));

Alpine.start();
