<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import i18n from '@/i18n';
import { edit } from '@/routes/appearance';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: () => i18n.global.t('settings.appearance.layoutBreadcrumb'),
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="$t('settings.appearance.headTitle')" />

    <h1 class="sr-only">{{ $t('settings.appearance.headTitle') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="$t('settings.appearance.sectionTitle')"
            :description="$t('settings.appearance.sectionDescription')"
        />
        <AppearanceTabs />
    </div>
</template>
