<script setup>
import { useForm } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    row: {}
});
const form = useForm({
    slug: '',
    name: '',
    email: '',
    phone: '',
});
const submit = () => {
    form.post(route('demo-request.store'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <section id="appointment" class="appointment section-bg ">
        <div class="container aos-init aos-animate " id="second">
            <div class="section-title">
                <h2>Book a Demo With Us</h2>
                <p>Start using the Akrahealth app today: Book a free demo here as well, OR schedule a completely free 45-minute consultation call to discuss and learn more about our healthcare app with the services on offer.
                </p>
            </div>
            <form @submit.prevent="submit" class="php-email-form">
                <div class="row book-a-demo justify-content-center">
                    <div class="col-md-8 book-a-demo">
                        <div class="form-group">
                            <input type="text" v-model="form.name" class="form-control" placeholder="Name" required />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>
                    <div class="row">
                        <div class="col-md-6 form-group mt-3 mt-md-0">
                            <input type="email" v-model="form.email" class="form-control" placeholder="Email" required />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>
                        <div class="col-md-6 form-group mt-3 mt-md-0">
                            <input type="text" v-model="form.phone" class="form-control" placeholder="Phone" pattern="[0-9]{0,10}" title="10 digit" required />
                            <InputError class="mt-2" :message="form.errors.phone" />
                        </div>
                    </div>
                    </div>
                </div>
                <div class="text-center"><button type="submit" class="btn btn-primary">Submit</button></div>
            </form>
        </div>
    </section>
</template>
