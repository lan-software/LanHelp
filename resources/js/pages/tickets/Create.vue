<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index, create, store } from '@/routes/tickets';

type Priority = { value: string; label: string };

type ContextSnapshot = {
    source_product?: string;
    source_domain?: string;
    event_reference?: string;
    seat_reference?: string;
    tournament_reference?: string;
    order_reference?: string;
    team_reference?: string;
    links?: string[];
};

type Prefill = {
    subject: string;
    category: string;
    context_snapshot: ContextSnapshot;
};

const props = defineProps<{
    priorities: Priority[];
    prefill: Prefill;
}>();

const form = useForm({
    subject: props.prefill.subject,
    description: '',
    priority: 'normal',
    category: props.prefill.category,
    context_snapshot: props.prefill.context_snapshot,
});

function submit() {
    form.post(store().url);
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'My Tickets', href: index() },
            { title: 'New Ticket', href: create() },
        ],
    },
});
</script>

<template>
    <Head title="New Ticket" />

    <div class="mx-auto max-w-2xl p-4">
        <h1 class="mb-6 text-2xl font-semibold">Open a Support Request</h1>

        <form class="flex flex-col gap-5" @submit.prevent="submit">
            <div class="flex flex-col gap-1.5">
                <Label for="subject"
                    >Subject <span class="text-destructive">*</span></Label
                >
                <Input
                    id="subject"
                    v-model="form.subject"
                    name="subject"
                    placeholder="Briefly describe your issue"
                    required
                />
                <InputError :message="form.errors.subject" />
            </div>

            <div class="flex flex-col gap-1.5">
                <Label for="description"
                    >Description <span class="text-destructive">*</span></Label
                >
                <Textarea
                    id="description"
                    v-model="form.description"
                    name="description"
                    placeholder="Provide as much detail as possible..."
                    rows="6"
                    required
                />
                <InputError :message="form.errors.description" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <Label for="priority">Priority</Label>
                    <select
                        id="priority"
                        v-model="form.priority"
                        name="priority"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:ring-1 focus:ring-ring focus:outline-none"
                    >
                        <option
                            v-for="p in priorities"
                            :key="p.value"
                            :value="p.value"
                        >
                            {{ p.label }}
                        </option>
                    </select>
                    <InputError :message="form.errors.priority" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <Label for="category">Category</Label>
                    <Input
                        id="category"
                        v-model="form.category"
                        name="category"
                        placeholder="e.g. account, technical"
                    />
                    <InputError :message="form.errors.category" />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Submitting…' : 'Submit Ticket' }}
                </Button>
            </div>
        </form>
    </div>
</template>
