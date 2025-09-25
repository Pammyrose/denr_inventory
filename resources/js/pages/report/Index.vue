<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale } from 'chart.js';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage, router } from '@inertiajs/vue3';
import { format } from 'date-fns';

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

// Access data passed from the controller
const { props } = usePage();
const assetsByDay = ref((props.assetsByDay || []) as { date: string; quantity: number }[]);
const availableYears = ref(props.availableYears as { value: string; label: string }[]);
const availableMonths = ref(props.availableMonths as { value: string; label: string }[]);
const selectedYear = ref(props.selectedYear as string);
const selectedMonth = ref(props.selectedMonth as string);
const daysInMonth = ref(props.daysInMonth as number);

// Force chart re-render key
const chartKey = ref(`${selectedYear.value}-${selectedMonth.value}`);

// Sync props with reactive refs and log changes
watch(() => props.assetsByDay, (newData) => {
    console.log('Props assetsByDay updated:', newData);
    assetsByDay.value = (newData || []) as { date: string; quantity: number }[];
    chartKey.value = `${selectedYear.value}-${selectedMonth.value}`; // Update chart key
}, { deep: true, immediate: true });

watch(() => props.availableYears, (newYears) => {
    console.log('Props availableYears updated:', newYears);
    availableYears.value = newYears as { value: string; label: string }[];
}, { deep: true });

watch(() => props.availableMonths, (newMonths) => {
    console.log('Props availableMonths updated:', newMonths);
    availableMonths.value = newMonths as { value: string; label: string }[];
}, { deep: true });

watch(() => props.selectedYear, (newYear) => {
    console.log('Props selectedYear updated:', newYear);
    selectedYear.value = newYear as string;
    chartKey.value = `${newYear}-${selectedMonth.value}`; // Update chart key
});

watch(() => props.selectedMonth, (newMonth) => {
    console.log('Props selectedMonth updated:', newMonth);
    selectedMonth.value = newMonth as string;
    chartKey.value = `${selectedYear.value}-${newMonth}`; // Update chart key
});

watch(() => props.daysInMonth, (newDays) => {
    console.log('Props daysInMonth updated:', newDays);
    daysInMonth.value = newDays as number;
});

// Format date labels (e.g., "Aug 1")
const formatDateLabel = (date: string) => {
    try {
        return format(new Date(date), 'MMM d'); // e.g., "Aug 1"
    } catch (error) {
        console.error('Invalid date format:', date, error);
        return date; // Fallback to raw date
    }
};

// Debug chart labels
watch(assetsByDay, (newData) => {
    console.log('assetsByDay ref updated:', newData);
    console.log('Chart labels:', newData.map(item => formatDateLabel(item.date)));
}, { deep: true });

// Reactive chart data
const chartData = computed(() => {
    console.log('Computing chartData with assetsByDay:', assetsByDay.value);
    return {
        labels: assetsByDay.value.map(item => formatDateLabel(item.date)),
        datasets: [
            {
                label: 'Assets',
                data: assetsByDay.value.map(item => item.quantity),
                borderColor: '#10b981', // emerald-500
                backgroundColor: '#10b981', // For points
                borderWidth: 4,
                pointRadius: 4,
                fill: false,
                tension: 0.5,
            },
        ],
    };
});

// Reactive chart options
const chartOptions = computed(() => ({
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top' as const,
        },
        title: {
            display: true,
            text: `Daily Purchased Assets (${format(new Date(parseInt(selectedYear.value), parseInt(selectedMonth.value) - 1), 'MMMM yyyy')})`,
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
                stepSize: 10,
                callback: (value: number) => `${value}`,
                maxTicksLimit: daysInMonth.value || 31, // Fallback to 31 if undefined
            },
            suggestedMax: 150,
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
}));

// Handle year or month selection change
const handleFilterChange = () => {
    console.log('Filter changed:', { year: selectedYear.value, month: selectedMonth.value });
    router.get(
        '/report',
        { year: selectedYear.value, month: selectedMonth.value },
        { preserveState: false, replace: true } // Disable preserveState, use replace
    );
};
</script>

<template>
    <Head title="Report" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <div class="mb-4 flex space-x-4">
                <div>
                    <label for="year-select" class="mr-2 text-gray-700">Select Year:</label>
                    <select
                        id="year-select"
                        v-model="selectedYear"
                        @change="handleFilterChange"
                        class="p-2 border border-gray-300 rounded-md bg-white text-gray-700 focus:ring-2 focus:ring-emerald-500"
                    >
                        <option v-for="year in availableYears" :key="year.value" :value="year.value">
                            {{ year.label }}
                        </option>
                    </select>
                </div>
                <div>
                    <label for="month-select" class="mr-2 text-gray-700">Select Month:</label>
                    <select
                        id="month-select"
                        v-model="selectedMonth"
                        @change="handleFilterChange"
                        class="p-2 border border-gray-300 rounded-md bg-white text-gray-700 focus:ring-2 focus:ring-emerald-500"
                    >
                        <option v-for="month in availableMonths" :key="month.value" :value="month.value">
                            {{ month.label }}
                        </option>
                    </select>
                </div>
            </div>
            <div class="mt-4">
                <div v-if="assetsByDay.length > 0 && daysInMonth > 0" class="w-full h-full p-4 bg-transparent">
                    <Line
                        :data="chartData"
                        :options="chartOptions"
                        class="w-full h-full"
                        :key="chartKey"
                    />
                </div>
                <div v-else class="text-center text-gray-500">
                    No data available for the selected month.
                </div>
            </div>
        </div>
    </AppLayout>
</template>