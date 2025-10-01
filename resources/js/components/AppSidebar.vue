<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, BookUser, Users, Package2, ChartNoAxesCombined, Archive, Settings } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const { props: pageProps } = usePage();
const user = computed(() => pageProps.auth?.user);

const mainNavItems: NavItem[] = computed(() => {
    const baseItems: NavItem[] = [
      
    ];

    if (!user.value?.is_admin) {
        baseItems.push(
            {
                title: 'Profile',
                href: '/profile',
                icon: BookOpen,
            }
        );
        return baseItems;
    }

    return [
        {
            title: 'Dashboard',
            href: '/dashboard',
            icon: LayoutGrid,
        },
        {
            title: 'Employee',
            href: '/employee',
            icon: BookUser,
        },
        {
            title: 'Inventory',
            href: '/inventory',
            icon: Package2,
        },
        {
            title: 'Report',
            href: '/report',
            icon: ChartNoAxesCombined,
        },
        {
            title: 'Users',
            href: '/users',
            icon: Users,
        },
        {
            title: 'Archived',
            href: '/archived',
            icon: Archive,
        },
    ];
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset" class="bg-gradient-to-b from-gray-700 to-gray-900">
        <SidebarHeader class="bg-gradient-to-b from-gray-700 to-gray-900">
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="2xl" as-child class="text-white">
                        <Link :href="route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent class="bg-gradient-to-b from-gray-700 to-gray-900">
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter class="bg-gradient-to-b from-gray-700 to-gray-900">
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>