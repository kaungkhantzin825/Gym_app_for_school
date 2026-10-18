<script setup>
import { computed } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../Layouts/AdminLayout.vue';
import Icon from '../Components/Icon.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    month: { type: String, required: true },
    label: { type: String, required: true },
    previous: { type: String, required: true },
    next: { type: String, required: true },
    today: { type: String, required: true },
    selected: { type: String, required: true },
    weeks: { type: Array, required: true },
    records: { type: Object, required: true },
    summary: { type: Object, required: true },
});

const weekdays = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

const selectedLabel = computed(() =>
    new Date(`${props.selected}T00:00:00`).toLocaleDateString(undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'long',
    }),
);

const totalRecords = computed(() => props.records.workouts.length + props.records.meals.length);

function visit(query) {
    router.get('/admin/calendar', query, { preserveState: true, preserveScroll: true, replace: true });
}

function selectDay(day) {
    visit({ month: props.month, day: day.date });
}

function goToMonth(month) {
    visit({ month });
}

const kg = (value) => `${new Intl.NumberFormat().format(Math.round(value))} kg`;
</script>

<template>
    <Head title="Calendar" />

    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-[19px] font-semibold tracking-tight text-white">Calendar</h1>
            <p class="mt-1 text-[12px] text-zinc-500">Every workout and meal members recorded.</p>
        </div>

        <div class="flex items-center gap-1">
            <button
                type="button"
                class="border border-[#26262b] p-2 text-zinc-400 transition-colors hover:border-zinc-600 hover:text-white"
                aria-label="Previous month"
                @click="goToMonth(previous)"
            >
                <Icon name="back" :size="14" />
            </button>
            <span class="min-w-[130px] text-center text-[13px] font-medium text-zinc-200">{{ label }}</span>
            <button
                type="button"
                class="border border-[#26262b] p-2 text-zinc-400 transition-colors hover:border-zinc-600 hover:text-white"
                aria-label="Next month"
                @click="goToMonth(next)"
            >
                <Icon name="forward" :size="14" />
            </button>
            <button
                type="button"
                class="ml-1 border border-[#26262b] px-2.5 py-2 text-[11px] text-zinc-400 transition-colors hover:border-zinc-600 hover:text-white"
                @click="visit({ month, day: today })"
            >
                Today
            </button>
        </div>
    </div>

    <div class="mb-4 grid grid-cols-2 gap-px border border-[#26262b] bg-[#26262b] sm:grid-cols-4">
        <div class="bg-[#121214] px-4 py-3">
            <div class="text-[10px] uppercase tracking-[0.12em] text-zinc-500">Workouts</div>
            <div class="mt-1 text-[17px] font-semibold text-white">{{ summary.workouts }}</div>
        </div>
        <div class="bg-[#121214] px-4 py-3">
            <div class="text-[10px] uppercase tracking-[0.12em] text-zinc-500">Meals</div>
            <div class="mt-1 text-[17px] font-semibold text-white">{{ summary.meals }}</div>
        </div>
        <div class="bg-[#121214] px-4 py-3">
            <div class="text-[10px] uppercase tracking-[0.12em] text-zinc-500">Volume</div>
            <div class="mt-1 text-[17px] font-semibold text-white">{{ kg(summary.volume) }}</div>
        </div>
        <div class="bg-[#121214] px-4 py-3">
            <div class="text-[10px] uppercase tracking-[0.12em] text-zinc-500">Active members</div>
            <div class="mt-1 text-[17px] font-semibold text-white">{{ summary.activeMembers }}</div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3">
        <!-- Month grid -->
        <section class="panel xl:col-span-2">
            <div class="grid grid-cols-7 border-b border-[#26262b]">
                <div
                    v-for="day in weekdays"
                    :key="day"
                    class="px-2 py-2 text-center text-[10px] font-semibold uppercase tracking-[0.1em] text-zinc-600"
                >
                    {{ day }}
                </div>
            </div>

            <div class="grid grid-cols-7">
                <template v-for="(week, weekIndex) in weeks" :key="weekIndex">
                    <button
                        v-for="day in week"
                        :key="day.date"
                        type="button"
                        class="relative flex h-20 flex-col items-start gap-1 border-b border-r border-[#26262b] px-2 py-1.5 text-left transition-colors hover:bg-white/[0.03]"
                        :class="[
                            day.inMonth ? '' : 'opacity-35',
                            day.isSelected ? 'bg-[#c6f24e]/10' : '',
                        ]"
                        @click="selectDay(day)"
                    >
                        <span
                            class="flex h-5 w-5 items-center justify-center text-[11px]"
                            :class="
                                day.isToday
                                    ? 'bg-[#c6f24e] font-semibold text-black'
                                    : day.isSelected
                                      ? 'text-[#c6f24e]'
                                      : 'text-zinc-400'
                            "
                        >
                            {{ day.day }}
                        </span>

                        <div class="flex flex-wrap gap-1">
                            <span
                                v-if="day.workouts > 0"
                                class="border border-[#c6f24e]/30 bg-[#c6f24e]/10 px-1 text-[10px] text-[#c6f24e]"
                                :title="`${day.workouts} workouts`"
                            >
                                {{ day.workouts }}w
                            </span>
                            <span
                                v-if="day.meals > 0"
                                class="border border-[#3f3f46] bg-white/5 px-1 text-[10px] text-zinc-400"
                                :title="`${day.meals} meals`"
                            >
                                {{ day.meals }}m
                            </span>
                        </div>
                    </button>
                </template>
            </div>
        </section>

        <!-- Selected day -->
        <section class="panel">
            <h2 class="border-b border-[#26262b] px-4 py-3 text-[13px] font-medium text-zinc-300">
                {{ selectedLabel }}
            </h2>

            <div v-if="totalRecords === 0" class="px-4 py-8 text-[12px] text-zinc-500">
                Nothing recorded on this day.
            </div>

            <div v-else class="divide-y divide-[#26262b]">
                <div v-if="records.workouts.length" class="px-4 py-3">
                    <div class="mb-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-600">
                        Workouts
                    </div>
                    <ul class="space-y-2">
                        <li v-for="workout in records.workouts" :key="workout.id" class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate text-[12.5px] text-zinc-200">{{ workout.title }}</div>
                                <div class="text-[11px] text-zinc-500">{{ workout.member }}</div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-[12px] text-zinc-300">{{ kg(workout.volume) }}</div>
                                <div class="text-[11px] text-zinc-600">{{ workout.time }}</div>
                            </div>
                        </li>
                    </ul>
                </div>

                <div v-if="records.meals.length" class="px-4 py-3">
                    <div class="mb-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-600">
                        Meals
                    </div>
                    <ul class="space-y-2">
                        <li v-for="meal in records.meals" :key="meal.id" class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="truncate text-[12.5px] text-zinc-200">{{ meal.title }}</div>
                                <div class="text-[11px] text-zinc-500">{{ meal.member }}</div>
                            </div>
                            <div class="shrink-0 text-right">
                                <div class="text-[12px] text-zinc-300">{{ Math.round(meal.calories) }} kcal</div>
                                <div class="text-[11px] text-zinc-600">{{ meal.time }}</div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-[#26262b] px-4 py-3">
                <div class="mb-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-600">
                    Add a record
                </div>
                <div class="flex flex-wrap gap-2">
                    <Link
                        :href="`/admin/workouts/create?date=${selected}`"
                        class="flex items-center gap-1.5 border border-[#26262b] px-2.5 py-1.5 text-[11px] text-zinc-400 transition-colors hover:border-[#c6f24e]/40 hover:text-[#c6f24e]"
                    >
                        <Icon name="plus" :size="12" />
                        Workout
                    </Link>
                    <Link
                        :href="`/admin/meals/create?date=${selected}`"
                        class="flex items-center gap-1.5 border border-[#26262b] px-2.5 py-1.5 text-[11px] text-zinc-400 transition-colors hover:border-[#c6f24e]/40 hover:text-[#c6f24e]"
                    >
                        <Icon name="plus" :size="12" />
                        Meal
                    </Link>
                </div>
            </div>
        </section>
    </div>
</template>
