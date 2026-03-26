<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { index, assign } from '@/routes/staff/tickets';
import { show } from '@/routes/tickets';

type User = { id: number; name: string; display_name: string | null };
type Ticket = {
    id: number;
    subject: string;
    status: string;
    priority: string;
    category: string | null;
    updated_at: string;
    requester: User;
    assignee: User | null;
};
type PaginatedTickets = {
    data: Ticket[];
    links: { url: string | null; label: string; active: boolean }[];
    meta: { current_page: number; last_page: number; total: number };
};
type Status = { value: string; label: string };

const props = defineProps<{
    tickets: PaginatedTickets;
    staffUsers: User[];
    statuses: Status[];
    filters: { status: string | null; assignee: string | null; scope: string };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Staff Board', href: index() }],
    },
});

const statusColor: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    in_progress: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    waiting_for_user: 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    resolved: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    closed: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
};

const priorityColor: Record<string, string> = {
    urgent: 'text-red-600 font-bold',
    high: 'text-orange-500',
    normal: '',
    low: 'text-gray-400',
};

const selectedStatus = ref(props.filters.status ?? '');
const selectedAssignee = ref(props.filters.assignee ?? '');
const selectedScope = ref(props.filters.scope ?? 'all');

function applyFilters() {
    router.get(index().url, {
        status: selectedStatus.value || undefined,
        assignee: selectedAssignee.value || undefined,
        scope: selectedScope.value !== 'all' ? selectedScope.value : undefined,
    }, { preserveState: true, replace: true });
}

function assignTicket(ticketId: number, assigneeId: string) {
    if (!assigneeId) return;
    router.patch(assign(ticketId).url, { assignee_id: assigneeId });
}
</script>

<template>
    <Head title="Staff Board" />

    <div class="flex flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Staff Ticket Board</h1>
            <span class="text-sm text-muted-foreground">{{ tickets.meta.total }} tickets</span>
        </div>

        <!-- Filters -->
        <div class="flex flex-wrap gap-3 rounded-lg border bg-card p-3">
            <div class="flex items-center gap-2">
                <label class="text-sm text-muted-foreground">Scope</label>
                <select
                    v-model="selectedScope"
                    class="h-8 rounded border border-input bg-background px-2 text-sm"
                    @change="applyFilters"
                >
                    <option value="all">All Tickets</option>
                    <option value="mine">Assigned to me</option>
                    <option value="unassigned">Unassigned</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-muted-foreground">Status</label>
                <select
                    v-model="selectedStatus"
                    class="h-8 rounded border border-input bg-background px-2 text-sm"
                    @change="applyFilters"
                >
                    <option value="">Active</option>
                    <option v-for="s in statuses" :key="s.value" :value="s.value">{{ s.label }}</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <label class="text-sm text-muted-foreground">Assignee</label>
                <select
                    v-model="selectedAssignee"
                    class="h-8 rounded border border-input bg-background px-2 text-sm"
                    @change="applyFilters"
                >
                    <option value="">Any</option>
                    <option v-for="u in staffUsers" :key="u.id" :value="String(u.id)">
                        {{ u.display_name ?? u.name }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Ticket table -->
        <div class="overflow-hidden rounded-lg border bg-card shadow-sm">
            <table class="w-full text-sm">
                <thead class="border-b bg-muted/50 text-left text-xs uppercase tracking-wider text-muted-foreground">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Subject</th>
                        <th class="px-4 py-3">Requester</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Priority</th>
                        <th class="px-4 py-3">Assignee</th>
                        <th class="px-4 py-3">Updated</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="ticket in tickets.data"
                        :key="ticket.id"
                        class="hover:bg-muted/30"
                    >
                        <td class="px-4 py-3 text-muted-foreground">{{ ticket.id }}</td>
                        <td class="max-w-xs px-4 py-3">
                            <Link :href="show(ticket.id)" class="font-medium hover:underline">
                                {{ ticket.subject }}
                            </Link>
                            <span v-if="ticket.category" class="ml-2 text-xs text-muted-foreground">{{ ticket.category }}</span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{ ticket.requester.display_name ?? ticket.requester.name }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium" :class="statusColor[ticket.status]">
                                {{ statuses.find(s => s.value === ticket.status)?.label ?? ticket.status }}
                            </span>
                        </td>
                        <td class="px-4 py-3" :class="priorityColor[ticket.priority]">
                            {{ ticket.priority }}
                        </td>
                        <td class="px-4 py-3">
                            <select
                                class="h-7 rounded border border-input bg-background px-1.5 text-xs"
                                :value="ticket.assignee?.id ?? ''"
                                @change="(e) => assignTicket(ticket.id, (e.target as HTMLSelectElement).value)"
                            >
                                <option value="">Unassigned</option>
                                <option v-for="u in staffUsers" :key="u.id" :value="u.id">
                                    {{ u.display_name ?? u.name }}
                                </option>
                            </select>
                        </td>
                        <td class="px-4 py-3 text-xs text-muted-foreground">
                            {{ new Date(ticket.updated_at).toLocaleDateString() }}
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="tickets.data.length === 0" class="py-12 text-center text-muted-foreground">
                No tickets match the current filters.
            </div>
        </div>

        <!-- Pagination -->
        <div v-if="tickets.meta.last_page > 1" class="flex justify-center gap-1">
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
