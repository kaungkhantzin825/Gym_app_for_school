<script setup>
import { computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '../../Layouts/AdminLayout.vue';
import Icon from '../../Components/Icon.vue';

defineOptions({ layout: AdminLayout });

const props = defineProps({
    resource: { type: Object, required: true },
    fields: { type: Array, required: true },
    record: { type: Object, default: null },
});

const visibleFields = computed(() => props.fields.filter((field) => !field.hidden));

const form = useForm(
    Object.fromEntries(visibleFields.value.map((field) => [field.key, field.value ?? ''])),
);

function submit() {
    if (props.record) {
        form.put(`/admin/${props.resource.key}/${props.record.id}`);
        return;
    }

    form.post(`/admin/${props.resource.key}`);
}

const title = computed(() =>
    props.record ? `Edit ${props.resource.singular.toLowerCase()}` : `New ${props.resource.singular.toLowerCase()}`,
);

const inputClass =
    'w-full border border-[#26262b] bg-[#0f0f11] px-3 py-2 text-[13px] text-zinc-100 outline-none transition-colors focus:border-[#c6f24e]';
</script>

<template>
    <Head :title="title" />

    <div class="mb-5 flex items-center gap-3">
        <Link
            :href="`/admin/${resource.key}`"
            class="flex items-center gap-1 text-[12px] text-zinc-500 transition-colors hover:text-white"
        >
            <Icon name="back" :size="14" />
            {{ resource.label }}
        </Link>
    </div>

    <h1 class="mb-5 text-[19px] font-semibold capitalize tracking-tight text-white">{{ title }}</h1>

    <form class="panel max-w-2xl" @submit.prevent="submit">
        <div class="grid grid-cols-1 gap-4 px-4 py-5 sm:grid-cols-2">
            <div
                v-for="field in visibleFields"
                :key="field.key"
                :class="field.type === 'textarea' ? 'sm:col-span-2' : 'sm:col-span-1'"
            >
                <label :for="field.key" class="mb-1.5 block text-[12px] font-medium text-zinc-400">
                    {{ field.label }}
                    <span v-if="field.suffix" class="text-zinc-600">({{ field.suffix }})</span>
                </label>

                <textarea
                    v-if="field.type === 'textarea'"
                    :id="field.key"
                    v-model="form[field.key]"
                    rows="3"
                    :class="inputClass"
                />

                <select
                    v-else-if="field.type === 'select'"
                    :id="field.key"
                    v-model="form[field.key]"
                    :class="inputClass"
                >
                    <option value="" disabled>Choose…</option>
                    <option v-for="(label, value) in field.options" :key="value" :value="value">{{ label }}</option>
                </select>

                <input
                    v-else
                    :id="field.key"
                    v-model="form[field.key]"
                    :type="field.type === 'datetime' ? 'datetime-local' : field.type"
                    :step="field.step"
                    :class="inputClass"
                    :autocomplete="field.type === 'password' ? 'new-password' : 'off'"
                />

                <p v-if="form.errors[field.key]" class="mt-1.5 text-[12px] text-red-400">
                    {{ form.errors[field.key] }}
                </p>
                <p v-else-if="field.type === 'password' && record" class="mt-1.5 text-[11px] text-zinc-600">
                    Leave blank to keep the current password.
                </p>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 border-t border-[#26262b] px-4 py-3">
            <Link
                :href="`/admin/${resource.key}`"
                class="border border-[#26262b] px-3 py-2 text-[12px] text-zinc-400 transition-colors hover:border-zinc-600 hover:text-white"
            >
                Cancel
            </Link>
            <button
                type="submit"
                :disabled="form.processing"
                class="bg-[#c6f24e] px-4 py-2 text-[12px] font-semibold text-black transition-opacity hover:opacity-90 disabled:opacity-50"
            >
                {{ form.processing ? 'Saving…' : 'Save' }}
            </button>
        </div>
    </form>
</template>
