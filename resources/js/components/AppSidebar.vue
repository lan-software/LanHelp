<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookOpen,
    ClipboardList,
    HelpCircle,
    LayoutGrid,
    Shield,
} from 'lucide-vue-next';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard, home } from '@/routes';
import { index as adminKbIndex } from '@/routes/admin/kb';
import { index as kbIndex } from '@/routes/kb';
import { index as staffTicketsIndex } from '@/routes/staff/tickets';
import { index as ticketsIndex } from '@/routes/tickets';
import type { NavItem } from '@/types';

const { t } = useI18n();
const page = usePage();
const user = computed(() => page.props.auth?.user as any);
const isStaff = computed(
    () => user.value?.role === 'staff' || user.value?.role === 'admin',
);
const isAdmin = computed(() => user.value?.role === 'admin');

const mainNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [];

    if (isAdmin.value) {
        items.push({
            title: t('navigation.dashboard'),
            href: dashboard(),
            icon: LayoutGrid,
        });
    }

    items.push(
        {
            title: t('navigation.myTickets'),
            href: ticketsIndex(),
            icon: HelpCircle,
        },
        {
            title: t('navigation.knowledgeBase'),
            href: kbIndex(),
            icon: BookOpen,
        },
    );

    return items;
});

const staffNavItems = computed((): NavItem[] => {
    if (!isStaff.value) {
        return [];
    }

    const items: NavItem[] = [
        {
            title: t('navigation.staffBoard'),
            href: staffTicketsIndex(),
            icon: ClipboardList,
        },
    ];

    if (isAdmin.value) {
        items.push({
            title: t('navigation.manageKb'),
            href: adminKbIndex(),
            icon: Shield,
        });
    }

    return items;
});

const footerNavItems: NavItem[] = [];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="home()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavMain v-if="staffNavItems.length > 0" :items="staffNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
