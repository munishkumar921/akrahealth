<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { languages as allLanguages } from "@/Data/languages";
import { useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";

const props = defineProps({
    languageSettings: {
        type: Object,
        default: () => ({
            languages: [],
            default_language: "en",
        }),
    },
});

const initialLanguages = (props.languageSettings.languages || []).map((language) => ({
    name: language.name,
    code: String(language.code).toLowerCase(),
    enabled: Boolean(language.enabled),
}));

const form = useForm({
    languages: initialLanguages,
    default_language: props.languageSettings.default_language || "en",
});

const newLanguageCode = ref("");
const keyword = ref("");

const selectedLanguageOption = computed(() =>
    allLanguages.find((language) => language.value === newLanguageCode.value)
);

const languageOptions = computed(() =>
    allLanguages.filter(
        (option) =>
            !form.languages.some(
                (language) => language.code.toLowerCase() === option.value.toLowerCase()
            )
    )
);

const filteredLanguages = computed(() => {
    const query = keyword.value.trim().toLowerCase();

    if (!query) {
        return form.languages;
    }

    return form.languages.filter((language) => {
        const haystack = `${language.name} ${language.code}`.toLowerCase();
        return haystack.includes(query);
    });
});

const metrics = computed(() => {
    const enabled = form.languages.filter((language) => language.enabled).length;

    return [
        {
            label: "Total Languages",
            value: form.languages.length,
            tone: "sky",
        },
        {
            label: "Enabled",
            value: enabled,
            tone: "emerald",
        },
        {
            label: "Default Locale",
            value: (form.default_language || "en").toUpperCase(),
            tone: "indigo",
        },
    ];
});

function setDefault(language) {
    form.default_language = language.code;
    language.enabled = true;
}

function toggleEnabled(language) {
    if (language.code === form.default_language) {
        language.enabled = true;
        return;
    }

    language.enabled = !language.enabled;
}

function addLanguage() {
    if (!selectedLanguageOption.value) {
        return;
    }

    form.languages.push({
        name: selectedLanguageOption.value.label,
        code: selectedLanguageOption.value.value.toLowerCase(),
        enabled: true,
    });

    if (!form.default_language) {
        form.default_language = selectedLanguageOption.value.value.toLowerCase();
    }

    newLanguageCode.value = "";
}

function removeLanguage(language) {
    if (language.code === form.default_language) {
        return;
    }

    form.languages = form.languages.filter((item) => item.code !== language.code);
}

function saveLanguages() {
    if (!form.languages.some((language) => language.code === form.default_language)) {
        const firstEnabled = form.languages.find((language) => language.enabled);
        form.default_language = firstEnabled?.code || form.languages[0]?.code || "en";
    }

    const defaultLanguage = form.languages.find(
        (language) => language.code === form.default_language
    );

    if (defaultLanguage) {
        defaultLanguage.enabled = true;
    }

    form.post(route("superAdmin.languagesetting.update"), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AuthLayout title="Language Manager" description="Super Admin - Language Manager" heading="Language Manager">
        <section class="language-manager-page">


            <div class="language-workspace">
                <section class="language-panel language-list-panel">
                    <div class="panel-header">
                        <div>
                            <p class="panel-kicker">Configured Locales</p>
                            <h2>Supported languages</h2>
                        </div>

                        <div class="language-search">
                            <span class="input-group-text bg-white border-end-0 border rounded-circle-left">
                                <i class="bi bi-search text-muted"></i>
                            </span>
                            <input v-model="keyword" type="text"
                                class="form-control border-start-0 rounded-circle-right"
                                placeholder="Search by language name or code" />
                        </div>
                    </div>

                    <div class="language-list">
                        <article v-for="language in filteredLanguages" :key="language.code" class="language-card">
                            <div class="language-card-main">
                                <div class="language-code-pill">
                                    {{ language.code.toUpperCase() }}
                                </div>

                                <div class="language-meta">
                                    <div class="language-title-row">
                                        <h3>{{ language.name }}</h3>
                                        <span v-if="language.code === form.default_language"
                                            class="badge default-badge">
                                            Default
                                        </span>
                                    </div>

                                    <p>{{ language.code }}</p>

                                    <label class="default-option">
                                        <input type="radio" name="default_language"
                                            :checked="language.code === form.default_language"
                                            @change="setDefault(language)" />
                                        <span>Set as default</span>
                                    </label>
                                </div>
                            </div>

                            <div class="language-card-actions">
                                <label class="settings-switch">
                                    <input type="checkbox" :checked="language.enabled"
                                        @change="toggleEnabled(language)" />
                                    <span class="settings-slider"></span>
                                </label>

                                <button type="button" class="icon-button"
                                    :disabled="language.code === form.default_language"
                                    @click="removeLanguage(language)">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </div>
                        </article>

                        <div v-if="filteredLanguages.length === 0" class="empty-state">
                            No languages match the current search.
                        </div>
                    </div>
                </section>

                <aside class="language-panel language-side-panel">
                    <div class="panel-header compact">
                        <div>
                            <p class="panel-kicker">Add Language</p>
                            <h2>Expand supported locales</h2>
                        </div>
                    </div>

                    <div class="side-card">
                        <label class="small-label">Language</label>
                        <select v-model="newLanguageCode" class="form-select form-control h-50">
                            <option value="">Select a language</option>
                            <option v-for="option in languageOptions" :key="option.value" :value="option.value">
                                {{ option.label }} ({{ option.value }})
                            </option>
                        </select>

                        <button type="button" class="btn btn-outline-primary w-100 mt-3" :disabled="!newLanguageCode"
                            @click="addLanguage">
                            <i class="fa fa-plus me-2"></i>
                            Add language
                        </button>
                    </div>

                    <div class="side-card info-card">
                        <h3>How this works</h3>
                        <ul>
                            <li>The default language always stays enabled.</li>
                            <li>Enabled languages are shared across the app runtime.</li>
                            <li>Changes are saved only when you click `Save Changes`.</li>
                        </ul>
                    </div>
                </aside>
            </div>
        </section>
    </AuthLayout>
</template>

<style scoped>
.language-manager-page {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    padding: 1rem 0 2rem;
}

.language-hero {
    display: flex;
    justify-content: space-between;
    gap: 1.5rem;
    align-items: flex-start;
    padding: 2rem;
    border-radius: 28px;
    background:
        radial-gradient(circle at top left, rgba(32, 156, 238, 0.16), transparent 38%),
        linear-gradient(135deg, #ffffff 0%, #f5fbff 100%);
    border: 1px solid #d8ebfb;
    box-shadow: 0 18px 45px rgba(14, 116, 144, 0.08);
}

.language-kicker,
.panel-kicker {
    margin: 0 0 0.35rem;
    font-size: 0.78rem;
    letter-spacing: 0.16em;
    text-transform: uppercase;
    color: #1792e6;
    font-weight: 700;
}

.language-hero h1,
.panel-header h2 {
    margin: 0;
    color: #1c2740;
    font-size: 2.15rem;
    font-weight: 700;
}

.language-copy {
    max-width: 720px;
    margin: 0.75rem 0 0;
    color: #5f7293;
    font-size: 1rem;
    line-height: 1.75;
}

.language-hero-actions {
    display: flex;
    align-items: flex-start;
}

.language-metrics {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 1rem;
}

.metric-card {
    padding: 1.25rem 1.4rem;
    border-radius: 24px;
    border: 1px solid #dceaf8;
    background: #fff;
    box-shadow: 0 14px 35px rgba(31, 76, 121, 0.07);
}

.metric-label {
    display: block;
    color: #6580a0;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.08em;
}

.metric-value {
    display: block;
    margin-top: 0.45rem;
    font-size: 1.8rem;
    color: #1c2740;
}

.language-workspace {
    display: grid;
    grid-template-columns: minmax(0, 2fr) minmax(320px, 0.95fr);
    gap: 1.5rem;
}

.language-panel {
    border-radius: 28px;
    background: #fff;
    border: 1px solid #dceaf8;
    box-shadow: 0 18px 42px rgba(31, 76, 121, 0.08);
    padding: 1.5rem;
}

.panel-header {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    align-items: center;
    margin-bottom: 1.25rem;
}

.panel-header.compact {
    margin-bottom: 1rem;
}

.language-search {
    display: flex;
    align-items: center;
    width: min(100%, 380px);
}

.language-search .form-control,
.language-search .input-group-text {
    height: 48px;
}

.language-list {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.language-card {
    display: flex;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.2rem 1.25rem;
    border: 1px solid #d9e8f7;
    border-radius: 22px;
    background: linear-gradient(180deg, #ffffff 0%, #f9fcff 100%);
}

.language-card-main {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.language-code-pill {
    min-width: 62px;
    height: 62px;
    display: grid;
    place-items: center;
    border-radius: 18px;
    background: linear-gradient(135deg, #e7f3ff 0%, #f3f9ff 100%);
    color: #0b78c6;
    font-weight: 700;
    letter-spacing: 0.08em;
}

.language-meta h3 {
    margin: 0;
    font-size: 1.2rem;
    color: #1c2740;
}

.language-meta p {
    margin: 0.3rem 0 0.7rem;
    color: #7590b0;
}

.language-title-row {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    flex-wrap: wrap;
}

.default-badge {
    background: rgba(23, 146, 230, 0.12);
    color: #0b78c6;
    border-radius: 999px;
    padding: 0.38rem 0.7rem;
    font-weight: 600;
}

.default-option {
    display: inline-flex;
    align-items: center;
    gap: 0.55rem;
    color: #6580a0;
}

.language-card-actions {
    display: flex;
    align-items: center;
    gap: 0.85rem;
}

.icon-button {
    width: 42px;
    height: 42px;
    border-radius: 14px;
    border: 1px solid #d9e8f7;
    background: #fff;
    color: #f15a60;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.icon-button:disabled {
    opacity: 0.45;
    cursor: not-allowed;
}

.settings-switch {
    position: relative;
    display: inline-flex;
    width: 56px;
    height: 30px;
}

.settings-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.settings-slider {
    position: absolute;
    inset: 0;
    border-radius: 999px;
    background: #d2dcea;
    transition: all 0.2s ease;
}

.settings-slider::before {
    content: "";
    position: absolute;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    left: 3px;
    top: 3px;
    background: #fff;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.15);
    transition: transform 0.2s ease;
}

.settings-switch input:checked+.settings-slider {
    background: #2f80ed;
}

.settings-switch input:checked+.settings-slider::before {
    transform: translateX(26px);
}

.side-card {
    border: 1px solid #dceaf8;
    border-radius: 22px;
    padding: 1.2rem;
    background: linear-gradient(180deg, #ffffff 0%, #f9fcff 100%);
}

.language-side-panel {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.small-label {
    display: inline-block;
    margin-bottom: 0.55rem;
    font-weight: 600;
    color: #3c5372;
}

.info-card h3 {
    margin: 0 0 0.85rem;
    font-size: 1.05rem;
    color: #1c2740;
}

.info-card ul {
    margin: 0;
    padding-left: 1.05rem;
    color: #5f7293;
    line-height: 1.75;
}

.empty-state {
    padding: 2rem;
    text-align: center;
    border-radius: 22px;
    border: 1px dashed #cfe2f5;
    color: #7090b3;
}

@media (max-width: 1200px) {
    .language-workspace {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {

    .language-hero,
    .panel-header,
    .language-card {
        flex-direction: column;
        align-items: stretch;
    }

    .language-metrics {
        grid-template-columns: 1fr;
    }

    .language-search {
        width: 100%;
    }
}
</style>
