// Composable for centralized avatar logic
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

export function useAvatar() {
    const page = usePage();
    
    const authUser = computed(() => page.props.auth.user);
    const switchedDoctor = computed(() => {
        return page.props.switched_role === 'Doctor' ? page.props.switched_doctor : null;
    });
    
    const profilePhotoUrl = computed(() => {
        // If in Doctor mode and switched_doctor has a profile photo, use it
        if (switchedDoctor.value?.profile_photo_url) {
            return switchedDoctor.value.profile_photo_url;
        }
        // Fall back to user's profile photo
        return authUser.value?.profile_photo_url;
    });
    
    const userSex = computed(() => {
        // If in Doctor mode, use switched doctor's sex
        if (switchedDoctor.value?.sex) {
            return switchedDoctor.value.sex;
        }
        // Fall back to user's sex
        return authUser.value?.sex;
    });
    
    const showDoctorAvatar = computed(() => {
        return !profilePhotoUrl.value && 
               (authUser.value?.role_id === 4 || authUser.value?.role_id === 5);
    });
    
    const avatarProps = computed(() => {
        if (profilePhotoUrl.value) {
            return { image: profilePhotoUrl.value, alt: 'user' };
        }
        
        if (showDoctorAvatar.value) {
            if (userSex.value === 'Male') {
                return { image: '/images/doctor_m_avtar.svg', alt: 'user' };
            } else if (userSex.value === 'Female') {
                return { image: '/images/doctor_f_avtar.svg', alt: 'user' };
            }
        }
        
        // Fallback for other roles
        if (authUser.value?.role_id === 4) {
            if (authUser.value?.sex === 'Male') {
                return { image: '/images/doctor_m_avtar.svg', alt: 'user' };
            } else if (authUser.value?.sex === 'Female') {
                return { image: '/images/doctor_f_avtar.svg', alt: 'user' };
            }
        }
        
        if (authUser.value?.role_id === 5) {
            if (authUser.value?.sex === 'Male') {
                return { image: '/images/doctor_m_avtar.svg', alt: 'user' };
            } else if (authUser.value?.sex === 'Female') {
                return { image: '/images/doctor_f_avtar.svg', alt: 'user' };
            }
        }
        
        return { image: '/images/user_default_avtar.svg', alt: 'user' };
    });
    
    return {
        authUser,
        switchedDoctor,
        profilePhotoUrl,
        userSex,
        showDoctorAvatar,
        avatarProps
    };
}
