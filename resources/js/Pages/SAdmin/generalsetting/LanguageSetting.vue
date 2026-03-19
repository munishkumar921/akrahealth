<script setup>
import AuthLayout from "@/Layouts/AuthLayout.vue";
import { ref } from "vue";

/*
|--------------------------------------------------------------------------
| LANGUAGE STATE
|--------------------------------------------------------------------------
*/
const languages = ref([
    { name: "English", code: "en", enabled: true, is_default: true },
    { name: "العربية", code: "ar", enabled: true, is_default: false },
    { name: "Dansk", code: "da", enabled: false, is_default: false },
    { name: "Deutsch", code: "de", enabled: true, is_default: false },
    { name: "Español", code: "es", enabled: true, is_default: false },
]);

/*
|--------------------------------------------------------------------------
| ADD NEW LANGUAGE FORM
|--------------------------------------------------------------------------
*/
const newLanguage = ref({
    name: "",
    code: "",
    enabled: true,
});

/*
|--------------------------------------------------------------------------
| SET DEFAULT LANGUAGE
|--------------------------------------------------------------------------
*/
const setDefault = (lang) => {
    languages.value.forEach(l => l.is_default = false);
    lang.is_default = true;
    lang.enabled = true; // default must be enabled
};

/*
|--------------------------------------------------------------------------
| ADD LANGUAGE (UI ONLY)
|--------------------------------------------------------------------------
*/
const addLanguage = () => {
    if (!newLanguage.value.name || !newLanguage.value.code) return;

    languages.value.push({
        name: newLanguage.value.name,
        code: newLanguage.value.code.toLowerCase(),
        enabled: newLanguage.value.enabled,
        is_default: false,
    });

    newLanguage.value = {
        name: "",
        code: "",
        enabled: true,
    };
};
</script>

<template>
<AuthLayout
    title="Language Manager"
    description="Admin - Language Manager"
    heading="Language Manager"
>

    <!-- PAGE HEADER -->
    <div class="page-header mt-5-7 justify-content-center mb-4">
        <div class="page-leftheader text-center">
            <h4 class="page-title mb-0">Language Manager</h4>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-12">

            <!-- LANGUAGE LIST -->
            <div
                v-for="lang in languages"
                :key="lang.code"
                class="card border-0 special-shadow mb-3"
            >
                <div class="card-body d-flex align-items-center justify-content-between">

                    <!-- LEFT -->
                    <div>
                        <h6 class="mb-1 font-weight-bold">
                            {{ lang.name }}
                            <small class="text-muted ms-2">{{ lang.code }}</small>

                            <span
                                v-if="lang.is_default"
                                class="badge bg-primary ms-2"
                            >
                                Default
                            </span>
                        </h6>

                        <!-- DEFAULT RADIO -->
                        <label class="text-muted small d-flex align-items-center gap-2">
                            <input
                                type="radio"
                                name="default_language"
                                :checked="lang.is_default"
                                @change="setDefault(lang)"
                            />
                            Set as default
                        </label>
                    </div>

                    <!-- RIGHT -->
                    <div class="d-flex align-items-center gap-3">

                        <!-- ENABLE TOGGLE -->
                        <label class="custom-switch mb-0">
                            <input
                                type="checkbox"
                                class="custom-switch-input"
                                v-model="lang.enabled"
                                :disabled="lang.is_default"
                            />
                            <span class="custom-switch-indicator"></span>
                        </label>

                        <!-- MENU -->
                        <button class="btn btn-sm btn-light">
                            <i class="fa fa-ellipsis-v"></i>
                        </button>

                    </div>

                </div>
            </div>

            <!-- ADD NEW LANGUAGE -->
            <div class="card border-0 special-shadow mt-4">
                <div class="card-body">
                    <h6 class="font-weight-bold mb-3">Add New Language</h6>

                    <div class="row">
                        <div class="col-md-5 mb-3">
                            <label class="small">Language Name</label>
                            <input
                                v-model="newLanguage.name"
                                class="form-control"
                                placeholder="e.g. Italian"
                            />
                        </div>

                        <div class="col-md-3 mb-3">
                            <label class="small">Language Code</label>
                            <input
                                v-model="newLanguage.code"
                                class="form-control"
                                placeholder="it"
                            />
                        </div>

                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <label class="custom-switch">
                                <input
                                    type="checkbox"
                                    class="custom-switch-input"
                                    v-model="newLanguage.enabled"
                                />
                                <span class="custom-switch-indicator"></span>
                            </label>
                        </div>

                        <div class="col-md-2 mb-3 d-flex align-items-end">
                            <button
                                class="btn btn-primary w-100"
                                @click="addLanguage"
                            >
                                Add
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</AuthLayout>
</template>

<style scoped>
.custom-switch {
    display: flex;
    align-items: center;
    cursor: pointer;
}

.custom-switch-input {
    width: 44px;
    height: 24px;
    appearance: none;
    background: #cbd5e0;
    border-radius: 12px;
    position: relative;
    outline: none;
    transition: background 0.3s;
    cursor: pointer;
}

.custom-switch-input:checked {
    background: #1d4ed8;
}

.custom-switch-input::before {
    content: "";
    position: absolute;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    top: 2px;
    left: 2px;
    transition: transform 0.3s;
}

.custom-switch-input:checked::before {
    transform: translateX(20px);
}

.custom-switch-indicator {
    display: none;
}
</style>
