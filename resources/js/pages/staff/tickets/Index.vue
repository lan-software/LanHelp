<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, h } from 'vue';
import {
    useVueTable,
    getCoreRowModel,
    FlexRender,
    type ColumnDef,
} from '@tanstack/vue-table';
import { ArrowUpDown } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { index, assign } from '@/routes/staff/tickets';
import { show } from '@/routes/tickets';

type User = { id: number; name: string; display_name: string | null };
type Ticket = {
    id: number;
    subject: string;
    status: string;
    priority: string;
    category: string | null;
    created_at: string;
    updated_at: string;
    requester: User;
    assignee: User | null;
};
type PaginatedTickets = {
    data: Ticket[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
};
type Status = { value: string; label: string };

const props = defineProps<{
    tickets: PaginatedTickets;
    staffUsers: User[];
    statuses: Status[];
    filters: {
        status: string | null;
        assignee: string | null;
        scope: string;
        search: string | null;
        sort: string;
        direction: string;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Staff Board', href: index() }],
    },
});

const statusColor: Record<string, string> = {
    open: 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
    in_progress:
        'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
    waiting_for_user:
        'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
    resolved:
        'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
    closed: 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
};

const priorityColor: Record<string, string> = {
    urgent: 'text-red-600 font-bold',
    high: 'text-orange-500',
    normal: '',
    low: 'text-gray-400',
};

const searchQuery = ref(props.filters.search ?? '');
const selectedStatus = ref(props.filters.status ?? '');
const selectedAssignee = ref(props.filters.assignee ?? '');
const selectedScope = ref(props.filters.scope ?? 'all');

function buildParams() {
    return {
        search: searchQuery.value || undefined,
        status: selectedStatus.value || undefined,
        assignee: selectedAssignee.value || undefined,
        scope: selectedScope.value !== 'all' ? selectedScope.value : undefined,
        sort:
            props.filters.sort !== 'updated_at'
                ? props.filters.sort
                : undefined,
        direction:
            props.filters.direction !== 'desc'
                ? props.filters.direction
                : undefined,
    };
}

function applyFilters() {
    router.get(index().url, buildParams(), {
        preserveState: true,
        replace: true,
    });
}

function sortBy(column: string) {
    const newDirection =
        props.filters.sort === column && props.filters.direction === 'asc'
            ? 'desc'
            : 'asc';
    router.get(
        index().url,
        {
            ...buildParams(),
            sort: column,
            direction: newDirection,
        },
        { preserveState: true, replace: true },
    );
}

let searchTimeout: ReturnType<typeof setTimeout>;
watch(searchQuery, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
});

function assignTicket(ticketId: number, assigneeId: string) {
    if (!assigneeId) return;
    router.patch(assign(ticketId).url, { assignee_id: assigneeId });
}

const columns: ColumnDef<Ticket>[] = [
    {
        accessorKey: 'id',
        header: '#',
        cell: ({ row }) => `#${row.original.id}`,
    },
    {
        accessorKey: 'subject',
        header: 'Subject',
    },
    {
        id: 'requester',
        header: 'Requester',
        accessorFn: (row) => row.requester.display_name ?? row.requester.name,
    },
    {
        accessorKey: 'status',
        header: 'Status',
    },
    {
        accessorKey: 'priority',
        header: 'Priority',
    },
    {
        id: 'assignee',
        header: 'Assignee',
    },
    {
        accessorKey: 'updated_at',
        header: 'Updated',
    },
];

const table = useVueTable({
    get data() {
        return props.tickets.data;
    },
    columns,
    getCoreRowModel: getCoreRowModel(),
    manualSorting: true,
    manualFiltering: true,
    manualPagination: true,
    pageCount: props.tickets.last_page,
});
</script>

