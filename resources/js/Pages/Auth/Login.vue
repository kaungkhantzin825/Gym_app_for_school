<script setup>
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/admin/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <Head title="Sign in" />

    <div class="flex min-h-screen items-center justify-center bg-[#0a0a0b] px-4">
        <div class="w-full max-w-sm">
            <div class="mb-8">
                <div class="text-lg font-semibold tracking-tight text-white">INTI Gym</div>
                <div class="mt-1 text-[13px] text-zinc-500">Sign in to the admin panel.</div>
            </div>

            <form class="space-y-4" @submit.prevent="submit">
                <div>
                    <label for="email" class="mb-1.5 block text-[12px] font-medium text-zinc-400">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="username"
                        autofocus
                        class="w-full border border-[#26262b] bg-[#121214] px-3 py-2 text-[13px] text-zinc-100 outline-none transition-colors focus:border-[#c6f24e]"
                    />
                    <p v-if="form.errors.email" class="mt-1.5 text-[12px] text-red-400">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-[12px] font-medium text-zinc-400">Password</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        class="w-full border border-[#26262b] bg-[#121214] px-3 py-2 text-[13px] text-zinc-100 outline-none transition-colors focus:border-[#c6f24e]"
                    />
                    <p v-if="form.errors.password" class="mt-1.5 text-[12px] text-red-400">{{ form.errors.password }}</p>
                </div>

                <label class="flex items-center gap-2 text-[12px] text-zinc-400">
                    <input v-model="form.remember" type="checkbox" class="accent-[#c6f24e]" />
                    Keep me signed in
                </label>

                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full bg-[#c6f24e] px-4 py-2.5 text-[13px] font-semibold text-black transition-opacity hover:opacity-90 disabled:opacity-50"
                >
                    {{ form.processing ? 'Signing in…' : 'Sign in' }}
                </button>
            </form>
        </div>
    </div>
</template>
