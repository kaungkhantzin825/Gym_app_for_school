<script setup>
import { computed, ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import Icon from '../Components/Icon.vue';

const page = usePage();
const sidebarOpen = ref(false);

const navigation = computed(() => page.props.navigation ?? []);
const admin = computed(() => page.props.admin ?? page.props.auth?.user ?? {});
const flash = computed(() => page.props.flash ?? {});

const currentUrl = computed(() => page.url.split('?')[0]);

const overview = [
    { label: 'Dashboard', href: '/admin', icon: 'dashboard' },
    { label: 'Calendar', href: '/admin/calendar', icon: 'calendar' },
    { label: 'Report', href: '/admin/report', icon: 'report' },
];

function isActive(href) {
    if (href === '/admin') {
        return currentUrl.value === '/admin';
    }

    return currentUrl.value.startsWith(href);
}
</script>

<template>
    <div class="min-h-screen bg-[#0a0a0b] text-zinc-200">
        <!-- Sidebar -->
        <aside
            class="fixed inset-y-0 left-0 z-40 w-60 shrink-0 overflow-y-auto border-r border-[#26262b] bg-[#0f0f11] transition-transform lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="flex items-center justify-between px-5 py-5">
                <div>
                    <div class="text-[15px] font-semibold tracking-tight text-white">INTI Gym</div>
                    <div class="text-[11px] uppercase tracking-[0.14em] text-zinc-500">Admin</div>
                </div>
                <button
                    type="button"
                    class="text-zinc-500 hover:text-white lg:hidden"
                    aria-label="Close menu"
                    @click="sidebarOpen = false"
                >
                    <Icon name="back" />
                </button>
            </div>

            <nav class="px-2 pb-6">
                <div class="px-3 pb-2 pt-2 text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-600">
                    Overview
                </div>
                <Link
                    v-for="item in overview"
                    :key="item.href"
                    :href="item.href"
                    class="flex items-center gap-2.5 rounded-md px-3 py-2 text-[13px] transition-colors"
                    :class="
                        isActive(item.href)
                            ? 'bg-[#c6f24e]/10 text-[#c6f24e]'
                            : 'text-zinc-400 hover:bg-white/5 hover:text-zinc-100'
                    "
                    @click="sidebarOpen = false"
                >
                    <Icon :name="item.icon" />
                    <span>{{ item.label }}</span>
                </Link>

                <template v-for="group in navigation" :key="group.group">
                    <div class="px-3 pb-2 pt-5 text-[10px] font-semibold uppercase tracking-[0.14em] text-zinc-600">
                        {{ group.group }}
                    </div>
                    <Link
                        v-for="item in group.items"
                        :key="item.key"
                        :href="`/admin/${item.key}`"
                        class="flex items-center gap-2.5 rounded-md px-3 py-2 text-[13px] transition-colors"
                        :class="
                            isActive(`/admin/${item.key}`)
                                ? 'bg-[#c6f24e]/10 text-[#c6f24e]'
                                : 'text-zinc-400 hover:bg-white/5 hover:text-zinc-100'
                        "
                        @click="sidebarOpen = false"
                    >
                        <Icon :name="item.icon" />
                        <span>{{ item.label }}</span>
                    </Link>
                </template>
            </nav>
        </aside>

        <div
            v-if="sidebarOpen"
            class="fixed inset-0 z-30 bg-black/60 lg:hidden"
            @click="sidebarOpen = false"
        />

        <!-- Content -->
        <div class="lg:pl-60">
            <header class="sticky top-0 z-20 flex h-14 items-center justify-between border-b border-[#26262b] bg-[#0a0a0b]/90 px-4 backdrop-blur lg:px-8">
                <button
                    type="button"
                    class="text-zinc-400 hover:text-white lg:hidden"
                    aria-label="Open menu"
                    @click="sidebarOpen = true"
                >
                    <Icon name="menu" />
                </button>

                <div class="hidden text-[12px] text-zinc-500 lg:block">
                    Signed in as <span class="text-zinc-300">{{ admin.email }}</span>
                </div>

                <div class="flex items-center gap-3">
                    <span class="text-[12px] text-zinc-400">{{ admin.name }}</span>
                    <Link
                        href="/admin/logout"
                        method="post"
                        as="button"
                        class="flex items-center gap-1.5 rounded-md border border-[#26262b] px-2.5 py-1.5 text-[12px] text-zinc-400 transition-colors hover:border-zinc-600 hover:text-white"
                    >
                        <Icon name="logout" :size="14" />
                        Sign out
                    </Link>
                </div>
            </header>

            <div
                v-if="flash.success"
                class="mx-4 mt-4 border border-[#c6f24e]/30 bg-[#c6f24e]/10 px-4 py-2.5 text-[13px] text-[#c6f24e] lg:mx-8"
            >
                {{ flash.success }}
            </div>
            <div
                v-if="flash.error"
                class="mx-4 mt-4 border border-red-500/30 bg-red-500/10 px-4 py-2.5 text-[13px] text-red-300 lg:mx-8"
            >
                {{ flash.error }}
            </div>

            <main class="px-4 py-6 lg:px-8">
                <slot />
            </main>
        </div>
    </div>
</template>
