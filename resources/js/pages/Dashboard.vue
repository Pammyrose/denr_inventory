<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Sample data (replace with actual data from your backend)
const dashboardData = {
    employees: 45,
    users: 120,
    assignedAssets: 75,
    assets: 100,
    assignedPerDepartment: [
        { name: 'IT', value: 30 },
        { name: 'HR', value: 20 },
        { name: 'Finance', value: 15 },
        { name: 'Operations', value: 10 },
    ],
    purchasedAssets: [
        { month: 'Jan', count: 10 },
        { month: 'Feb', count: 15 },
        { month: 'Mar', count: 8 },
        { month: 'Apr', count: 20 },
        { month: 'May', count: 12 },
        { month: 'Jun', count: 18 },
    ],
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
            <!-- First Row: 4-column grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-[#ef4444] rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col items-center justify-between h-24">
                    <h3 class="text-lg font-semibold text-gray-100 ">Employees</h3>
                    <span class="text-2xl font-bold text-gray-100">{{ dashboardData.employees }}</span>
                </div>
                <div class="bg-[#f59e0b] rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col items-center justify-between h-24">
                    <h3 class="text-lg font-semibold text-gray-100">Users</h3>
                    <span class="text-2xl font-bold text-gray-100">{{ dashboardData.users }}</span>
                </div>
                <div class="bg-[#10b981] rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col items-center justify-between h-24">
                    <h3 class="text-lg font-semibold text-gray-100">Assigned Assets</h3>
                    <span class="text-2xl font-bold text-gray-100">{{ dashboardData.assignedAssets }}</span>
                </div>
                <div class="bg-[#3b82f6] rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col items-center justify-between h-24">
                    <h3 class="text-lg font-semibold text-gray-100">Assets</h3>
                    <span class="text-2xl font-bold text-gray-100">{{ dashboardData.assets }}</span>
                </div>
            </div>

            <!-- Second Row: Pie chart and Line chart -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Pie Chart for Assigned Assets per Department -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assigned Assets per Department</h3>
                    <div class="relative aspect-[4/3] max-w-[250px] mx-auto">
                        <!-- Placeholder SVG for Pie Chart -->
                        <svg viewBox="0 0 100 100" class="w-full h-full">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#3b82f6" stroke-width="20" stroke-dasharray="60 251.2" />
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="20" stroke-dasharray="50 251.2" transform="rotate(90 50 50)" />
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f59e0b" stroke-width="20" stroke-dasharray="40 251.2" transform="rotate(180 50 50)" />
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#ef4444" stroke-width="20" stroke-dasharray="30 251.2" transform="rotate(270 50 50)" />
                        </svg>
                        <!-- Legend -->
                        <div class="mt-4 flex flex-wrap justify-center gap-4">
                            <div v-for="dept in dashboardData.assignedPerDepartment" :key="dept.name" class="flex items-center">
                                <div class="w-4 h-4 mr-2" :style="{ backgroundColor: getDepartmentColor(dept.name) }"></div>
                                <span class="text-sm text-gray-900 dark:text-white">{{ dept.name }}: {{ dept.value }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Line Chart for Purchased Assets -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Purchased Assets Over Time</h3>
                    <div class="relative aspect-[5/3] max-w-[600px]">
                        <!-- Placeholder SVG for Line Chart -->
                        <svg viewBox="0 0 600 150" class="w-full h-full">
                            <polyline
                                fill="none"
                                stroke="#3b82f6"
                                stroke-width="2"
                                :points="dashboardData.purchasedAssets.map((item, index) => `${(index * 600) / (dashboardData.purchasedAssets.length - 1)},${150 - item.count * 6}`).join(' ')"
                            />
                            <!-- X-axis labels -->
                            <text v-for="(item, index) in dashboardData.purchasedAssets" :key="index" :x="(index * 600) / (dashboardData.purchasedAssets.length - 1)" y="140" text-anchor="middle" class="text-sm text-gray-900 dark:text-white">{{ item.month }}</text>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script lang="ts">
export default {
    methods: {
        getDepartmentColor(department: string): string {
            const colors: { [key: string]: string } = {
                IT: '#3b82f6',
                HR: '#10b981',
                Finance: '#f59e0b',
                Operations: '#ef4444',
            };
            return colors[department] || '#6b7280';
        },
    },
};
</script>