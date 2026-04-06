<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { BookOpen, ClipboardList, HelpCircle, LayoutGrid, Settings, Shield } from 'lucide-vue-next';
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
import { index as ticketsIndex } from '@/routes/tickets';
import { index as kbIndex } from '@/routes/kb';
import { index as staffTicketsIndex } from '@/routes/staff/tickets';
import { index as adminKbIndex } from '@/routes/admin/kb';
import type { NavItem } from '@/types';

const page = usePage();
const user = computed(() => page.props.auth?.user as any);
const isStaff = computed(() => user.value?.role === 'staff' || user.value?.role === 'admin');
const isAdmin = computed(() => user.value?.role === 'admin');

const mainNavItems = computed((): NavItem[] => {
    const items: NavItem[] = [];
    if (isAdmin.value) {
        items.push({ title: 'Dashboard', href: dashboard(), icon: LayoutGrid });
    }
    items.push(
        { title: 'My Tickets', href: ticketsIndex(), icon: HelpCircle },
        { title: 'Knowledge Base', href: kbIndex(), icon: BookOpen },
    );
    return items;
});

const staffNavItems = computed((): NavItem[] => {
    if (!isStaff.value) return [];
    const items: NavItem[] = [
        { title: 'Staff Board', href: staffTicketsIndex(), icon: ClipboardList },
    ];
    if (isAdmin.value) {
        items.push({ title: 'Manage KB', href: adminKbIndex(), icon: Shield });
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
