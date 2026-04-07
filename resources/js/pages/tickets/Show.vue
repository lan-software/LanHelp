<script setup lang="ts">
import { Form, Head, Link, setLayoutProps, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Textarea } from '@/components/ui/textarea';
import { index, show } from '@/routes/tickets';
import { store as storeReply } from '@/routes/tickets/replies';
import { update as updateStatus } from '@/routes/tickets/status';

type User = {
    id: number;
    name: string;
    display_name: string | null;
    avatar_url: string | null;
};
type Reply = {
    id: number;
    body: string;
    is_internal: boolean;
    author: User;
    created_at: string;
};
type Ticket = {
    id: number;
    subject: string;
    description: string;
    status: string;
    priority: string;
    category: string | null;
    context_snapshot: Record<string, unknown> | null;
    created_at: string;
    updated_at: string;
    resolved_at: string | null;
    requester: User;
    assignee: User | null;
};
type Status = { value: string; label: string };

const props = defineProps<{
    ticket: Ticket;
    replies: Reply[];
    statuses: Status[];
    canReply: boolean;
    canUpdateStatus: boolean;
    canAssign: boolean;
}>();

setLayoutProps({
    breadcrumbs: [
        { title: 'My Tickets', href: index() },
        { title: `#${props.ticket.id}`, href: show(props.ticket.id) },
    ],
});

const page = usePage();
const currentUser = computed(() => page.props.auth.user as User);
const isStaff = computed(
    () =>
        (page.props.auth.user as any)?.role === 'staff' ||
        (page.props.auth.user as any)?.role === 'admin',
);

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
    low: 'text-gray-500',
    normal: 'text-blue-600',
    high: 'text-orange-600',
    urgent: 'text-red-600 font-semibold',
};

function formatDate(dt: string) {
    return new Date(dt).toLocaleString();
}

const contextEntries = computed(() => {
    const snap = props.ticket.context_snapshot;
    if (!snap) return [];
    return Object.entries(snap).filter(
        ([, v]) => v !== null && v !== undefined && typeof v !== 'object',
    );
});

const contextLinks = computed(() => {
    const snap = props.ticket.context_snapshot;
    if (!snap || typeof snap.links !== 'object') return [];
    return Object.entries(snap.links as Record<string, string>);
});
</script>

