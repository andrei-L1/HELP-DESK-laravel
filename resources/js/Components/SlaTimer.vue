<script setup>
import { ref, onMounted, onUnmounted, computed, watch } from 'vue';
import { differenceInSeconds, isPast, formatDuration, intervalToDuration } from 'date-fns';

const props = defineProps({
    dueAt: {
        type: String,
        required: true
    },
    isBreached: {
        type: Boolean,
        default: false
    },
    status: {
        type: String,
        default: 'Open'
    }
});

const now = ref(new Date());
let timer = null;

const remainingSeconds = computed(() => {
    if (!props.dueAt) return 0;
    const due = new Date(props.dueAt);
    return Math.max(0, differenceInSeconds(due, now.value));
});

const isBreachedNow = computed(() => {
    if (props.isBreached) return true;
    if (!props.dueAt) return false;
    return isPast(new Date(props.dueAt));
});

const timeDisplay = computed(() => {
    if (isBreachedNow.value) return 'Breached';
    
    const duration = intervalToDuration({
        start: now.value,
        end: new Date(props.dueAt)
    });

    const parts = [];
    if (duration.days) parts.push(`${duration.days}d`);
    if (duration.hours || duration.days) parts.push(`${duration.hours}`.padStart(2, '0') + 'h');
    parts.push(`${duration.minutes}`.padStart(2, '0') + 'm');
    parts.push(`${duration.seconds}`.padStart(2, '0') + 's');

    return parts.join(' ');
});

const timerColorClass = computed(() => {
    if (isBreachedNow.value) return 'text-rose-600 bg-rose-50 border-rose-200';
    
    const seconds = remainingSeconds.value;
    if (seconds < 3600) return 'text-amber-600 bg-amber-50 border-amber-200 animate-pulse-slow'; // 1 hour left
    return 'text-emerald-600 bg-emerald-50 border-emerald-200';
});

const statusText = computed(() => {
    if (isBreachedNow.value) return 'SLA Breached';
    if (remainingSeconds.value < 3600) return 'Urgent: Deadline Approaching';
    return 'On Track';
});

onMounted(() => {
    timer = setInterval(() => {
        now.value = new Date();
    }, 1000);
});

onUnmounted(() => {
    if (timer) clearInterval(timer);
});

</script>

<template>
    <div v-if="dueAt && status !== 'Resolved' && status !== 'Closed'" 
        class="flex flex-col gap-2 p-4 rounded-2xl border transition-all duration-500 shadow-sm"
        :class="timerColorClass"
    >
        <div class="flex items-center justify-between">
            <span class="text-[10px] font-black uppercase tracking-widest opacity-70">{{ statusText }}</span>
            <div class="h-2 w-2 rounded-full" :class="isBreachedNow ? 'bg-rose-500 shadow-[0_0_8px_rgba(244,63,94,0.6)]' : (remainingSeconds < 3600 ? 'bg-amber-500 animate-ping' : 'bg-emerald-500')"></div>
        </div>
        
        <div class="flex items-baseline gap-1">
            <span class="text-2xl font-black tracking-tighter tabular-nums">{{ timeDisplay }}</span>
            <span v-if="!isBreachedNow" class="text-[10px] font-bold opacity-60 uppercase">remaining</span>
        </div>

        <div class="w-full bg-black/5 rounded-full h-1 overflow-hidden">
            <div 
                v-if="!isBreachedNow"
                class="h-full transition-all duration-1000 ease-linear"
                :class="remainingSeconds < 3600 ? 'bg-amber-500' : 'bg-emerald-500'"
                :style="{ width: '100%' }"
            ></div>
            <div v-else class="h-full bg-rose-500 w-full"></div>
        </div>
    </div>
</template>

<style scoped>
.animate-pulse-slow {
    animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.8; }
}

.tabular-nums {
    font-variant-numeric: tabular-nums;
}
</style>
