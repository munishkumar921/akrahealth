<script setup>
import { ref, computed } from "vue";
import Avatar from "../Common/Avatar.vue";
import "./header.css";
import { profileOptions as baseProfileOptions } from "./options";
import { router, usePage } from "@inertiajs/vue3";
import ResetPasswordModal from "./ResetPasswordModal.vue";
import Modal from "../Common/Modal.vue";
import { useAvatar } from "@/Composables/useAvatar";

// Use centralized avatar composable
const { 
  authUser, 
  switchedDoctor, 
  profilePhotoUrl, 
  userSex, 
  showDoctorAvatar,
  avatarProps
} = useAvatar();

const page = usePage();
const role = computed(() => {
  return page.props.switched_role || authUser.value?.roles?.[0]?.name;
});

const profileOptions = computed(() => {
  const options = [...baseProfileOptions];
  if (role.value === 'Admin') {
    options.push({
      label: "Branch",
      svg: `
            <svg xmlns="http://www.w3.org/2000/svg" width="1.2em" height="1.2em" viewBox="0 0 24 24">
                <path fill="currentColor" d="M10 1v5.5l3 3l-3 3V14a5.006 5.006 0 0 1-4.5 5A5.006 5.006 0 0 1 3 14V7a3 3 0 0 1 3-3h4m0 2H7a1 1 0 0 0-1 1v7a5.006 5.006 0 0 0 4.5 5A5.006 5.006 0 0 0 15 17v-4.5l-3 3l-3-3V19a3 3 0 0 1-3-3V7a1 1 0 0 0-1-1M17 7v7h2V7a2 2 0 0 0-2-2M7 1a1 1 0 0 1 1 1v3h2V2a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1v3h2V2a1 1 0 0 1 1-1h1"/>
            </svg>
        `,
      path: "",
      onClick: () => {
        router.get(route('admin.branches'));
      },
    });
  }
  return options;
});

const logout = () => {
    router.post(route("logout"), {}, {
        preserveState: false,
        preserveScroll: false,
        replace: true,
    });
};

const showPasswordResetModal = ref(false);

const doProfileAction = (icon) => {
    if (icon === "Sign out") {
        logout();
        return;
    }
    if (icon === "Profile") {
        gotToProfile();
        return;
    }
    if (icon === "Change Password") {
        openPasswordResetModal();
        return;
    }
    if (icon === "Messages") {
        goToChat();
        return;
    }
    if (icon === "Branch") {
        router.get(route('admin.branches'));
        return;
    }
};

const goToChat = () => {
    router.get(route("messages.index"));
};

const gotToProfile = () => {
    router.get(route("doctor.profile", authUser.value?.id));
};

const openPasswordResetModal = () => {
    showPasswordResetModal.value = true;
};

const closePasswordResetModal = () => {
    showPasswordResetModal.value = false;
};
</script>

<template>
    <ul class="navbar-list">
        <li>
            <button
                type="button"
                class="search-toggle iq-waves-effect d-flex align-items-center bg-primary rounded border-0"
                aria-label="Toggle profile menu"
            >
                <Avatar
                    :image="avatarProps.image"
                    :alt="avatarProps.alt"
                />
            </button>
            <div class="iq-sub-dropdown iq-user-dropdown">
                <div class="iq-card shadow-none m-0">
                    <div class="iq-card-body p-0">
                        <div class="bg-primary p-3">
                            <h5 class="mb-0 text-white line-height">
                                {{ $page.props.auth.user?.name }}
                            </h5>
                            <small class="text-white-50">{{ role }}</small>
                        </div>
                         <div
                            v-for="option in profileOptions"
                            :key="option.id"
                            class="iq-sub-card iq-bg-primary-hover cursor-pointer"
                            :style="{ animationDelay: option.delay + 'ms' }"
                            @click="doProfileAction(option.label)"
                        >
                             <div class="media align-items-center">
                                <div class="rounded iq-card-icon bg-light">
                                    <i v-html="option.svg"></i>
                                </div>
                                <div class="media-body ml-3">
                                     <h6 class="mb-0">{{ option.label }}</h6>
                                </div>
                            </div>
                        </div>
                        <div class="d-inline-block w-100 text-center p-2">
                            <button
                                class="btn btn-primary dark-btn-primary"
                                @click="logout()"
                                role="button"
                            >
                                Sign out
                                <i class="ri-login-box-line ml-2"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    </ul>
    <Modal
        :isOpen="showPasswordResetModal"
        title="Change Password"
        @close="closePasswordResetModal"
        size="lg"
    >
        <ResetPasswordModal @close="closePasswordResetModal" />
    </Modal>
</template>
