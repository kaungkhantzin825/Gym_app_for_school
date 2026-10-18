<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '../Layouts/AdminLayout.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    headline: { type: Array, required: true },
    topMembers: { type: Array, required: true },
    goals: { type: Array, required: true },
});

const goalTotal = computed(() => props.goals.reduce((sum, goal) => sum + goal.total, 0) || 1);
</script>

<template>
    <Head title="Member report" />

    <div class="mb-6">
        <h1 class="text-[19px] font-semibold tracking-tight text-white">Member report</h1>
        <p class="mt-1 text-[12px] text-zinc-500">Who is training, what they are training for.</p>
    </div>

    <div class="grid grid-cols-1 gap-px border border-[#26262b] bg-[#26262b] sm:grid-cols-2 xl:grid-cols-4">
        <div v-for="stat in headline" :key="stat.label" class="bg-[#121214] px-4 py-4">
            <div class="text-[11px] uppercase tracking-[0.12em] text-zinc-500">{{ stat.label }}</div>
            <div class="mt-2 text-[22px] font-semibold leading-none tracking-tight text-white">{{ stat.value }}</div>
            <div class="mt-2 text-[11px] text-zinc-500">{{ stat.hint }}</div>
        </div>
    </div>

    <div class="mt-4 grid grid-cols-1 gap-4 xl:grid-cols-3">
        <section class="panel xl:col-span-2">
            <h2 class="border-b border-[#26262b] px-4 py-3 text-[13px] font-medium text-zinc-300">Most active members</h2>

            <div v-if="topMembers.length === 0" class="px-4 py-8 text-[12px] text-zinc-500">
                No member activity yet.
            </div>

            <table v-else class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-[#26262b]">
                        <th class="px-4 py-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">Member</th>
                        <th class="px-4 py-2 text-right text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">Workouts</th>
                        <th class="px-4 py-2 text-right text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500">Meals</th>
                        <th class="w-16 px-4 py-2" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="member in topMembers"
                        :key="member.id"
                        class="border-b border-[#26262b] last:border-0 transition-colors hover:bg-white/[0.02]"
                    >
                        <td class="px-4 py-2.5">
                            <div class="text-[12.5px] text-zinc-200">{{ member.name }}</div>
                            <div class="text-[11px] text-zinc-600">{{ member.email }}</div>
                        </td>
                        <td class="px-4 py-2.5 text-right text-[12.5px] text-zinc-300">{{ member.workouts }}</td>
                        <td class="px-4 py-2.5 text-right text-[12.5px] text-zinc-300">{{ member.meals }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <Link
                                :href="`/admin/users/${member.id}/edit`"
                                class="text-[11px] text-zinc-500 transition-colors hover:text-[#c6f24e]"
                            >
                                Open
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>

        <section class="panel">
            <h2 class="border-b border-[#26262b] px-4 py-3 text-[13px] font-medium text-zinc-300">Training goals</h2>

            <div v-if="goals.length === 0" class="px-4 py-8 text-[12px] text-zinc-500">
                No goals recorded yet.
            </div>

            <ul v-else class="space-y-3 px-4 py-4">
                <li v-for="goal in goals" :key="goal.goal">
                    <div class="mb-1 flex items-center justify-between text-[12px]">
                        <span class="capitalize text-zinc-300">{{ goal.goal.replace(/_/g, ' ') }}</span>
                        <span class="text-zinc-500">{{ goal.total }}</span>
                    </div>
                    <div class="h-1 w-full bg-white/5">
                        <div
                            class="h-1 bg-[#c6f24e]"
                            :style="{ width: `${Math.round((goal.total / goalTotal) * 100)}%` }"
                        />
                    </div>
                </li>
            </ul>
        </section>
    </div>
</template>
