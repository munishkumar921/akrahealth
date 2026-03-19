import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

/**
 * Subscription-plan based permission checks
 */
export function useSubscriptionPermissions() {
    const page = usePage();

    const subscriptionPermissions = computed(() => {
        return page.props.subscriptionPermissions || [];
    });

    const hasActiveSubscription = computed(() => {
        const subscription = page.props.subscription;
        if (!subscription) return false;
        if (subscription.status !== 'active') return false;
        if (!subscription.end_date) return true;
        return new Date(subscription.end_date) > new Date();
    });

    const hasPermission = (permission) => {
        const perms = subscriptionPermissions.value;
        if (!perms || perms.length === 0) return false;

        if (perms.includes('*')) return true;
        if (perms.includes(permission)) return true;

        const parts = permission.split('.');
        for (let i = parts.length; i > 0; i--) {
            const wildcard = parts.slice(0, i).join('.') + '.*';
            if (perms.includes(wildcard)) {
                return true;
            }
        }

        if (permission.includes('.')) {
            const routeParts = permission.split('.');
            if (routeParts.length > 2) {
                const base = routeParts.slice(0, -1).join('.');
                if (perms.includes(base)) {
                    return true;
                }
            }
        }

        return false;
    };

    const hasAnyPermission = (list) =>
        Array.isArray(list) && list.some(p => hasPermission(p));

    const hasAllPermissions = (list) =>
        Array.isArray(list) && list.every(p => hasPermission(p));

    return {
        subscriptionPermissions,
        hasPermission,
        hasAnyPermission,
        hasAllPermissions,
        hasActiveSubscription,
    };
}


