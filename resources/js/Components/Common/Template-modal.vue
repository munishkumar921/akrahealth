<script setup>
import { useForm } from "@inertiajs/vue3";
import InputError from "@/Components/InputError.vue";
import DatePicker from "@/Components/DatePicker.vue";
const props = defineProps({
    row: {},
    allergy: Array,
});
const form = useForm({
    slug: "",
    allergies_medicine: "",
    allergies_reaction: "",
    allergies_severity: "",
    notes: "",
    date_active: "",
});
const submit = () => {
    form.post(route("doctor.allergies.store"), {
        onSuccess: () => {
            $(".close-modal").click();
            form.reset();
        },
    });
};

const update = (allergy) => {
    form.type = allergy?.type;
    if (allergy == null) {
        form.type = "";
    }

    form.slug = allergy?.slug;
    form.allergies_medicine = allergy?.allergies_medicine;
    form.allergies_reaction = allergy?.allergies_reaction;
    form.allergies_severity = allergy?.allergies_severity;
    form.notes = allergy?.notes;
    form.date_active = allergy?.date_active;
};

defineExpose({
    update,
});
</script>
<template>
    <div
        class="modal fade bd-allergies-modal-lg"
        tabindex="-1"
        role="dialog"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New allergies</h5>
                    <button
                        type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close"
                    >
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form @submit.prevent="submit">
                    <div class="modal-body">
                        <div class="iq-card-body">
                            <div class="row">
                                <div class="col">
                                    <label>Substance or Medication</label>
                                    <input
                                        v-model="form.allergies_medicine"
                                        type="text"
                                        class="form-control"
                                        id="allergies_med"
                                        placeholder="Allergies Medicine"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="
                                            form.errors.allergies_medicine
                                        "
                                    />
                                </div>
                                <div class="col">
                                    <label>Reaction</label>
                                    <input
                                        v-model="form.allergies_reaction"
                                        type="text"
                                        class="form-control"
                                        placeholder="Allergies Reaction"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="
                                            form.errors.allergies_reaction
                                        "
                                    />
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col">
                                    <label>Severity</label>
                                    <select
                                        class="form-control"
                                        id="severity"
                                        v-model="form.allergies_severity"
                                    >
                                        <option value="mild">Mild</option>
                                        <option value="moderate">
                                            Moderate
                                        </option>
                                        <option value="severe">Severe</option>
                                    </select>
                                    <InputError
                                        class="mt-2"
                                        :message="
                                            form.errors.allergies_severity
                                        "
                                    />
                                </div>
                                <div class="col">
                                    <label>Date Active</label>
                                    <DatePicker
                                        v-model="form.date_active"
                                         placeholder="Date Active"
                                        required
                                    />
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.date_active"
                                    />
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col">
                                    <label>Notes</label>
                                    <textarea
                                        rows="5"
                                        class="form-control"
                                        v-model="form.notes"
                                    ></textarea>
                                    <InputError
                                        class="mt-2"
                                        :message="form.errors.notes"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button
                            type="button"
                            class="btn btn-danger close-modal"
                            data-dismiss="modal"
                        >
                            Close
                        </button>
                        <button type="submit" class="btn btn-primary">
                            Save Medication
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
