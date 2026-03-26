import { onMounted, onUnmounted, ref } from 'vue';
import { router } from '@inertiajs/vue3';

export function useRealtimeDashboard(channelName, propsToRefresh = []) {
    const isLive = ref(false);
    const lastUpdated = ref(null);
    let debounceTimer = null;

    const refreshDashboard = () => {
        // Prevent multiple reloads if several events fire quickly
        if (debounceTimer) clearTimeout(debounceTimer);
        
        debounceTimer = setTimeout(() => {
            router.reload({
                only: propsToRefresh,
                preserveScroll: true,
                preserveState: true,
                onSuccess: () => {
                    lastUpdated.value = new Date();
                }
            });
        }, 1000);
    };

    onMounted(() => {
        if (window.Echo) {
            window.Echo.private(channelName)
                .listen('.ticket.updated', (e) => {
                    refreshDashboard();
                });
            isLive.value = true;
        }
    });

    onUnmounted(() => {
        if (window.Echo) {
            window.Echo.leave(channelName);
        }
    });

    return {
        isLive,
        lastUpdated
    };
}
