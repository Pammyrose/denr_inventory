<script setup lang="ts">
import { computed } from 'vue';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale } from 'chart.js';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { format } from 'date-fns';
import EmployeeIndex from '@/components/employee/Index.vue';

// Register Chart.js components
ChartJS.register(Title, Tooltip, Legend, LineElement, PointElement, CategoryScale, LinearScale);

// Access the props passed from the backend
const { props } = usePage();
const dashboardData = props.dashboardData as {
  employees: number;
  users: number;
  assignedAssets: number;
  assets: number;
  assignedPerDepartment: { name: string; value: number }[];
  purchasedAssets: { month: string; count: number }[];
  assetsByDay: { date: string; quantity: number }[];
  daysInMonth: number;
  selectedYear: string;
  selectedMonth: string;
};

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dashboard',
    href: '/dashboard',
  },
];

// Format month labels (e.g., "Jan", "Feb")
const formatMonthLabel = (month: string) => {
  try {
    return format(new Date(month), 'MMM'); // e.g., "Jan"
  } catch (error) {
    console.error('Invalid month format:', month, error);
    return month; // Fallback to raw month
  }
};

// Format date labels (e.g., "Aug 1")
const formatDateLabel = (date: string) => {
  try {
    return format(new Date(date), 'MMM d'); // e.g., "Aug 1"
  } catch (error) {
    console.error('Invalid date format:', date, error);
    return date; // Fallback to raw date
  }
};

// Reactive chart data for purchased assets (monthly)
const chartDataMonthly = computed(() => ({
  labels: dashboardData.purchasedAssets.map(item => formatMonthLabel(item.month)),
  datasets: [
    {
      label: 'Purchased Assets',
      data: dashboardData.purchasedAssets.map(item => item.count),
      borderColor: '#10b981', // emerald-500
      backgroundColor: '#10b981', // For points
      borderWidth: 2,
      pointRadius: 3,
      fill: false,
      tension: 0.4,
    },
  ],
}));

// Reactive chart data for daily assets (mini report graph)
const chartDataDaily = computed(() => ({
  labels: dashboardData.assetsByDay.map(item => formatDateLabel(item.date)),
  datasets: [
    {
      label: 'Daily Purchased Assets',
      data: dashboardData.assetsByDay.map(item => item.quantity),
      borderColor: '#10b981', // emerald-500, matching Index.vue
      backgroundColor: '#10b981', // For points
      borderWidth: 2,
      pointRadius: 3,
      fill: false,
      tension: 0.5,
    },
  ],
}));

// Reactive chart options for monthly purchased assets
const chartOptionsMonthly = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
      labels: {
        color: '#1f2937', // gray-800
      },
    },
    title: {
      display: true,
      text: 'Monthly Purchased Assets',
      color: '#1f2937',
      font: {
        size: 14,
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
        color: '#1f2937',
        font: {
          size: 12,
        },
      },
      ticks: {
        stepSize: 10,
        color: '#1f2937',
      },
      suggestedMax: Math.max(...dashboardData.purchasedAssets.map(item => item.count)) + 10 || 50,
    },
    x: {
      title: {
        display: true,
        text: 'Month',
        color: '#1f2937',
        font: {
          size: 12,
        },
      },
      ticks: {
        color: '#1f2937',
        maxRotation: 45,
        minRotation: 45,
      },
    },
  },
}));