<template>
    <Head :title="`#${ticket.id} — ${ticket.subject}`" />

    <div class="mx-auto max-w-4xl p-4">
        <!-- Header -->
        <div
            class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"
        >
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-sm text-muted-foreground"
                        >#{{ ticket.id }}</span
                    >
                    <span
                        class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                        :class="statusColor[ticket.status] ?? 'bg-gray-100'"
                    >
                        {{
                            statuses.find((s) => s.value === ticket.status)
                                ?.label ?? ticket.status
                        }}
                    </span>
                    <span
                        class="text-sm"
                        :class="priorityColor[ticket.priority]"
                    >
                        {{ ticket.priority }} priority
                    </span>
                </div>
                <h1 class="mt-1 text-2xl font-semibold">
                    {{ ticket.subject }}
                </h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Opened by
                    {{
                        ticket.requester.display_name ?? ticket.requester.name
                    }}
                    · {{ formatDate(ticket.created_at) }}
                    <span v-if="ticket.assignee">
                        · Assigned to
                        {{
                            ticket.assignee.display_name ?? ticket.assignee.name
                        }}</span
                    >
                    <span v-if="ticket.category"> · {{ ticket.category }}</span>
                </p>
            </div>

            <!-- Status change (staff or requester if policy allows) -->
            <div v-if="canUpdateStatus" class="flex shrink-0 gap-2">
                <Form v-bind="updateStatus.form(ticket.id)" class="flex gap-2">
                    <select
                        name="status"
                        :value="ticket.status"
                        class="h-8 rounded border border-input bg-background px-2 text-sm"
                    >
                        <option
                            v-for="s in statuses"
                            :key="s.value"
                            :value="s.value"
                        >
                            {{ s.label }}
                        </option>
                    </select>
                    <Button type="submit" size="sm" variant="outline"
                        >Update</Button
                    >
                </Form>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <!-- Conversation column -->
            <div class="lg:col-span-2">
                <!-- Original description -->
                <div class="rounded-lg border bg-card p-4 shadow-sm">
                    <div class="mb-2 flex items-center gap-2">
                        <span class="font-medium">{{
                            ticket.requester.display_name ??
                            ticket.requester.name
                        }}</span>
                        <span class="text-xs text-muted-foreground">{{
                            formatDate(ticket.created_at)
                        }}</span>
                    </div>
                    <p class="text-sm whitespace-pre-wrap">
                        {{ ticket.description }}
                    </p>
                </div>

                <!-- Replies -->
                <div
                    v-for="reply in replies"
                    :key="reply.id"
                    class="mt-3 rounded-lg border p-4 shadow-sm"
                    :class="
                        reply.is_internal
                            ? 'border-yellow-300 bg-yellow-50 dark:border-yellow-700 dark:bg-yellow-950'
                            : 'bg-card'
                    "
                >
                    <div class="mb-2 flex items-center gap-2">
                        <span class="font-medium">{{
                            reply.author.display_name ?? reply.author.name
                        }}</span>
                        <span class="text-xs text-muted-foreground">{{
                            formatDate(reply.created_at)
                        }}</span>
                        <span
                            v-if="reply.is_internal"
                            class="rounded-full bg-yellow-200 px-2 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-800 dark:text-yellow-200"
                        >
                            Internal note
                        </span>
                    </div>
                    <p class="text-sm whitespace-pre-wrap">{{ reply.body }}</p>
                </div>

                <!-- Reply form -->
                <div v-if="canReply" class="mt-4">
                    <Form
                        v-bind="storeReply.form(ticket.id)"
                        v-slot="{ errors, processing }"
                        class="flex flex-col gap-3 rounded-lg border bg-card p-4 shadow-sm"
                    >
                        <h3 class="font-medium">Add Reply</h3>
                        <Textarea
                            name="body"
                            placeholder="Write your reply…"
                            rows="4"
                            required
                        />
                        <InputError :message="errors.body" />
                        <div class="flex flex-col gap-3">
                            <div
                                v-if="isStaff"
                                class="flex flex-wrap items-center gap-4"
                            >
                                <label class="flex items-center gap-2 text-sm">
                                    <input
                                        type="checkbox"
                                        name="is_internal"
                                        value="1"
                                        class="rounded"
                                    />
                                    Internal note (staff only)
                                </label>
                                <div class="flex items-center gap-2 text-sm">
                                    <span class="text-muted-foreground"
                                        >Email notification:</span
                                    >
                                    <label class="flex items-center gap-1">
                                        <input
                                            type="radio"
                                            name="notify_mode"
                                            value="link"
                                            checked
                                            class="rounded"
                                        />
                                        Link only
                                    </label>
                                    <label class="flex items-center gap-1">
                                        <input
                                            type="radio"
                                            name="notify_mode"
                                            value="content"
                                            class="rounded"
                                        />
                                        Include content
                                    </label>
                                </div>
                            </div>
                            <div class="flex justify-end">
                                <Button type="submit" :disabled="processing">
                                    {{ processing ? 'Sending…' : 'Send Reply' }}
                                </Button>
                            </div>
                        </div>
                    </Form>
                </div>
                <p
                    v-else-if="['resolved', 'closed'].includes(ticket.status)"
                    class="mt-4 text-center text-sm text-muted-foreground"
                >
                    This ticket is {{ ticket.status }}.
                    <Link :href="index()" class="underline"
                        >Open a new ticket</Link
                    >
                    if you need more help.
                </p>
            </div>

            <!-- Sidebar column -->
            <div class="flex flex-col gap-4">
                <!-- Context snapshot -->
                <div
                    v-if="
                        ticket.context_snapshot &&
                        (contextEntries.length > 0 || contextLinks.length > 0)
                    "
                    class="rounded-lg border bg-card p-4 shadow-sm"
                >
                    <h2
                        class="mb-3 text-sm font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Context Snapshot
                    </h2>
                    <dl class="flex flex-col gap-2 text-sm">
                        <div
                            v-for="[key, val] in contextEntries"
                            :key="key"
                            class="flex flex-col"
                        >
                            <dt
                                class="text-xs text-muted-foreground capitalize"
                            >
                                {{ key.replace(/_/g, ' ') }}
                            </dt>
                            <dd class="font-medium">{{ val }}</dd>
                        </div>
                    </dl>
                    <div
                        v-if="contextLinks.length > 0"
                        class="mt-3 flex flex-col gap-1"
                    >
                        <p class="text-xs text-muted-foreground">Links</p>
                        <a
                            v-for="[name, href] in contextLinks"
                            :key="name"
                            :href="href"
                            target="_blank"
                            rel="noopener"
                            class="text-sm text-primary capitalize underline"
                        >
                            {{ name }}
                        </a>
                    </div>
                </div>

                <!-- Ticket meta -->
                <div class="rounded-lg border bg-card p-4 text-sm shadow-sm">
                    <h2
                        class="mb-3 text-sm font-semibold tracking-wider text-muted-foreground uppercase"
                    >
                        Details
                    </h2>
                    <dl class="flex flex-col gap-2">
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Status</dt>
                            <dd>
                                {{
                                    statuses.find(
                                        (s) => s.value === ticket.status,
                                    )?.label
                                }}
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-muted-foreground">Priority</dt>
                            <dd :class="priorityColor[ticket.priority]">
                                {{ ticket.priority }}
                            </dd>
                        </div>
                        <div
                            v-if="ticket.assignee"
                            class="flex justify-between"
                        >
                            <dt class="text-muted-foreground">Assignee</dt>
                            <dd>
                                {{
                                    ticket.assignee.display_name ??
                                    ticket.assignee.name
                                }}
                            </dd>
                        </div>
                        <div
                            v-if="ticket.resolved_at"
                            class="flex justify-between"
                        >
                            <dt class="text-muted-foreground">Resolved</dt>
                            <dd>{{ formatDate(ticket.resolved_at) }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</template>
