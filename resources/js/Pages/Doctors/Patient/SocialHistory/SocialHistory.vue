<script setup>
import { ref, computed } from "vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";
import MentalHealthModal from "./Partials/MentalHealth/MentalHealthModal.vue";
import LifestyleModal from "./Partials/Lifestyle/LifestyleModal.vue";
import HabitModal from "./Partials/Habits/HabitsModal.vue";
import Modal from "@/Components/Common/Modal.vue";

const props = defineProps({
	socialHistory: Object,
});
const isMentalHealthModalOpen = ref(false);
const isLifestyleOpen = ref(false);
const isHobitsOpen = ref(false);

const MentalHealthModalOpen = () => {
	isMentalHealthModalOpen.value = true;
}

const MentalHealthcloseModal = () => {
	isMentalHealthModalOpen.value = false;
};

const Lifestylemodal = () => {
	isLifestyleOpen.value = true;

}
const closeLifestyleModal = () => {
	isLifestyleOpen.value = false;
}

const habitModal = () => {

	isHobitsOpen.value = true;
}
const HabitModalClose = () => {
	isHobitsOpen.value = false;
}


const parseMentalHealthNotes = (notes) => {
	if (!notes || !notes.trim()) {
		return {
			psychological_history: "",
			devolepmental_history: "",
			past_medication_trials: ""
		};
	}

	const parts = notes.split(' | ');
	return {
		psychological_history: parts[0] || "",
		devolepmental_history: parts[1] || "",
		past_medication_trials: parts[2] || ""
	};
};

const existingData = computed(() => parseMentalHealthNotes(props.socialHistory?.mental_health_notes));

</script>
<template>
<AuthLayout title="Social History" description="Manage patient social history and lifestyle information" heading="Social History">

		<div class="iq-card mb-4">
			<div class="iq-card-header d-flex justify-content-between align-items-center bg-primary text-white">
				<h5 class="mb-0 text-white">Lifestyle</h5>
				<button class="btn btn-light btn-sm" @click="Lifestylemodal">
					Edit
				</button>
			</div>
			<div class="iq-card-body" style="border: 1.5px solid var(--iq-light-border);">
				<div class="row g-3">
					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4  "><strong>Social History </strong></div>
							<div class="col-7 col-sm-8">{{ socialHistory?.social_history }}</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4  "><strong>Sexually Active</strong></div>
							<div class="col-7 col-sm-8">{{ socialHistory?.sexually_active ==true?'Yes':'No' }}</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4">
								<strong>Diet</strong>
							</div>
							<div class="col-7 col-sm-8">{{ socialHistory?.diet }}</div>

						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4">
								<strong>Physical Activity:</strong>
							</div>
							<div class="col-7 col-sm-8">{{ socialHistory?.physical_activity }}</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4">
								<strong>Employment:</strong>
							</div>
							<div class="col-7 col-sm-8">{{ socialHistory?.employment }}</div>

						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="iq-card mb-4">
			<div class="iq-card-header d-flex justify-content-between align-items-center bg-primary text-white">
				<h5 class="mb-0 text-white">Habits</h5>
				<button class="btn btn-light btn-sm" @click="habitModal">
					Edit
				</button>
			</div>
			<div class="iq-card-body" style="border: 1.5px solid var(--iq-light-border);">
				<div class="row g-3">
					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4  "><strong>Alcohol Use </strong></div>
							<div class="col-7 col-sm-8">{{ socialHistory?.alcohol_use }}</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4  "><strong>Tobacco Use</strong></div>
							<div class="col-7 col-sm-8">{{ socialHistory?.tobacco_use ==true ? 'Yes' : 'No' }}</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4  ">
								<strong>
									Tobacco Use Details </strong>
							</div>
							<div class="col-7 col-sm-8">{{ socialHistory?.tobacco_use_details }}</div>

						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4  ">
								<strong>Illicit Drug Use:</strong>
							</div>
							<div class="col-7 col-sm-8">{{ socialHistory?.drug_use }}</div>

						</div>
					</div>

				</div>
			</div>
		</div>
		<div class="iq-card mb-4">
			<div class="iq-card-header d-flex justify-content-between align-items-center bg-primary text-white">
				<h5 class="mb-0 text-white">Mental Health</h5>
				<button class="btn btn-light btn-sm" @click="MentalHealthModalOpen">
					Edit
				</button>
			</div>
			<div class="iq-card-body" style="border: 1.5px solid var(--iq-light-border);">
				<div class="row g-3">
					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4"><strong>Psychosocial History </strong></div>
							<div class="col-7 col-sm-8">{{ existingData?.psychological_history }}</div>
						</div>
					</div>

					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4  "><strong>Developmental History</strong></div>
							<div class="col-7 col-sm-8">{{ existingData?.devolepmental_history }}</div>
						</div>
					</div>
					<div class="col-md-6">
						<div class="row">
							<div class="col-5 col-sm-4  ">
								<strong>
									Past Medication Trials
								</strong>
							</div>
							<div class="col-7 col-sm-8">{{ existingData?.past_medication_trials }}</div>

						</div>
					</div>
				</div>
			</div>
		</div>
		<Modal :isOpen="isMentalHealthModalOpen" title="Edit Mental Health" @close="MentalHealthcloseModal" size="lg">
			<MentalHealthModal @close="MentalHealthcloseModal" :socialHistory="socialHistory" />
		</Modal>
		<Modal :isOpen="isLifestyleOpen" title="Edit Lifestyle" @close="closeLifestyleModal" size="lg">
			<LifestyleModal @close="closeLifestyleModal"  :socialHistory="socialHistory" />
		</Modal>
		<Modal :isOpen="isHobitsOpen" title="Edit Habits" @close="HabitModalClose" size="lg">
			<HabitModal @close="HabitModalClose"  :socialHistory="socialHistory" />
		</Modal>
	</AuthLayout>
</template>
