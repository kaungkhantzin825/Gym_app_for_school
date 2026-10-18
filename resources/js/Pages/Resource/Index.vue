<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Icon from '../../Components/Icon.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    resource: { type: Object, required: true },
    columns: { type: Array, required: true },
    rows: { type: Array, required: true },
    pagination: { type: Object, required: true },
    filters: { type: Object, required: true },
});

const search = ref(props.filters.search ?? '');

const tones = {
    lime: 'border-[#c6f24e]/30 bg-[#c6f24e]/10 text-[#c6f24e]',
    slate: 'border-[#3f3f46] bg-white/5 text-zinc-400',
    sky: 'border-sky-500/30 bg-sky-500/10 text-sky-300',
    violet: 'border-violet-500/30 bg-violet-500/10 text-violet-300',
    amber: 'border-amber-500/30 bg-amber-500/10 text-amber-300',
};

let timer = null;

watch(search, (value) => {
    clearTimeout(timer);

    timer = setTimeout(() => {
        router.get(
            `/admin/${props.resource.key}`,
            value === '' ? {} : { search: value },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }, 300);
});

function destroy(row) {
    if (!window.confirm(`Delete this ${props.resource.singular.toLowerCase()}? This cannot be undone.`)) {
        return;
    }

    router.delete(`/admin/${props.resource.key}/${row.id}`, { preserveScroll: true });
}

function goToPage(page) {
    const query = { page };

    if (search.value !== '') {
        query.search = search.value;
    }

    router.get(`/admin/${props.resource.key}`, query, { preserveScroll: true, preserveState: true });
}
</script>

<template>
    <Head :title="resource.label" />

    <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="text-[19px] font-semibold tracking-tight text-white">{{ resource.label }}</h1>
            <p class="mt-1 text-[12px] text-zinc-500">
                {{ pagination.total }} {{ pagination.total === 1 ? resource.singular.toLowerCase() : resource.label.toLowerCase() }}
            </p>
        </div>

        <Link
            :href="`/admin/${resource.key}/create`"
            class="flex items-center gap-1.5 bg-[#c6f24e] px-3 py-2 text-[12px] font-semibold text-black transition-opacity hover:opacity-90"
        >
            <Icon name="plus" :size="14" />
            New {{ resource.singular.toLowerCase() }}
        </Link>
    </div>

    <div class="panel">
        <div class="flex items-center gap-2 border-b border-[#26262b] px-3 py-2.5">
            <Icon name="search" :size="15" class="text-zinc-500" />
            <input
                v-model="search"
                type="search"
                :placeholder="`Search ${resource.label.toLowerCase()}`"
                class="w-full bg-transparent text-[13px] text-zinc-200 placeholder:text-zinc-600 focus:outline-none"
            />
        </div>

        <div v-if="rows.length === 0" class="px-4 py-10 text-center text-[13px] text-zinc-500">
            Nothing here yet.
        </div>

        <div v-else class="overflow-x-auto">
            <table class="w-full border-collapse text-left">
                <thead>
                    <tr class="border-b border-[#26262b]">
                        <th
                            v-for="column in columns"
                            :key="column.key"
                            class="whitespace-nowrap px-3 py-2 text-[10px] font-semibold uppercase tracking-[0.12em] text-zinc-500"
                            :class="column.align === 'right' ? 'text-right' : 'text-left'"
                        >
                            {{ column.label }}
                        </th>
                        <th class="w-20 px-3 py-2" />
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in rows"
                        :key="row.id"
                        class="border-b border-[#26262b] last:border-0 transition-colors hover:bg-white/[0.02]"
                    >
                        <td
                            v-for="column in columns"
                            :key="column.key"
                            class="whitespace-nowrap px-3 py-2.5 text-[12.5px]"
                            :class="column.align === 'right' ? 'text-right text-zinc-300' : 'text-zinc-300'"
                        >
                            <span
                                v-if="column.type === 'badge'"
                                class="inline-block border px-1.5 py-0.5 text-[10.5px] font-medium uppercase tracking-wide"
                                :class="tones[row.cells[column.key]?.tone] ?? tones.slate"
                            >
                                {{ row.cells[column.key]?.display }}
                            </span>
                            <span v-else-if="column.type === 'relation'" class="text-zinc-400">
                                {{ row.cells[column.key]?.display }}
                            </span>
                            <span v-else>{{ row.cells[column.key]?.display }}</span>
                        </td>
                        <td class="px-3 py-2.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <Link
                                    :href="`/admin/${resource.key}/${row.id}/edit`"
                                    class="border border-[#26262b] px-2 py-1 text-[11px] text-zinc-400 transition-colors hover:border-zinc-600 hover:text-white"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    class="border border-[#26262b] px-2 py-1 text-[11px] text-zinc-500 transition-colors hover:border-red-500/50 hover:text-red-400"
                                    @click="destroy(row)"
                                >
                                    <Icon name="trash" :size="12" />
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div
            v-if="pagination.pages > 1"
            class="flex items-center justify-between border-t border-[#26262b] px-3 py-2.5 text-[11px] text-zinc-500"
        >
            <span>{{ pagination.from }}–{{ pagination.to }} of {{ pagination.total }}</span>
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    :disabled="pagination.page <= 1"
                    class="border border-[#26262b] px-2 py-1 transition-colors hover:border-zinc-600 hover:text-white disabled:opacity-40"
                    @click="goToPage(pagination.page - 1)"
                >
                    <Icon name="back" :size="12" />
                </button>
                <span class="px-2">{{ pagination.page }} / {{ pagination.pages }}</span>
                <button
                    type="button"
                    :disabled="pagination.page >= pagination.pages"
                    class="border border-[#26262b] px-2 py-1 transition-colors hover:border-zinc-600 hover:text-white disabled:opacity-40"
                    @click="goToPage(pagination.page + 1)"
                >
                    <Icon name="forward" :size="12" />
                </button>
            </div>
        </div>
    </div>
</template>
