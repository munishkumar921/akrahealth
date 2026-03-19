<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import DatePicker from '@/Components/Common/Input/BaseDatePicker.vue';
import { provide } from 'vue';
const props = defineProps({
    row: {},
    alert:Array,
    doctors:Array,
});
const form = useForm({
    slug: '',
    alert: '',
    provider_alert:'',
    message_alert: '',
    description: '',
    date_active: '',
});
const submit = () => {

form.post(route('doctor.alerts.store'), {
    onSuccess: () => {
        $('.close-modal').click();
        form.reset();
    },
});
};

const update = (alert) => {
   
    form.type = alert?.type;
    if(alert == null){
        form.type = "";
    }

    form.slug = alert?.slug;
    form.alert = alert?.alert;
    form.provider_alert = alert?.provider_alert;
    form.message_alert =alert?.message_sent??'';
    form.description = alert?.description;
    form.date_active = alert?.date_active;
}

defineExpose({
    update
})

</script>
<template>
    <div class="modal fade bd-alert-modal-lg" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">New Alerts</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <form @submit.prevent="submit">
                    <div class="modal-body">

                        <div class="iq-card-body">

                            <div class="row">
                                <div class="col">
                                    <label>Alert</label>
                                    <input v-model="form.alert" type="text" class="form-control" id="Alert" placeholder="Alert" required />
                                    <InputError class="mt-2" :message="form.errors.alert" />
                                </div>
                                <div class="col">
                                    <label>User or Provider to Alert</label>
                                    <select v-model="form.provider_alert" class="form-control">
                                        <option disabled value="">Select User or Provider</option>
                                        <option v-for="(doctor, index) in $page.props.doctors" :key="index" :value="doctor.id">
                                            {{ doctor?.first_name }} {{ doctor?.last_name }}
                                        </option>
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.provider_alert" />
                                    </div>
                            </div>
                            <div class="row mt-3">
                                <div class="col">
                                    <label>Description</label>
                                    <textarea rows="5"  class="form-control" v-model="form.description"></textarea>
                                    <InputError class="mt-2" :message="form.errors.description" />
                                </div>
                                </div>
                           
                            <div class="row mt-3">
                                <div class="col">
                                    <label>Message to Patient about Alert</label>
                                    <select class="form-control" id="message_alert" v-model="form.message_alert">
                                    <option disabled value="">Message Send</option> <!-- Acts as a placeholder -->
                                    <option value="0">No</option>
                                    <option value="1">Yes</option>
                                  
                                    </select>
                                    <InputError class="mt-2" :message="form.errors.message_alert" />
                                    </div>
                                <div class="col">
                          
                                    <label>Date InActive</label>
                                    <DatePicker v-model="form.date_active"  type="date"  placeholder="Date Active" required/>
                                    <InputError class="mt-2" :message="form.errors.date_active" />
                                </div>
                                </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Save Alert</button>
                        <button type="button" class="btn btn-danger close-modal" data-dismiss="modal">Close</button>

                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
