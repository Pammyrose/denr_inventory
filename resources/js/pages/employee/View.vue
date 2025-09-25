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
  position_name: string;
  assignment_name: string;
  div_sec_unit: string;
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
        <div class="grid grid-cols-2 gap-4">
          <div>
            <p><strong>Employee ID:</strong> <span class="text-black text-md">{{ props.employee.id }}</span></p>
            <p class="mt-3"><strong>Full Name:</strong> <span class="text-black text-md">{{ props.employee.first_name }} {{ props.employee.middle_name || '' }} {{ props.employee.last_name }} {{ props.employee.suffix || '' }}</span></p>
            <p class="mt-3"><strong>Sex:</strong> <span class="text-black text-md">{{ props.employee.sex }}</span></p>
            <p class="mt-3"><strong>Email:</strong> <span class="text-black text-md">{{ props.employee.email }}</span></p>
          </div>
          <div>
            <p> <strong>Status:</strong> <span class="text-black text-md">{{ props.employee.emp_status }}</span></p>
            <p class="mt-3"><strong>Position:</strong> <span class="text-black text-md">{{ props.employee.position_name }}</span></p>
            <p class="mt-3"><strong>Assignment:</strong> <span class="text-black text-md">{{ props.employee.assignment_name }}</span></p>
            <p class="mt-3"><strong>Division/Unit:</strong> <span class="text-black text-md">{{ props.employee.div_sec_unit }}</span></p>
          </div>
        </div>
      </div>
      <div v-else class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">Employee not found or failed to load.</span>
      </div>
    </div>
  </AppLayout>
</template>