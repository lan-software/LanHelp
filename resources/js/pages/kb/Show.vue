<script setup lang="ts">
import { Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { index } from '@/routes/kb';

type Article = {
    id: number;
    title: string;
    slug: string;
    content: string;
    category: string | null;
    published_at: string;
    author: { name: string; display_name: string | null };
};

const props = defineProps<{ article: Article }>();

setLayoutProps({
    breadcrumbs: [
        { title: 'Knowledge Base', href: index() },
        { title: props.article.title, href: '#' },
    ],
});
</script>

<template>
    <Head :title="article.title" />

    <div class="mx-auto max-w-3xl p-4">
        <div class="mb-2 flex items-center gap-2 text-sm text-muted-foreground">
            <Link :href="index()" class="hover:underline">Knowledge Base</Link>
            <span>›</span>
            <span v-if="article.category" class="capitalize">{{
                article.category
            }}</span>
        </div>

        <h1 class="mb-2 text-3xl font-bold">{{ article.title }}</h1>

        <p class="mb-8 text-sm text-muted-foreground">
            By {{ article.author.display_name ?? article.author.name }} ·
            {{ new Date(article.published_at).toLocaleDateString() }}
        </p>

        <div
            class="prose prose-sm dark:prose-invert max-w-none whitespace-pre-wrap"
        >
            {{ article.content }}
        </div>

        <div class="mt-10 border-t pt-6">
            <Link :href="index()" class="text-sm text-primary hover:underline"
                >← Back to Knowledge Base</Link
            >
        </div>
    </div>
</template>
