
<script setup lang="ts">
import { ref } from 'vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale } from 'chart.js';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { BarChart } from 'lucide-vue-next';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale);

// Define BreadcrumbItem type
interface BreadcrumbItem {
    title: string;
    href: string;
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Report',
        href: '/report',
    },
];

// Sample data for purchased assets (July 1–31, 2025)
const purchaseData = [
    { date: '2025-07-01', quantity: 120 },
    { date: '2025-07-02', quantity: 130 },
    { date: '2025-07-03', quantity: 110 },
    { date: '2025-07-04', quantity: 140 },
    { date: '2025-07-05', quantity: 90 },
    { date: '2025-07-06', quantity: 100 },
    { date: '2025-07-07', quantity: 150 },
    { date: '2025-07-08', quantity: 160 },
    { date: '2025-07-09', quantity: 170 },
    { date: '2025-07-10', quantity: 180 },
    { date: '2025-07-11', quantity: 200 },
    { date: '2025-07-12', quantity: 190 },
    { date: '2025-07-13', quantity: 210 },
    { date: '2025-07-14', quantity: 220 },
    { date: '2025-07-15', quantity: 230 },
    { date: '2025-07-16', quantity: 240 },
    { date: '2025-07-17', quantity: 250 },
    { date: '2025-07-18', quantity: 260 },
    { date: '2025-07-19', quantity: 270 },
    { date: '2025-07-20', quantity: 280 },
    { date: '2025-07-21', quantity: 290 },
    { date: '2025-07-22', quantity: 300 },
    { date: '2025-07-23', quantity: 310 },
    { date: '2025-07-24', quantity: 320 },
    { date: '2025-07-25', quantity: 330 },
    { date: '2025-07-26', quantity: 340 },
    { date: '2025-07-27', quantity: 350 },
    { date: '2025-07-28', quantity: 360 },
    { date: '2025-07-29', quantity: 370 },
    { date: '2025-07-30', quantity: 380 },
    { date: '2025-07-31', quantity: 390 },
];

// Chart data
const chartData = ref({
    labels: purchaseData.map(item => new Date(item.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
    datasets: [
        {
            label: 'Purchased Assets',
            data: purchaseData.map(item => item.quantity),
            borderColor: '#10b981', // emerald-500
            backgroundColor: '#10b981', // For points
            borderWidth: 2,
            pointRadius: 3,
            fill: false, // No fill under the line
            tension: 0.5, // Slight curve for smoothness
        },
    ],
});

// Chart options
const chartOptions = ref({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top' as const,
        },
        title: {
            display: true,
            text: 'Daily Purchased Assets (July 2025)',
            color: '#1f2937', // gray-800
            font: {
                size: 16,
            },
        },
        tooltip: {
            callbacks: {
                label: (context: any) => `${context.raw} units`,
            },
        },
    },
    scales: {
        y: {
            beginAtZero: true,
            title: {
                display: true,
                text: 'Quantity',
                color: '#1f2937', // gray-800
            },
            ticks: {
                callback: (value: number) => `${value}`,
            },
        },
        x: {
            title: {
                display: true,
                text: 'Date',
                color: '#1f2937', // gray-800
            },
            ticks: {
                autoSkip: false,
                maxRotation: 45,
                minRotation: 45,
            },
        },
    },
});
</script>

<template>
    <Head title="Report" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mt-4">
                <div class="w-full h-[600px] p-4 bg-transparent">
                    <Line
                        :data="chartData"
                        :options="chartOptions"
                        class="w-full h-full"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
