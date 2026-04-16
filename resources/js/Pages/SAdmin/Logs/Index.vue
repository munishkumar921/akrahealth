<script setup>
import AuthLayout from '@/Layouts/AuthLayout.vue';

defineProps({
    logViewerUrl: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <AuthLayout title="Laravel Logs" description="Review application logs from the Super Admin panel">
        <div class="log-view-shell">
            <div class="log-view-frame-card">
                <iframe
                    :src="logViewerUrl"
                    title="Laravel Log Viewer"
                    class="log-view-frame"
                />
            </div>
        </div>
    </AuthLayout>
</template>

<style scoped>
.log-view-shell {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.log-view-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    padding: 1.5rem 1.75rem;
    border: 1px solid #dbeafe;
    border-radius: 1.5rem;
    background: linear-gradient(135deg, #eff6ff 0%, #ffffff 55%, #f8fafc 100%);
}

.log-view-title {
    margin: 0;
    font-size: 2rem;
    font-weight: 700;
    color: #0f172a;
}

.log-view-subtitle {
    margin: 0.5rem 0 0;
    max-width: 48rem;
    color: #64748b;
    font-size: 1rem;
}

.log-view-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0.75rem 1rem;
    border-radius: 0.9rem;
    border: 1px solid #93c5fd;
    background: #ffffff;
    color: #0f6dcf;
    font-weight: 600;
    white-space: nowrap;
    text-decoration: none;
}

.log-view-frame-card {
    overflow: hidden;
    border: 1px solid #e2e8f0;
    border-radius: 1.5rem;
    background: #ffffff;
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.08);
}

.log-view-frame {
    display: block;
    width: 100%;
    min-height: 78vh;
    border: 0;
    background: #ffffff;
}

@media (max-width: 768px) {
    .log-view-header {
        flex-direction: column;
    }

    .log-view-title {
        font-size: 1.6rem;
    }

    .log-view-link {
        width: 100%;
    }
}
</style>
