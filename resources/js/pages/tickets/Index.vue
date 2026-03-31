<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { index, create, show } from '@/routes/tickets';

type Ticket = {
    id: number;
    subject: string;
    status: string;
    priority: string;
    category: string | null;
    created_at: string;
    updated_at: string;
    assignee: { id: number; name: string; display_name: string | null } | null;
};

type PaginatedTickets = {
    data: Ticket[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
};

defineProps<{ tickets: PaginatedTickets }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'My Tickets', href: index() }],
    },
});

const statusColor: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    waiting_for_user: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    resolved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    closed: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
};

const statusLabel: Record<string, string> = {
    open: 'Open',
    in_progress: 'In Progress',
    waiting_for_user: 'Waiting',
    resolved: 'Resolved',
    closed: 'Closed',
};
</script>

<template>
    <Head title="My Tickets" />

    <div class="flex flex-col gap-6 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">My Tickets</h1>
            <Button as-child>
                <Link :href="create()">Open a New Ticket</Link>
            </Button>
        </div>

        <div v-if="tickets.data.length === 0" class="py-16 text-center text-muted-foreground">
            <p class="text-lg">You have no tickets yet.</p>
            <Button as-child class="mt-4">
                <Link :href="create()">Open a support request</Link>
            </Button>
        </div>

        <div v-else class="flex flex-col gap-3">
            <Link
                v-for="ticket in tickets.data"
                :key="ticket.id"
                :href="show(ticket.id)"
                class="block rounded-lg border bg-card p-4 text-card-foreground shadow-sm transition-colors hover:bg-muted/50"
            >
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-muted-foreground">#{{ ticket.id }}</span>
                            <span v-if="ticket.category" class="text-xs text-muted-foreground">· {{ ticket.category }}</span>
                        </div>
                        <p class="mt-1 truncate font-medium">{{ ticket.subject }}</p>
                        <p class="mt-1 text-xs text-muted-foreground">
                            Updated {{ new Date(ticket.updated_at).toLocaleDateString() }}
                            <span v-if="ticket.assignee"> · Assigned to {{ ticket.assignee.display_name ?? ticket.assignee.name }}</span>
                        </p>
                    </div>
                    <span
                        class="shrink-0 rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="statusColor[ticket.status] ?? 'bg-gray-100 text-gray-800'"
                    >
                        {{ statusLabel[ticket.status] ?? ticket.status }}
                    </span>
                </div>
            </Link>
        </div>

        <!-- Pagination -->
        <div v-if="tickets.last_page > 1" class="flex justify-center gap-1">
            <template v-for="link in tickets.links" :key="link.label">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    class="rounded border px-3 py-1 text-sm"
                    :class="link.active ? 'border-primary bg-primary text-primary-foreground' : 'hover:bg-muted'"
                    v-html="link.label"
                />
                <span v-else class="rounded border px-3 py-1 text-sm text-muted-foreground" v-html="link.label" />
            </template>
        </div>
    </div>
</template>
