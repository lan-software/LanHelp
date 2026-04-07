<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { index, create, edit, destroy } from '@/routes/admin/kb';

type Article = {
    id: number;
    title: string;
    slug: string;
    category: string | null;
    is_published: boolean;
    published_at: string | null;
    updated_at: string;
    author: { name: string; display_name: string | null };
};
type PaginatedArticles = {
    data: Article[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
};

defineProps<{ articles: PaginatedArticles }>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Admin: Knowledge Base', href: index() }],
    },
});
</script>

<template>
    <Head title="Admin: Knowledge Base" />

    <div class="flex flex-col gap-4 p-4">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold">Knowledge Base</h1>
            <Button as-child>
                <Link :href="create()">New Article</Link>
            </Button>
        </div>

        <div class="overflow-hidden rounded-lg border bg-card shadow-sm">
            <table class="w-full text-sm">
                <thead
                    class="border-b bg-muted/50 text-left text-xs tracking-wider text-muted-foreground uppercase"
                >
                    <tr>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Author</th>
                        <th class="px-4 py-3">Updated</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <tr
                        v-for="article in articles.data"
                        :key="article.id"
                        class="hover:bg-muted/30"
                    >
                        <td class="max-w-sm px-4 py-3">
                            <span class="font-medium">{{ article.title }}</span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground capitalize">
                            {{ article.category ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span
                                class="rounded-full px-2 py-0.5 text-xs font-medium"
                                :class="
                                    article.is_published
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200'
                                        : 'bg-gray-100 text-gray-600'
                                "
                            >
                                {{
                                    article.is_published ? 'Published' : 'Draft'
                                }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-muted-foreground">
                            {{
                                article.author.display_name ??
                                article.author.name
                            }}
                        </td>
                        <td class="px-4 py-3 text-xs text-muted-foreground">
                            {{
                                new Date(
                                    article.updated_at,
                                ).toLocaleDateString()
                            }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <Link
                                    :href="edit(article.id)"
                                    class="text-xs text-primary hover:underline"
                                    >Edit</Link
                                >
                                <Form
                                    v-bind="destroy.form(article.id)"
                                    class="inline"
                                >
                                    <button
                                        type="submit"
                                        class="text-xs text-destructive hover:underline"
                                        @click.prevent="
                                            $event.currentTarget
                                                .closest('form')
                                                ?.requestSubmit()
                                        "
                                    >
                                        Delete
                                    </button>
                                </Form>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div
                v-if="articles.data.length === 0"
                class="py-12 text-center text-muted-foreground"
            >
                No articles yet.
            </div>
        </div>

        <div v-if="articles.last_page > 1" class="flex justify-center gap-1">
            <template v-for="link in articles.links" :key="link.label">
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
</template>