<template>
    <Head title="Staff Board" />

    <div class="flex flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Staff Ticket Board</h1>
            <span class="text-sm text-muted-foreground"
                >{{ tickets.total }} tickets</span
            >
        </div>

        <!-- Search + Filters -->
        <div
            class="flex flex-col gap-3 rounded-lg border bg-card p-3 sm:flex-row sm:flex-wrap sm:items-center"
        >
            <Input
                v-model="searchQuery"
                placeholder="Search tickets..."
                class="h-8 w-full sm:w-64"
            />
            <div class="flex flex-wrap items-center gap-3">
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
                        <option
                            v-for="s in statuses"
                            :key="s.value"
                            :value="s.value"
                        >
                            {{ s.label }}
                        </option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <label class="text-sm text-muted-foreground"
                        >Assignee</label
                    >
                    <select
                        v-model="selectedAssignee"
                        class="h-8 rounded border border-input bg-background px-2 text-sm"
                        @change="applyFilters"
                    >
                        <option value="">Any</option>
                        <option
                            v-for="u in staffUsers"
                            :key="u.id"
                            :value="String(u.id)"
                        >
                            {{ u.display_name ?? u.name }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- DataTable -->
        <div class="overflow-hidden rounded-lg border bg-card shadow-sm">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-16">
                            <Button
                                variant="ghost"
                                size="sm"
                                class="-ml-3 h-8"
                                @click="sortBy('id')"
                            >
                                #
                                <ArrowUpDown class="ml-1 h-3.5 w-3.5" />
                            </Button>
                        </TableHead>
                        <TableHead>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="-ml-3 h-8"
                                @click="sortBy('subject')"
                            >
                                Subject
                                <ArrowUpDown class="ml-1 h-3.5 w-3.5" />
                            </Button>
                        </TableHead>
                        <TableHead>Requester</TableHead>
                        <TableHead>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="-ml-3 h-8"
                                @click="sortBy('status')"
                            >
                                Status
                                <ArrowUpDown class="ml-1 h-3.5 w-3.5" />
                            </Button>
                        </TableHead>
                        <TableHead>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="-ml-3 h-8"
                                @click="sortBy('priority')"
                            >
                                Priority
                                <ArrowUpDown class="ml-1 h-3.5 w-3.5" />
                            </Button>
                        </TableHead>
                        <TableHead>Assignee</TableHead>
                        <TableHead>
                            <Button
                                variant="ghost"
                                size="sm"
                                class="-ml-3 h-8"
                                @click="sortBy('updated_at')"
                            >
                                Updated
                                <ArrowUpDown class="ml-1 h-3.5 w-3.5" />
                            </Button>
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="table.getRowModel().rows.length === 0">
                        <TableCell
                            :colspan="7"
                            class="py-12 text-center text-muted-foreground"
                        >
                            No tickets match the current filters.
                        </TableCell>
                    </TableRow>
                    <TableRow
                        v-for="row in table.getRowModel().rows"
                        :key="row.original.id"
                        class="hover:bg-muted/30"
                    >
                        <TableCell class="text-muted-foreground"
                            >#{{ row.original.id }}</TableCell
                        >
                        <TableCell class="max-w-xs">
                            <Link
                                :href="show(row.original.id)"
                                class="font-medium hover:underline"
                            >
                                {{ row.original.subject }}
                            </Link>
                            <span
                                v-if="row.original.category"
                                class="ml-2 text-xs text-muted-foreground"
                                >{{ row.original.category }}</span
                            >
                        </TableCell>
                        <TableCell class="text-muted-foreground">
                            {{
                                row.original.requester.display_name ??
                                row.original.requester.name
                            }}
                        </TableCell>
                        <TableCell>
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="statusColor[row.original.status]"
                            >
                                {{
                                    statuses.find(
                                        (s) => s.value === row.original.status,
                                    )?.label ?? row.original.status
                                }}
                            </span>
                        </TableCell>
                        <TableCell
                            :class="priorityColor[row.original.priority]"
                            class="capitalize"
                        >
                            {{ row.original.priority }}
                        </TableCell>
                        <TableCell>
                            <select
                                class="h-7 rounded border border-input bg-background px-1.5 text-xs"
                                :value="row.original.assignee?.id ?? ''"
                                @change="
                                    (e) =>
                                        assignTicket(
                                            row.original.id,
                                            (e.target as HTMLSelectElement)
                                                .value,
                                        )
                                "
                            >
                                <option value="">Unassigned</option>
                                <option
                                    v-for="u in staffUsers"
                                    :key="u.id"
                                    :value="u.id"
                                >
                                    {{ u.display_name ?? u.name }}
                                </option>
                            </select>
                        </TableCell>
                        <TableCell class="text-xs text-muted-foreground">
                            {{
                                new Date(
                                    row.original.updated_at,
                                ).toLocaleDateString()
                            }}
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Pagination -->
        <div
            v-if="tickets.last_page > 1"
            class="flex items-center justify-between"
        >
            <p class="text-sm text-muted-foreground">
                Page {{ tickets.current_page }} of {{ tickets.last_page }}
            </p>
            <div class="flex gap-1">
                <template v-for="link in tickets.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        class="rounded border px-3 py-1 text-sm"
                        :class="
                            link.active
                                ? 'border-primary bg-primary text-primary-foreground'
                                : 'hover:bg-muted'
                        "
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="rounded border px-3 py-1 text-sm text-muted-foreground"
                        v-html="link.label"
                    />
                </template>
            </div>
        </div>
    </div>
</template>
