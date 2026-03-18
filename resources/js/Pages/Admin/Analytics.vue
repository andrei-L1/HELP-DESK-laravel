<script setup>
import AdminNavigation from '@/Components/AdminNavigation.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    overview_stats: {
        type: Object,
        required: true
    },
    volume_data: {
        type: Array,
        required: true
    },
    weekly_total: {
        type: Number,
        required: true
    },
    weekly_growth: {
        type: String,
        required: true
    },
    agent_performance: {
        type: Array,
        required: true
    },
    current_period: {
        type: Number,
        default: 7
    }
});

const changePeriod = (days) => {
    router.get(route('admin.analytics'), { period: days }, { 
        preserveScroll: true,
        preserveState: true
    });
};

const exportData = () => {
    // Generate a simple CSV content
    let csvContent = "data:text/csv;charset=utf-8,Date,Resolution Time,FRT,Tickets\n";
    props.volume_data.forEach(row => {
        csvContent += `${row.day},${props.overview_stats.resolution_time},${props.overview_stats.first_response},${row.count}\n`;
    });

    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `analytics_report_${props.current_period}d.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <Head title="Admin Analytics" />
    <AdminNavigation>
        <template #header-title>
            <div class="flex items-center gap-2">
                <span class="text-xl font-bold text-slate-900 tracking-tight">Analytics</span>
            </div>
        </template>

        <template #breadcrumb>
            <div class="flex items-center gap-2 text-[11px] font-bold text-slate-500">
                <span class="hover:text-slate-700 cursor-pointer">Admin</span>
                <svg class="h-2 w-2 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" /></svg>
                <span class="text-slate-900">Performance Metrics</span>
            </div>
        </template>

        <div class="max-w-[1400px] mx-auto space-y-10 pb-20 pt-4">
            <!-- Analytics Hero -->
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 px-2 stagger-1">
                <div class="space-y-1">
                    <h2 class="text-3xl font-bold text-slate-900 tracking-tight text-balance">Intelligence Dashboard</h2>
                    <p class="text-slate-500 font-medium">Real-time performance analytics and support quality insights.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="flex items-center bg-white border border-slate-200 rounded-xl p-1 shadow-sm">
                        <button 
                            @click="changePeriod(7)"
                            class="px-4 py-1.5 text-xs font-black rounded-lg transition-all"
                            :class="current_period === 7 ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900'"
                        >Last 7D</button>
                        <button 
                            @click="changePeriod(30)"
                            class="px-4 py-1.5 text-xs font-black rounded-lg transition-all"
                            :class="current_period === 30 ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900'"
                        >30D</button>
                        <button 
                            @click="changePeriod(90)"
                            class="px-4 py-1.5 text-xs font-black rounded-lg transition-all"
                            :class="current_period === 90 ? 'bg-slate-900 text-white' : 'text-slate-500 hover:text-slate-900'"
                        >90D</button>
                    </div>
                    <button 
                        @click="exportData"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white text-slate-900 text-sm font-bold border border-slate-200 shadow-sm hover:shadow-md transition-all active:scale-95"
                    >
                         <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                        Export
                    </button>
                </div>
            </div>

            <!-- Enhanced Metrics Grid -->
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-4 px-1 stagger-2">
                <div class="bg-white p-7 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-500 group">
                    <div class="h-10 w-10 bg-slate-900 text-white rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    </div>
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Avg Resolution</p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-3xl font-black text-slate-900">{{ overview_stats.resolution_time }}</p>
                        <span class="text-xs font-bold text-emerald-500">-12m</span>
                    </div>
                </div>
                
                <div class="bg-white p-7 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-500 group">
                    <div class="h-10 w-10 bg-indigo-600 text-white rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" /></svg>
                    </div>
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">First Response</p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-3xl font-black text-slate-900">{{ overview_stats.first_response }}</p>
                        <span class="text-xs font-bold text-rose-500">+4m</span>
                    </div>
                </div>

                <div class="bg-white p-7 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-500 group">
                    <div class="h-10 w-10 bg-amber-500 text-white rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.921-.755 1.688-1.54 1.118l-3.976-2.888a1 1 0 00-1.175 0l-3.976 2.888c-.784.57-1.838-.197-1.539-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" /></svg>
                    </div>
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Satisfaction</p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-3xl font-black text-slate-900">{{ overview_stats.csat }}</p>
                        <span class="text-xs font-bold text-emerald-500">{{ overview_stats.satisfaction_trend }}</span>
                    </div>
                </div>

                <div class="bg-white p-7 rounded-3xl border border-slate-200 shadow-sm hover:shadow-xl hover:shadow-slate-200/50 transition-all duration-500 group">
                    <div class="h-10 w-10 bg-emerald-600 text-white rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" /></svg>
                    </div>
                    <p class="text-[11px] font-black text-slate-400 uppercase tracking-widest mb-1">Weekly Volume</p>
                    <div class="flex items-baseline gap-2">
                        <p class="text-3xl font-black text-slate-900">{{ weekly_total }}</p>
                        <span class="text-xs font-bold" :class="weekly_growth.startsWith('+') ? 'text-emerald-500' : 'text-rose-500'">{{ weekly_growth }}</span>
                    </div>
                </div>
            </div>

            <!-- Volume Chart & Distribution -->
            <div class="grid gap-10 lg:grid-cols-12 px-1 stagger-3">
                <div class="lg:col-span-8 bg-white rounded-[2rem] border border-slate-200 p-8 shadow-sm">
                    <div class="flex items-center justify-between mb-10">
                        <div>
                            <h3 class="text-lg font-black text-slate-900 tracking-tight">Ticket Volume</h3>
                            <p class="text-sm font-medium text-slate-400">Comparison across the last 7 days</p>
                        </div>
                    </div>
                    
                    <div class="flex items-end justify-between h-48 gap-4 pb-2">
                        <div v-for="data in volume_data" :key="data.day" class="flex-1 flex flex-col items-center group">
                            <div 
                                class="w-full bg-slate-100 rounded-t-xl hover:bg-slate-900 transition-all duration-500 relative cursor-pointer" 
                                :style="{ height: (Math.max(data.count, 2) / (Math.max(...volume_data.map(d => d.count), 1) * 1.2) * 100) + '%' }"
                            >
                                <div class="absolute -top-10 left-1/2 -translate-x-1/2 bg-slate-900 text-white text-[10px] font-black px-2 py-1 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                    {{ data.count }} Tickets
                                </div>
                            </div>
                            <span class="mt-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">{{ data.day }}</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-4 space-y-6">
                    <div class="bg-slate-900 rounded-[2rem] p-8 text-white shadow-2xl relative overflow-hidden h-full">
                        <div class="absolute top-0 right-0 p-8 opacity-10">
                            <svg class="h-32 w-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/></svg>
                        </div>
                        <h3 class="text-xl font-black mb-2 relative z-10">Efficiency Goal</h3>
                        <p class="text-slate-400 text-sm font-medium mb-10 relative z-10">You're currently <span class="text-emerald-400">8% ahead</span> of your monthly resolution target.</p>
                        
                        <div class="relative pt-1">
                            <div class="flex mb-2 items-center justify-between">
                                <div><span class="text-xs font-black inline-block py-1 px-2 uppercase rounded-full bg-emerald-500 text-white">Progress</span></div>
                                <div class="text-right"><span class="text-sm font-black inline-block text-emerald-400">92%</span></div>
                            </div>
                            <div class="overflow-hidden h-2 mb-4 text-xs flex rounded-full bg-white/10">
                                <div style="width:92%" class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-emerald-500 transition-all duration-1000"></div>
                            </div>
                        </div>

                        <button class="w-full mt-6 py-3 bg-white/5 border border-white/10 rounded-2xl text-xs font-black uppercase tracking-widest hover:bg-white/10 transition-all">View Efficiency Details</button>
                    </div>
                </div>
            </div>

            <!-- Agent Performance Table -->
            <div class="bg-white rounded-[2rem] border border-slate-200 overflow-hidden shadow-sm stagger-4">
                <div class="px-8 py-6 border-b border-slate-100 flex items-center justify-between">
                    <h3 class="text-lg font-black text-slate-900 tracking-tight">Agent Performance</h3>
                    <button class="text-xs font-black text-slate-400 hover:text-slate-900 transition-colors uppercase tracking-widest">Full Member List</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead>
                            <tr class="bg-slate-50/50">
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Member</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Tickets Resolved</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Avg Response</th>
                                <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Rating</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            <tr v-for="agent in agent_performance" :key="agent.name" class="hover:bg-slate-50/50 transition-colors cursor-pointer group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="h-10 w-10 rounded-xl bg-slate-100 flex items-center justify-center font-black text-xs text-slate-600 transition-transform group-hover:scale-110">{{ agent.avatar }}</div>
                                        <span class="text-sm font-bold text-slate-900 group-hover:text-slate-700">{{ agent.name }}</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="text-sm font-black text-slate-900">{{ agent.resolved }}</span>
                                </td>
                                <td class="px-8 py-5 text-center">
                                    <span class="text-sm font-bold text-slate-600">{{ agent.avg_time }}</span>
                                </td>
                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <svg class="h-4 w-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" /></svg>
                                        <span class="text-sm font-black text-slate-900">{{ agent.rating }}</span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminNavigation>
</template>

<style scoped>
.stagger-1 { animation: slideUp 0.6s ease both 0.1s; }
.stagger-2 { animation: slideUp 0.6s ease both 0.2s; }
.stagger-3 { animation: slideUp 0.6s ease both 0.3s; }
.stagger-4 { animation: slideUp 0.6s ease both 0.4s; }

@keyframes slideUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>
