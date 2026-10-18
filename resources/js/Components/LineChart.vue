<script setup>
import { computed } from 'vue';

/*
 * Dependency-free line chart.
 *
 * The admin only needs small trend panels, so this draws straight into SVG
 * instead of pulling a charting library into the bundle. Series are scaled
 * against a shared maximum so two lines stay comparable.
 */
const props = defineProps({
    labels: { type: Array, required: true },
    series: { type: Array, required: true },
    height: { type: Number, default: 180 },
});

const WIDTH = 640;
const PAD_X = 8;
const PAD_TOP = 12;
const PAD_BOTTOM = 22;

const max = computed(() => {
    const values = props.series.flatMap((entry) => entry.data.map(Number));
    return Math.max(1, ...values);
});

const plotHeight = computed(() => props.height - PAD_TOP - PAD_BOTTOM);

function x(index) {
    const count = Math.max(1, props.labels.length - 1);
    return PAD_X + (index * (WIDTH - PAD_X * 2)) / count;
}

function y(value) {
    return PAD_TOP + plotHeight.value - (Number(value) / max.value) * plotHeight.value;
}

const shapes = computed(() =>
    props.series.map((entry) => {
        const points = entry.data.map((value, index) => `${x(index)},${y(value)}`);

        return {
            name: entry.name,
            color: entry.color ?? '#c6f24e',
            line: points.length === 0 ? '' : `M${points.join(' L')}`,
            area:
                points.length === 0
                    ? ''
                    : `M${points.join(' L')} L${x(entry.data.length - 1)},${PAD_TOP + plotHeight.value} L${x(0)},${
                          PAD_TOP + plotHeight.value
                      } Z`,
            points: entry.data.map((value, index) => ({
                cx: x(index),
                cy: y(value),
                value,
                label: props.labels[index],
            })),
        };
    }),
);

const gridLines = computed(() =>
    [0, 0.25, 0.5, 0.75, 1].map((fraction) => ({
        y: PAD_TOP + plotHeight.value * fraction,
        value: Math.round(max.value * (1 - fraction)),
    })),
);

// Only label a handful of x ticks so the axis stays readable.
const tickEvery = computed(() => Math.max(1, Math.ceil(props.labels.length / 7)));
</script>

<template>
    <div>
        <div class="mb-3 flex flex-wrap items-center gap-4">
            <div v-for="entry in series" :key="entry.name" class="flex items-center gap-1.5 text-[11px] text-zinc-400">
                <span class="inline-block h-[2px] w-4" :style="{ backgroundColor: entry.color ?? '#c6f24e' }" />
                {{ entry.name }}
            </div>
        </div>

        <svg :viewBox="`0 0 ${WIDTH} ${height}`" class="w-full" role="img" :aria-label="series.map((s) => s.name).join(', ')">
            <g>
                <line
                    v-for="line in gridLines"
                    :key="line.y"
                    :x1="PAD_X"
                    :x2="WIDTH - PAD_X"
                    :y1="line.y"
                    :y2="line.y"
                    stroke="#26262b"
                    stroke-width="1"
                />
                <text :x="WIDTH - PAD_X" :y="line.y - 4" text-anchor="end" fill="#52525b" font-size="9">
                    {{ line.value }}
                </text>
            </g>

            <g v-for="shape in shapes" :key="`area-${shape.name}`">
                <path :d="shape.area" :fill="shape.color" opacity="0.08" />
            </g>

            <g v-for="shape in shapes" :key="`line-${shape.name}`">
                <path :d="shape.line" fill="none" :stroke="shape.color" stroke-width="1.8" stroke-linejoin="round" />
                <circle v-for="(point, index) in shape.points" :key="index" :cx="point.cx" :cy="point.cy" r="2" :fill="shape.color">
                    <title>{{ point.label }} · {{ shape.name }}: {{ point.value }}</title>
                </circle>
            </g>

            <g>
                <text
                    v-for="(label, index) in labels"
                    v-show="index % tickEvery === 0"
                    :key="label + index"
                    :x="x(index)"
                    :y="height - 6"
                    text-anchor="middle"
                    fill="#52525b"
                    font-size="9"
                >
                    {{ label }}
                </text>
            </g>
        </svg>
    </div>
</template>
