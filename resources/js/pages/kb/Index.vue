<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { index, show } from '@/routes/kb';

type Article = {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    category: string | null;
    published_at: string;
};

const props = defineProps<{
    articles: Article[];
    categories: string[];
    filters: { category: string | null };
}>();

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Knowledge Base', href: index() }],
    },
});

const selectedCategory = ref(props.filters.category ?? '');

function filterByCategory(cat: string) {
    selectedCategory.value = cat;
    router.get(
        index().url,
        { category: cat || undefined },
        { preserveState: true, replace: true },
    );
}
</script>

<template>
    <Head title="Knowledge Base" />

    <div class="mx-auto max-w-4xl p-4">
        <h1 class="mb-6 text-3xl font-semibold">Knowledge Base</h1>

        <!-- Category filter -->
        <div v-if="categories.length > 0" class="mb-6 flex flex-wrap gap-2">
            <button
                class="rounded-full px-3 py-1 text-sm transition-colors"
                :class="
                    selectedCategory === ''
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted hover:bg-muted/80'
                "
                @click="filterByCategory('')"
            >
                All
            </button>
            <button
                v-for="cat in categories"
                :key="cat"
                class="rounded-full px-3 py-1 text-sm capitalize transition-colors"
                :class="
                    selectedCategory === cat
                        ? 'bg-primary text-primary-foreground'
                        : 'bg-muted hover:bg-muted/80'
                "
                @click="filterByCategory(cat)"
            >
                {{ cat }}
            </button>
        </div>

        <div
            v-if="articles.length === 0"
            class="py-16 text-center text-muted-foreground"
        >
            No articles found.
        </div>

        <div v-else class="flex flex-col gap-4">
            <Link
                v-for="article in articles"
                :key="article.id"
                :href="show(article.slug)"
                class="block rounded-lg border bg-card p-5 shadow-sm transition-colors hover:bg-muted/50"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <span
                            v-if="article.category"
                            class="text-xs font-medium tracking-wider text-primary uppercase"
                        >
                            {{ article.category }}
                        </span>
                        <h2 class="mt-1 text-lg font-semibold">
                            {{ article.title }}
                        </h2>
                        <p
                            v-if="article.excerpt"
                            class="mt-1 line-clamp-2 text-sm text-muted-foreground"
                        >
                            {{ article.excerpt }}
                        </p>
                    </div>
                    <span class="shrink-0 text-xs text-muted-foreground">
                        {{
                            new Date(article.published_at).toLocaleDateString()
                        }}
                    </span>
                </div>
            </Link>
        </div>
    </div>
</template>
