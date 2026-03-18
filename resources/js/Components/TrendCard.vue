<script setup>
import { computed } from 'vue';

const props = defineProps({
    label: { type: String, required: true },
    value: { type: [Number, String], required: true },
    trend: { type: Array, default: () => [10, 25, 45, 30, 55, 70, 85] },
    color: { type: String, default: 'slate' },
    percentage: { type: String, default: '+12%' },
});

// Generate SVG Path for the trend line
const sparklinePath = computed(() => {
    const width = 140;
    const height = 40;
    const max = Math.max(...props.trend);
    const min = Math.min(...props.trend);
    const range = max - min;
    const step = width / (props.trend.length - 1);

    return props.trend.map((val, i) => {
        const x = i * step;
        const y = height - ((val - min) / range) * height;
        return `${i === 0 ? 'M' : 'L'} ${x} ${y}`;
    }).join(' ');
});

const colorClasses = {
    slate: 'stroke-slate-900 fill-slate-900/10',
    rose: 'stroke-rose-500 fill-rose-500/10',
    emerald: 'stroke-emerald-500 fill-emerald-500/10',
    violet: 'stroke-violet-500 fill-violet-500/10',
    blue: 'stroke-blue-500 fill-blue-500/10',
};
</script>

<template>
    <div class="group relative p-8 bg-white rounded-3xl border border-slate-300/40 shadow-sm shadow-slate-200/50 hover:shadow-2xl hover:shadow-slate-200/40 transition-all duration-500 overflow-hidden">
        <!-- Floating Design Elements -->
        <div class="absolute -right-4 -top-4 h-24 w-24 rounded-full blur-2xl opacity-20 pointer-events-none transition-transform group-hover:scale-110" :class="[
            color === 'slate' ? 'bg-slate-900' : 
            color === 'rose' ? 'bg-rose-500' : 
            color === 'emerald' ? 'bg-emerald-500' : 
            'bg-violet-500'
        ]"></div>

        <div class="relative flex flex-col h-full">
            <div class="flex items-center justify-between mb-8">
                <span class="text-[10px] font-black text-slate-500 uppercase tracking-[0.2em] leading-none">{{ label }}</span>
                <div class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-[10px] font-black border" :class="[
                    color === 'rose' ? 'bg-rose-50 text-rose-600 border-rose-100' : 'bg-emerald-50 text-emerald-600 border-emerald-100'
                ]">
                    <svg v-if="percentage.includes('+')" class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    <svg v-else class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 17h8m0 0v-8m0 8l-8-8-4 4-6-6" /></svg>
                    {{ percentage }}
                </div>
            </div>

            <div class="flex items-end justify-between gap-4 mt-auto">
                <div class="flex flex-col gap-0.5">
                    <span class="text-4xl font-black text-slate-900 tracking-tighter leading-none">{{ value }}</span>
                    <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest mt-1">Growth Indicator</span>
                </div>

                <!-- SVG Sparkline -->
                <div class="w-[140px] h-[40px] flex items-center justify-center shrink-0">
                    <svg viewBox="0 0 140 40" class="w-full h-full overflow-visible drop-shadow-sm">
                        <path 
                            :d="sparklinePath" 
                            fill="none" 
                            stroke-width="3" 
                            stroke-linecap="round" 
                            stroke-linejoin="round"
                            :class="colorClasses[color]"
                            class="transition-all duration-1000"
                        />
                        <path 
                            :d="sparklinePath + ' L 140 60 L 0 60 Z'" 
                            :class="colorClasses[color]"
                            class="stroke-0 opacity-10 transition-all duration-1000"
                        />
                    </svg>
                </div>
            </div>

            <!-- Interaction Pulse -->
            <div class="mt-8 flex items-center gap-2">
                <div class="h-1 flex-1 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full bg-slate-900 rounded-full w-2/3 group-hover:w-full transition-all duration-700"></div>
                </div>
                <div class="h-1.5 w-1.5 rounded-full animate-pulse shrink-0" :class="[
                    color === 'slate' ? 'bg-slate-900' : 
                    color === 'rose' ? 'bg-rose-500' : 
                    color === 'emerald' ? 'bg-emerald-500' : 
                    'bg-violet-500'
                ]"></div>
            </div>
        </div>
    </div>
</template>
