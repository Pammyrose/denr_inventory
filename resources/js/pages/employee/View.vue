<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { type BreadcrumbItem } from '@/types';

interface Employee {
  id: number;
  first_name: string;
  middle_name: string;
  last_name: string;
  suffix: string;
  sex: string;
  email: string;
  emp_status: string;
  position_name: string | null; // Stores name, not ID
  assignment_name: string | null; // Stores name, not ID
  div_sec_unit: string | null; // Stores name, not ID
  date_of_birth: string | null;
  tin_no: string | null;
  date_appointment: string | null;
  date_last_promotion: string | null;
  civil_service: string | null;
  education: string | null;
  assets: Array<{
    id: number;
    name: string | null;
    category: string | null;
    location: string | null;
    purchase_date: string | null;
  }> | null;
}

const props = defineProps<{
  employee: Employee | null;
  error?: string | null;
}>();

console.log('ViewEmployee.vue props:', props.employee); // Debug the employee prop

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Employee', href: '/employee' },
  { title: 'View Employee', href: '#' },
];
</script>

<template>
  <Head :title="employee ? `Employee ${employee.id}` : 'Employee Not Found'" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <h2 class="text-2xl font-semibold text-gray-900 mb-4">Employee Details</h2>
      <div v-if="props.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{{ props.error }}</span>
      </div>
      <div v-else-if="props.employee" class="bg-white shadow-md rounded-lg p-8">
        <div class="grid grid-cols-3 gap-4">
          <div>
            <p><strong>Employee ID:</strong> <span class="text-black text-md">{{ props.employee.id }}</span></p>
            <p class="mt-3"><strong>Full Name:</strong> <span class="text-black text-md">{{ props.employee.first_name }} {{ props.employee.middle_name || '' }} {{ props.employee.last_name }} {{ props.employee.suffix || '' }}</span></p>
            <p class="mt-3"><strong>Sex:</strong> <span class="text-black text-md">{{ props.employee.sex || 'N/A' }}</span></p>
            <p class="mt-3"><strong>Email:</strong> <span class="text-black text-md">{{ props.employee.email || 'N/A' }}</span></p>
            <p class="mt-3"><strong>Date of Birth:</strong> <span class="text-black text-md">{{ props.employee.date_of_birth || 'N/A' }}</span></p>
            <p class="mt-3"><strong>TIN No:</strong> <span class="text-black text-md">{{ props.employee.tin_no || 'N/A' }}</span></p>
          </div>
          <div>
            <p><strong>Status:</strong> <span class="text-black text-md">{{ props.employee.emp_status || 'N/A' }}</span></p>
            <p class="mt-3"><strong>Position:</strong> <span class="text-black text-md">{{ props.employee.position_name || 'N/A' }}</span></p>
            <p class="mt-3"><strong>Assignment:</strong> <span class="text-black text-md">{{ props.employee.assignment_name || 'N/A' }}</span></p>
            <p class="mt-3"><strong>Division/Unit:</strong> <span class="text-black text-md">{{ props.employee.div_sec_unit || 'N/A' }}</span></p>
          </div>
          <div>
            <p class="mt-3"><strong>Date of Appointment:</strong> <span class="text-black text-md">{{ props.employee.date_appointment || 'N/A' }}</span></p>
            <p class="mt-3"><strong>Date of Last Promotion:</strong> <span class="text-black text-md">{{ props.employee.date_last_promotion || 'N/A' }}</span></p>
            <p class="mt-3"><strong>Civil Service:</strong> <span class="text-black text-md">{{ props.employee.civil_service || 'N/A' }}</span></p>
            <p class="mt-3"><strong>Education:</strong> <span class="text-black text-md">{{ props.employee.education || 'N/A' }}</span></p>
          </div>
        </div>
        <!-- Assets Section -->
        <div v-if="props.employee.assets && props.employee.assets.length > 0" class="mt-6">
          <h2 class="text-lg font-semibold mb-2">Assigned Assets</h2>
          <table class="w-full border-collapse border border-gray-300">
            <thead>
              <tr class="bg-gray-100">
                <th class="border border-gray-300 p-2">Name</th>
                <th class="border border-gray-300 p-2">Category</th>
                <th class="border border-gray-300 p-2">Location</th>
                <th class="border border-gray-300 p-2">Assigned Date</th>
                <th class="border border-gray-300 p-2">Returned Date</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="asset in props.employee.assets" :key="asset.id" class="hover:bg-gray-50 text-black">
                <td class="border border-gray-300 p-2">{{ asset.name || 'N/A' }}</td>
                <td class="border border-gray-300 p-2">{{ asset.category || 'N/A' }}</td>
                <td class="border border-gray-300 p-2">{{ asset.location || 'N/A' }}</td>
                <td class="border border-gray-300 p-2">{{ asset.purchase_date || 'N/A' }}</td>
                <td class="border border-gray-300 p-2">{{ asset.return_date || 'N/A' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div v-else class="mt-6">
          <p>No assets assigned to this employee.</p>
        </div>
      </div>
      <div v-else class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">Employee not found or failed to load.</span>
      </div>
    </div>
  </AppLayout>
</template>