// Reactive chart options for daily assets (mini report graph)
const chartOptionsDaily = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
      labels: {
        color: '#1f2937', // gray-800
        font: {
          size: 12,
        },
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
        color: '#1f2937',
        font: {
          size: 10,
        },
      },
      ticks: {
        stepSize: 10,
        color: '#1f2937',
        font: {
          size: 10,
        },
        maxTicksLimit: dashboardData.daysInMonth || 31,
      },
      suggestedMax: 50, // Smaller max for mini chart
    },
    x: {
      title: {
        display: true,
        text: 'Date',
        color: '#1f2937',
        font: {
          size: 10,
        },
      },
      ticks: {
        color: '#1f2937',
        font: {
          size: 10,
        },
        maxRotation: 45,
        minRotation: 45,
        autoSkip: false,
      },
    },
  },
}));
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
      <!-- First Row: 4-column grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <Link :href="route('employee.index')">
          <div class="bg-[#ef4444] rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col items-center justify-between h-24 hover:shadow-lg hover:scale-105 hover:brightness-105 transition duration-300">
            <h3 class="text-lg font-semibold text-gray-100">Employees</h3>
            <span class="text-2xl font-bold text-gray-100">{{ dashboardData.employees }}</span>
          </div>
        </Link>
        <Link :href="route('users.index')">
          <div
            class="bg-[#f59e0b] rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col items-center justify-between h-24 hover:shadow-lg hover:scale-105 hover:brightness-105 transition duration-300"
          >
            <h3 class="text-lg font-semibold text-gray-100">Users</h3>
            <span class="text-2xl font-bold text-gray-100">{{ dashboardData.users }}</span>
          </div>
        </Link>
        <Link :href="route('inventory.index')">
          <div
            class="bg-[#10b981] rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col items-center justify-between h-24 hover:shadow-lg hover:scale-105 hover:brightness-105 transition duration-300"
          >
            <h3 class="text-lg font-semibold text-gray-100">Assigned Assets</h3>
            <span class="text-2xl font-bold text-gray-100">{{ dashboardData.assignedAssets }}</span>
          </div>
        </Link>
        <Link :href="route('inventory.index')">
          <div
            class="bg-[#3b82f6] rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 flex flex-col items-center justify-between h-24 hover:shadow-lg hover:scale-105 hover:brightness-105 transition duration-300"
          >
            <h3 class="text-lg font-semibold text-gray-100">Assets</h3>
            <span class="text-2xl font-bold text-gray-100">{{ dashboardData.assets }}</span>
          </div>
        </Link>
      </div>

      <!-- Second Row: Charts -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Pie Chart for Assigned Assets per Department -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Assigned Assets per Department</h3>
          <div class="relative aspect-[4/3] max-w-[250px] mx-auto">
            <svg viewBox="0 0 100 100" class="w-full h-full">
              <circle cx="50" cy="50" r="40" fill="none" stroke="#3b82f6" stroke-width="20" stroke-dasharray="60 251.2" />
              <circle cx="50" cy="50" r="40" fill="none" stroke="#10b981" stroke-width="20" stroke-dasharray="50 251.2" transform="rotate(90 50 50)" />
              <circle cx="50" cy="50" r="40" fill="none" stroke="#f59e0b" stroke-width="20" stroke-dasharray="40 251.2" transform="rotate(180 50 50)" />
              <circle cx="50" cy="50" r="40" fill="none" stroke="#ef4444" stroke-width="20" stroke-dasharray="30 251.2" transform="rotate(270 50 50)" />
            </svg>
            <div class="mt-4 flex flex-wrap justify-center gap-4">
              <div v-for="dept in dashboardData.assignedPerDepartment" :key="dept.name" class="flex items-center">
                <div class="w-4 h-4 mr-2" :style="{ backgroundColor: getDepartmentColor(dept.name) }"></div>
                <span class="text-sm text-gray-900 dark:text-white">{{ dept.name }}: {{ dept.value }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Line Charts Container -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
          <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Purchased Assets</h3>
          <!-- Monthly Purchased Assets Chart -->

          <!-- Mini Daily Purchased Assets Chart -->
          <Link :href="route('report.index')">
            <div class="relative h-[400px]">
              <Line
                v-if="dashboardData.assetsByDay.length > 0 && dashboardData.daysInMonth > 0"
                :data="chartDataDaily"
                :options="chartOptionsDaily"
                class="w-full h-full"
              />
              <div v-else class="text-center text-gray-500">
                No daily assets data available for the selected month.
              </div>
            </div>
          </Link>
        </div>
      </div>

      <!-- Table Section -->
      <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
            <tr>
              <th scope="col" class="px-6 py-3">Name</th>
              <th scope="col" class="px-6 py-3">
                <div class="flex items-center">
                  Assigned Date
                  <a href="#">
                    <svg class="w-3 h-3 ms-1.5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M8.574 11.024h6.852a2.075 2.075 0 0 0 1.847-1.086 1.9 1.9 0 0 0-.11-1.986L13.736 2.9a2.122 2.122 0 0 0-3.472 0L6.837 7.952a1.9 1.9 0 0 0-.11 1.986 2.074 2.074 0 0 0 1.847 1.086Zm6.852 1.952H8.574a2.072 2.072 0 0 0-1.847 1.087 1.9 1.9 0 0 0 .11 1.985l3.426 5.05a2.123 2.123 0 0 0 3.472 0l3.427-5.05a1.9 1.9 0 0 0 .11-1.985 2.074 2.074 0 0 0-1.846-1.087Z"/>
                    </svg>
                  </a>
                </div>
              </th>
              <th scope="col" class="px-6 py-3">Status</th>
              
            </tr>
          </thead>
          <tbody>
            <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200">
              <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                Pamela Rose Malasan
              </th>
              <td class="px-6 py-4">09-18-2025</td>
              <td class="px-6 py-4">
                <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Open</a>
              </td>
            </tr>
          </tbody>
        </table>
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