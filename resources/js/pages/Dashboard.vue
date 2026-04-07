<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BookOpen,
    CheckCircle2,
    Clock,
    Inbox,
    LifeBuoy,
    Loader2,
    MessageSquare,
    Users,
    UserCog,
} from 'lucide-vue-next';
import { dashboard } from '@/routes';
import { index as staffTicketsIndex } from '@/routes/staff/tickets';
import { show as ticketShow } from '@/routes/tickets';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Dashboard',
                href: dashboard(),
            },
        ],
    },
});

const props = defineProps<{
    stats: {
        totalTickets: number;
        openTickets: number;
        inProgressTickets: number;
        waitingTickets: number;
        resolvedTickets: number;
        closedTickets: number;
        urgentTickets: number;
        unassignedTickets: number;
        publishedArticles: number;
        draftArticles: number;
        totalUsers: number;
        staffCount: number;
    };
    recentTickets: Array<{
        id: number;
        subject: string;
        status: string;
        statusLabel: string;
        statusColor: string;
        priority: string;
        priorityLabel: string;
        priorityColor: string;
        requester: string | null;
        assignee: string | null;
        createdAt: string;
    }>;
    ticketsByCategory: Array<{
        category: string;
        count: number;
    }>;
}>();

const activeTickets =
    props.stats.openTickets +
    props.stats.inProgressTickets +
    props.stats.waitingTickets;

const statusColorMap: Record<string, string> = {
    blue: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
    yellow: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400',
    orange: 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
    green: 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
    gray: 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400',
    red: 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
};
</script>

<template>
    <Head title="Dashboard" />

    <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6">
        <!-- Stats overview -->
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-muted-foreground">
                        Active Tickets
                    </p>
                    <Inbox class="h-4 w-4 text-muted-foreground" />
                </div>
                <p class="mt-2 text-3xl font-bold">{{ activeTickets }}</p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ stats.openTickets }} open,
                    {{ stats.inProgressTickets }} in progress
                </p>
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-muted-foreground">
                        Urgent
                    </p>
                    <AlertTriangle class="h-4 w-4 text-red-500" />
                </div>
                <p
                    class="mt-2 text-3xl font-bold"
                    :class="
                        stats.urgentTickets > 0
                            ? 'text-red-600 dark:text-red-400'
                            : ''
                    "
                >
                    {{ stats.urgentTickets }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ stats.unassignedTickets }} unassigned
                </p>
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-muted-foreground">
                        Resolved
                    </p>
                    <CheckCircle2 class="h-4 w-4 text-primary" />
                </div>
                <p class="mt-2 text-3xl font-bold">
                    {{ stats.resolvedTickets + stats.closedTickets }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    of {{ stats.totalTickets }} total tickets
                </p>
            </div>

            <div class="rounded-xl border border-border bg-card p-5">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-muted-foreground">
                        Knowledge Base
                    </p>
                    <BookOpen class="h-4 w-4 text-muted-foreground" />
                </div>
                <p class="mt-2 text-3xl font-bold">
                    {{ stats.publishedArticles }}
                </p>
                <p class="mt-1 text-xs text-muted-foreground">
                    {{ stats.draftArticles }} drafts
                </p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Recent tickets -->
            <div class="rounded-xl border border-border bg-card lg:col-span-2">
                <div
                    class="flex items-center justify-between border-b border-border px-5 py-4"
                >
                    <h2 class="text-sm font-semibold">Recent Tickets</h2>
                    <Link
                        :href="staffTicketsIndex()"
                        class="text-xs font-medium text-primary hover:underline"
                    >
                        View all
                    </Link>
                </div>
                <div class="divide-y divide-border">
                    <Link
                        v-for="ticket in recentTickets"
                        :key="ticket.id"
                        :href="ticketShow({ ticket: ticket.id })"
                        class="flex items-center gap-4 px-5 py-3.5 transition hover:bg-accent/50"
                    >
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium">
                                {{ ticket.subject }}
                            </p>
                            <p class="mt-0.5 text-xs text-muted-foreground">
                                {{ ticket.requester }} &middot;
                                {{ ticket.createdAt }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-semibold tracking-wide uppercase"
                                :class="
                                    statusColorMap[ticket.statusColor] ||
                                    statusColorMap.gray
                                "
                            >
                                {{ ticket.statusLabel }}
                            </span>
                            <span
                                class="rounded-full px-2 py-0.5 text-[10px] font-semibold tracking-wide uppercase"
                                :class="
                                    statusColorMap[ticket.priorityColor] ||
                                    statusColorMap.gray
                                "
                            >
                                {{ ticket.priorityLabel }}
                            </span>
                        </div>
                    </Link>
                    <div
                        v-if="recentTickets.length === 0"
                        class="px-5 py-8 text-center text-sm text-muted-foreground"
                    >
                        No tickets yet.
                    </div>
                </div>
            </div>

            <!-- Sidebar stats -->
            <div class="flex flex-col gap-6">
                <!-- Ticket status breakdown -->
                <div class="rounded-xl border border-border bg-card p-5">
                    <h2 class="mb-4 text-sm font-semibold">Ticket Status</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <Inbox class="h-3.5 w-3.5 text-blue-500" /> Open
                            </span>
                            <span class="font-medium">{{
                                stats.openTickets
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <Loader2 class="h-3.5 w-3.5 text-yellow-500" />
                                In Progress
                            </span>
                            <span class="font-medium">{{
                                stats.inProgressTickets
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <Clock class="h-3.5 w-3.5 text-orange-500" />
                                Waiting
                            </span>
                            <span class="font-medium">{{
                                stats.waitingTickets
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <CheckCircle2
                                    class="h-3.5 w-3.5 text-green-500"
                                />
                                Resolved
                            </span>
                            <span class="font-medium">{{
                                stats.resolvedTickets
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <MessageSquare
                                    class="h-3.5 w-3.5 text-gray-400"
                                />
                                Closed
                            </span>
                            <span class="font-medium">{{
                                stats.closedTickets
                            }}</span>
                        </div>
                    </div>
                </div>

                <!-- Categories & team -->
                <div class="rounded-xl border border-border bg-card p-5">
                    <h2 class="mb-4 text-sm font-semibold">Categories</h2>
                    <div class="space-y-3">
                        <div
                            v-for="cat in ticketsByCategory"
                            :key="cat.category"
                            class="flex items-center justify-between text-sm"
                        >
                            <span class="text-muted-foreground">{{
                                cat.category
                            }}</span>
                            <span class="font-medium">{{ cat.count }}</span>
                        </div>
                        <div
                            v-if="ticketsByCategory.length === 0"
                            class="text-sm text-muted-foreground"
                        >
                            No categorized tickets yet.
                        </div>
                    </div>
                </div>

                <div class="rounded-xl border border-border bg-card p-5">
                    <h2 class="mb-4 text-sm font-semibold">Team</h2>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <Users class="h-3.5 w-3.5" /> Total Users
                            </span>
                            <span class="font-medium">{{
                                stats.totalUsers
                            }}</span>
                        </div>
                        <div class="flex items-center justify-between text-sm">
                            <span
                                class="flex items-center gap-2 text-muted-foreground"
                            >
                                <UserCog class="h-3.5 w-3.5" /> Staff Members
                            </span>
                            <span class="font-medium">{{
                                stats.staffCount
                            }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
