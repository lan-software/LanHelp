<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import i18n from '@/i18n';
import { logout } from '@/routes';
import { send } from '@/routes/verification';

defineOptions({
    layout: {
        title: () => i18n.global.t('auth.verifyEmail.layoutTitle'),
        description: () => i18n.global.t('auth.verifyEmail.layoutDescription'),
    },
});

defineProps<{
    status?: string;
}>();
</script>

<template>
    <Head :title="$t('auth.verifyEmail.headTitle')" />

    <div
        v-if="status === 'verification-link-sent'"
        class="mb-4 text-center text-sm font-medium text-green-600"
    >
        {{ $t('auth.verifyEmail.linkSent') }}
    </div>

    <Form
        v-bind="send.form()"
        class="space-y-6 text-center"
        v-slot="{ processing }"
    >
        <Button :disabled="processing" variant="secondary">
            <Spinner v-if="processing" />
            {{ $t('auth.verifyEmail.resendButton') }}
        </Button>

        <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
            {{ $t('auth.verifyEmail.logout') }}
        </TextLink>
    </Form>
</template>
