<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/vue3';
import { BookOpen, Folder, LayoutGrid, Users, UserCheck, GraduationCap, CreditCard, Megaphone, PenTool, Settings, Image, Sparkles, School, Info, PhoneCall } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
    },
    {
        title: 'Students',
        href: '/students',
        icon: Users,
    },
    {
        title: 'Teachers',
        href: '/teachers',
        icon: UserCheck,
    },
    {
        title: 'Academic Programs',
        href: '/programs',
        icon: School,
    },
       {
        title: 'Subjects Config',
        href: '/subjects',
        icon: BookOpen,
    },
    {
        title: 'Semesters & Exams',
        href: '/semesters',
        icon: Settings,
    },
    {
        title: 'Academic Results',
        href: '/results',
        icon: GraduationCap,
    },
    {
        title: 'Enter Marks',
        href: '/results/marks-entry',
        icon: PenTool,
    },
    {
        title: 'Tuition Fees',
        href: '/fees',
        icon: CreditCard,
    },
    {
        title: 'Notice Board',
        href: '/notices',
        icon: Megaphone,
    },
    {
        title: 'Hero Banners',
        href: '/banners',
        icon: Image,
    },
    {
        title: 'Campus Life Gallery',
        href: '/campus-life',
        icon: Sparkles,
    },
    {
        title: 'About Us',
        href: '/about-us',
        icon: Info,
    },
    {
        title: 'Contact Us',
        href: '/contact-us',
        icon: PhoneCall,
    },
    {
        title: 'Settings',
        href: '/settings/profile',
        icon: Settings,
    },

];


</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader class="p-4">
            <Link :href="route('dashboard')" class="flex items-center gap-3 px-2 py-1.5 overflow-hidden">
                <AppLogo />
            </Link>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
