<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import { LifeBuoy, LayoutGrid } from 'lucide-vue-next';
import { computed } from 'vue';

defineProps<{
    canRegister: boolean;
    lanCoreEnabled: boolean;
}>();

const page = usePage();
const user = computed(() => (page.props.auth as any)?.user);
</script>

<template>
    <Head :title="$t('landing.headTitle')" />

    <div class="flex min-h-screen flex-col bg-background text-foreground">
        <header
            class="mx-auto flex w-full max-w-5xl items-center justify-between px-6 py-6"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-primary"
                >
                    <LifeBuoy class="h-5 w-5 text-primary-foreground" />
                </div>
                <span class="text-lg font-semibold tracking-tight">{{
                    $t('common.appName')
                }}</span>
            </div>
            <div class="flex items-center gap-3">
                <Link
                    href="/kb"
                    class="text-sm font-medium text-muted-foreground transition hover:text-foreground"
                >
                    {{ $t('navigation.knowledgeBase') }}
                </Link>
                <template v-if="user">
                    <Link
                        v-if="user.role === 'admin'"
                        href="/dashboard"
                        class="rounded-full bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
                    >
                        <LayoutGrid class="mr-1.5 inline-block h-4 w-4" />
                        {{ $t('navigation.dashboard') }}
                    </Link>
                    <Link
                        v-else
                        href="/tickets"
                        class="rounded-full bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
                    >
                        {{ $t('navigation.myTickets') }}
                    </Link>
                </template>
                <template v-else>
                    <Link
                        href="/login"
                        class="rounded-full border border-border px-5 py-2 text-sm font-medium text-muted-foreground transition hover:border-primary hover:text-foreground"
                    >
                        {{ $t('landing.signIn') }}
                    </Link>
                    <Link
                        v-if="canRegister"
                        href="/register"
                        class="rounded-full bg-primary px-5 py-2 text-sm font-medium text-primary-foreground transition hover:bg-primary/90"
                    >
                        {{ $t('landing.register') }}
                    </Link>
                </template>
            </div>
        </header>

        <main
            class="flex flex-1 flex-col items-center justify-center px-6 text-center"
        >
            <div
                class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-primary/10"
            >
                <LifeBuoy class="h-8 w-8 text-primary" />
            </div>
            <h1
                class="mt-6 max-w-2xl text-4xl leading-tight font-bold sm:text-5xl"
            >
                {{ $t('landing.hero') }}
            </h1>
            <p
                class="mt-4 max-w-lg text-lg leading-relaxed text-muted-foreground"
            >
                {{ $t('landing.heroDescription') }}
            </p>

            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                <template v-if="user">
                    <Link
                        v-if="user.role === 'admin'"
                        href="/dashboard"
                        class="rounded-xl bg-primary px-7 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                    >
                        <LayoutGrid class="mr-1.5 inline-block h-4 w-4" />
                        {{ $t('landing.goToDashboard') }}
                    </Link>
                    <Link
                        href="/tickets"
                        class="rounded-xl border border-border px-7 py-3 text-sm font-medium text-muted-foreground transition hover:border-primary hover:text-foreground"
                    >
                        {{ $t('navigation.myTickets') }}
                    </Link>
                    <Link
                        href="/kb"
                        class="rounded-xl border border-border px-7 py-3 text-sm font-medium text-muted-foreground transition hover:border-primary hover:text-foreground"
                    >
                        {{ $t('navigation.knowledgeBase') }}
                    </Link>
                </template>
                <template v-else>
                    <Link
                        href="/kb"
                        class="rounded-xl bg-primary px-7 py-3 text-sm font-semibold text-primary-foreground transition hover:bg-primary/90"
                    >
                        {{ $t('landing.browseKb') }}
                    </Link>
                    <Link
                        href="/login"
                        class="rounded-xl border border-border px-7 py-3 text-sm font-medium text-muted-foreground transition hover:border-primary hover:text-foreground"
                    >
                        {{ $t('landing.signInToSubmit') }}
                    </Link>
                </template>
            </div>

            <div class="mx-auto mt-20 grid max-w-3xl gap-6 sm:grid-cols-3">
                <div
                    class="rounded-xl border border-border bg-card p-6 text-left"
                >
                    <div
                        class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-primary"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"
                            />
                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            <path d="M10 12h4" />
                            <path d="M10 16h4" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold">
                        {{ $t('landing.features.tickets.title') }}
                    </h3>
                    <p
                        class="mt-2 text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ $t('landing.features.tickets.description') }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-border bg-card p-6 text-left"
                >
                    <div
                        class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-primary"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H19a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H6.5a1 1 0 0 1 0-5H20"
                            />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold">
                        {{ $t('landing.features.kb.title') }}
                    </h3>
                    <p
                        class="mt-2 text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ $t('landing.features.kb.description') }}
                    </p>
                </div>
                <div
                    class="rounded-xl border border-border bg-card p-6 text-left"
                >
                    <div
                        class="mb-3 flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-primary"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"
                            />
                            <circle cx="9" cy="7" r="4" />
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87" />
                            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold">
                        {{ $t('landing.features.staffDashboard.title') }}
                    </h3>
                    <p
                        class="mt-2 text-sm leading-relaxed text-muted-foreground"
                    >
                        {{ $t('landing.features.staffDashboard.description') }}
                    </p>
                </div>
            </div>
        </main>

        <footer class="py-8 text-center text-xs text-muted-foreground">
            {{ $t('landing.footer') }}
        </footer>
    </div>
</template>
