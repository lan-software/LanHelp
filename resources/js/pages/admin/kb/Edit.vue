<script setup lang="ts">
import { Form, Head, Link, setLayoutProps } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { index, create, store, update } from '@/routes/admin/kb';

const { t } = useI18n();

type Article = {
    id: string;
    title: string;
    content: string;
    excerpt: string | null;
    category: string | null;
    is_published: boolean;
} | null;

const props = defineProps<{ article: Article }>();

const isEdit = props.article !== null;

setLayoutProps({
    breadcrumbs: [
        { title: t('kb.admin.breadcrumb'), href: index() },
        {
            title: isEdit
                ? t('kb.admin.editBreadcrumb')
                : t('kb.admin.newBreadcrumb'),
            href: isEdit ? '#' : create(),
        },
    ],
});

const formAction = isEdit ? update.form(props.article!.id) : store.form();
</script>

<template>
    <Head
        :title="isEdit ? $t('kb.admin.editArticle') : $t('kb.admin.newArticle')"
    />

    <div class="mx-auto max-w-3xl p-4">
        <h1 class="mb-6 text-2xl font-semibold">
            {{
                isEdit ? $t('kb.admin.editArticle') : $t('kb.admin.newArticle')
            }}
        </h1>

        <Form
            v-bind="formAction"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-5"
        >
            <div class="flex flex-col gap-1.5">
                <Label for="title"
                    >{{ $t('kb.admin.titleLabel') }}
                    <span class="text-destructive">*</span></Label
                >
                <Input
                    id="title"
                    name="title"
                    :value="article?.title ?? ''"
                    required
                />
                <InputError :message="errors.title" />
            </div>

            <div class="flex flex-col gap-1.5">
                <Label for="excerpt">{{ $t('kb.admin.excerptLabel') }}</Label>
                <Textarea
                    id="excerpt"
                    name="excerpt"
                    rows="2"
                    :value="article?.excerpt ?? ''"
                    :placeholder="$t('kb.admin.excerptPlaceholder')"
                />
                <InputError :message="errors.excerpt" />
            </div>

            <div class="flex flex-col gap-1.5">
                <Label for="content"
                    >{{ $t('kb.admin.contentLabel') }}
                    <span class="text-destructive">*</span></Label
                >
                <Textarea
                    id="content"
                    name="content"
                    rows="16"
                    :value="article?.content ?? ''"
                    required
                />
                <InputError :message="errors.content" />
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div class="flex flex-col gap-1.5">
                    <Label for="category">{{
                        $t('kb.admin.categoryLabel')
                    }}</Label>
                    <Input
                        id="category"
                        name="category"
                        :value="article?.category ?? ''"
                        :placeholder="$t('kb.admin.categoryPlaceholder')"
                    />
                    <InputError :message="errors.category" />
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <input
                        id="is_published"
                        type="checkbox"
                        name="is_published"
                        value="1"
                        :checked="article?.is_published ?? false"
                        class="rounded"
                    />
                    <Label for="is_published">{{
                        $t('kb.admin.publishedLabel')
                    }}</Label>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <Link
                    :href="index()"
                    class="text-sm text-muted-foreground hover:underline"
                    >{{ $t('common.cancel') }}</Link
                >
                <Button type="submit" :disabled="processing">
                    {{
                        processing
                            ? $t('kb.admin.saving')
                            : isEdit
                              ? $t('kb.admin.saveChanges')
                              : $t('kb.admin.createArticle')
                    }}
                </Button>
            </div>
        </Form>
    </div>
</template>
