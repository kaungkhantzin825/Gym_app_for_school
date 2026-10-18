<script setup>
import { computed } from 'vue';

/*
 * Inline stroke icons. Kept in one component so every page draws from the
 * same 24px grid and no icon font or library has to be loaded.
 */
const props = defineProps({
    name: { type: String, required: true },
    size: { type: Number, default: 18 },
});

const paths = {
    dashboard: ['M3 3h7v7H3z', 'M14 3h7v4h-7z', 'M14 11h7v10h-7z', 'M3 14h7v7H3z'],
    calendar: ['M3 5h18v16H3z', 'M3 10h18', 'M8 3v4', 'M16 3v4'],
    report: ['M4 20V10', 'M10 20V4', 'M16 20v-7', 'M22 20H2'],
    users: ['M16 20v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2', 'M9 10a4 4 0 1 0 0-8 4 4 0 0 0 0 8', 'M22 20v-2a4 4 0 0 0-3-3.9', 'M16 3.1a4 4 0 0 1 0 7.8'],
    dumbbell: ['M6 7v10', 'M3 10v4', 'M18 7v10', 'M21 10v4', 'M6 12h12'],
    activity: ['M3 12h4l3 8 4-16 3 8h4'],
    utensils: ['M5 3v8a3 3 0 0 0 6 0V3', 'M8 11v10', 'M17 3c-1.5 2-2 4-2 6a2 2 0 0 0 4 0c0-2-.5-4-2-6', 'M17 13v8'],
    play: ['M6 4l14 8-14 8z'],
    inbox: ['M3 12h5l2 3h4l2-3h5', 'M4 5h16l1 7v7H3v-7z'],
    settings: ['M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6', 'M19.4 15a1.6 1.6 0 0 0 .3 1.8l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.6 1.6 0 0 0-2.7 1.1V21a2 2 0 1 1-4 0v-.1A1.6 1.6 0 0 0 7 19.4l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1A1.6 1.6 0 0 0 3 13.9H3a2 2 0 1 1 0-4h.1A1.6 1.6 0 0 0 4.6 7L4.5 7a2 2 0 1 1 2.8-2.8l.1.1A1.6 1.6 0 0 0 10 4.6V4a2 2 0 1 1 4 0v.1a1.6 1.6 0 0 0 2.7 1.1l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.6 1.6 0 0 0 1.1 2.7H21a2 2 0 1 1 0 4h-.1a1.6 1.6 0 0 0-1.5 1z'],
    plus: ['M12 5v14', 'M5 12h14'],
    search: ['M11 19a8 8 0 1 0 0-16 8 8 0 0 0 0 16', 'M21 21l-4.3-4.3'],
    back: ['M15 18l-6-6 6-6'],
    forward: ['M9 6l6 6-6 6'],
    trash: ['M4 7h16', 'M9 7V4h6v3', 'M6 7l1 13h10l1-13'],
    logout: ['M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4', 'M16 17l5-5-5-5', 'M21 12H9'],
    menu: ['M3 6h18', 'M3 12h18', 'M3 18h18'],
};

const box = computed(() => paths[props.name] ?? paths.dashboard);
</script>

<template>
    <svg
        :width="size"
        :height="size"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.6"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
    >
        <path v-for="(d, index) in box" :key="index" :d="d" />
    </svg>
</template>
