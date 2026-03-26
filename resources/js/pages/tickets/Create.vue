<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { index, create, store } from '@/routes/tickets';

type Priority = { value: string; label: string };

defineProps<{ priorities: Priority[] }>();

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

        <Form
            v-bind="store.form()"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="flex flex-col gap-1.5">
                <Label for="subject">Subject <span class="text-destructive">*</span></Label>
                <Input id="subject" name="subject" placeholder="Briefly describe your issue" required />
                <InputError :message="errors.subject" />
            </div>

            <div class="flex flex-col gap-1.5">
                <Label for="description">Description <span class="text-destructive">*</span></Label>
                <Textarea
                    id="description"
                    name="description"
                    placeholder="Provide as much detail as possible..."
                    rows="6"
                    required
                />
                <InputError :message="errors.description" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <Label for="priority">Priority</Label>
                    <select
                        id="priority"
                        name="priority"
                        class="flex h-9 w-full rounded-md border border-input bg-transparent px-3 py-1 text-sm shadow-sm transition-colors focus:outline-none focus:ring-1 focus:ring-ring"
                    >
                        <option v-for="p in priorities" :key="p.value" :value="p.value">{{ p.label }}</option>
                    </select>
                    <InputError :message="errors.priority" />
                </div>

                <div class="flex flex-col gap-1.5">
                    <Label for="category">Category</Label>
                    <Input id="category" name="category" placeholder="e.g. account, technical" />
                    <InputError :message="errors.category" />
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <Button type="submit" :disabled="processing">
                    {{ processing ? 'Submitting…' : 'Submit Ticket' }}
                </Button>
            </div>
        </Form>
    </div>
</template>
