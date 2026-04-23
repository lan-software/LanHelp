<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import AppearanceTabs from '@/components/AppearanceTabs.vue';
import Heading from '@/components/Heading.vue';
import { edit } from '@/routes/appearance';

const { t } = useI18n();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: () => t('settings.appearance.layoutBreadcrumb'),
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
