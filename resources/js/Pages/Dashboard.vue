<script setup>
import { Head } from '@inertiajs/vue3';
import AdminLayout from '../Layouts/AdminLayout.vue';
import LineChart from '../Components/LineChart.vue';

defineOptions({ layout: AdminLayout });

defineProps({
    headline: { type: Array, required: true },
    series: { type: Object, required: true },
    topExercises: { type: Array, required: true },
    recentWorkouts: { type: Array, required: true },
    recentMembers: { type: Array, required: true },
});

const kg = (value) => `${new Intl.NumberFormat().format(Math.round(value))} kg`;
</script>

<template>
    <Head title="Dashboard" />

    <div class="mb-6">
        <h1 class="text-[19px] font-semibold tracking-tight text-white">Dashboard</h1>
        <p class="mt-1 text-[12px] text-zinc-500">Everything members logged, last 14 days.</p>
    </div>

    <div class="grid grid-cols-1 gap-px border border-[#26262b] bg-[#26262b] sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="stat in headline" :key="stat.label" class="bg-[#121214] px-4 py-4">
            <div class="text-[11px] uppercase tracking-[0.12em] text-zinc-500">{{ stat.label }}</div>
            <div class="mt-2 text-[22px] font-semibold leading-none tracking-tight text-white">{{ stat.value }}</div>
            <div class="mt-2 text-[11px] text-zinc-500">{{ stat.hint }}</div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-2">
        <section class="panel px-4 py-4">
            <h2 class="mb-4 text-[13px] font-medium text-zinc-300">Sessions and meals</h2>
            <LineChart
                :labels="series.labels"
                :series="[
                    { name: 'Workouts', data: series.workouts, color: '#c6f24e' },
                    { name: 'Meals', data: series.meals, color: '#71717a' },
                ]"
            />
        </section>

        <section class="panel px-4 py-4">
            <h2 class="mb-4 text-[13px] font-medium text-zinc-300">Volume lifted</h2>
            <LineChart
                :labels="series.labels"
                :series="[{ name: 'kg', data: series.volume, color: '#c6f24e' }]"
            />
        </section>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <section class="panel xl:col-span-1">
            <h2 class="border-b border-[#26262b] px-4 py-3 text-[13px] font-medium text-zinc-300">Top exercises</h2>
            <div v-if="topExercises.length === 0" class="px-4 py-6 text-[12px] text-zinc-500">
                No sets recorded in the last 30 days.
            </div>
            <ul v-else class="divide-y divide-[#26262b]">
                <li v-for="exercise in topExercises" :key="exercise.name" class="flex items-center justify-between px-4 py-2.5">
                    <span class="truncate text-[13px] text-zinc-300">{{ exercise.name }}</span>
                    <span class="ml-3 shrink-0 text-[12px] text-zinc-500">{{ kg(exercise.volume) }}</span>
                </li>
            </ul>
        </section>

        <section class="panel xl:col-span-1">
            <h2 class="border-b border-[#26262b] px-4 py-3 text-[13px] font-medium text-zinc-300">Latest sessions</h2>
            <div v-if="recentWorkouts.length === 0" class="px-4 py-6 text-[12px] text-zinc-500">Nothing logged yet.</div>
            <ul v-else class="divide-y divide-[#26262b]">
                <li v-for="workout in recentWorkouts" :key="workout.id" class="px-4 py-2.5">
                    <div class="flex items-center justify-between">
                        <span class="truncate text-[13px] text-zinc-300">{{ workout.name }}</span>
                        <span class="ml-3 shrink-0 text-[11px] text-zinc-600">{{ workout.when }}</span>
                    </div>
                    <div class="mt-0.5 text-[11px] text-zinc-500">{{ workout.member }} · {{ kg(workout.volume) }}</div>
                </li>
            </ul>
        </section>

        <section class="panel xl:col-span-1">
            <h2 class="border-b border-[#26262b] px-4 py-3 text-[13px] font-medium text-zinc-300">New members</h2>
            <div v-if="recentMembers.length === 0" class="px-4 py-6 text-[12px] text-zinc-500">No members yet.</div>
            <ul v-else class="divide-y divide-[#26262b]">
                <li v-for="member in recentMembers" :key="member.id" class="px-4 py-2.5">
                    <div class="flex items-center justify-between">
                        <span class="truncate text-[13px] text-zinc-300">{{ member.name }}</span>
                        <span class="ml-3 shrink-0 text-[11px] text-zinc-600">{{ member.when }}</span>
                    </div>
                    <div class="mt-0.5 truncate text-[11px] text-zinc-500">{{ member.email }}</div>
                </li>
            </ul>
        </section>
    </div>
</template>
