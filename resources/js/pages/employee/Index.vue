<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { usePage, Head, useForm, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import Create from './Create.vue';
import Edit from './Edit.vue';

interface Employee {
  id: number;
  first_name: string;
  middle_name: string;
  last_name: string;
  suffix: string;
  sex: string;
  email: string;
  emp_status: string;
  position_id?: string;
  assignment_id?: string;
  org_unit_id?: string;
  position_name?: string;
  assignment_name?: string;
  div_sec_unit?: string;
}

interface Dropdowns {
  orgUnits: Array<{ value: string; label: string }>;
  positions: Array<{ value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }>;
  assignmentPlaces: Array<{ value: string; label: string }>;
  empStatuses: Array<{ value: string; label: string }>;
}

interface BreadcrumbItem {
  title: string;
  href: string;
}

const props = defineProps<{
  employees: Employee[] | null;
  dropdowns: Dropdowns | null;
  error?: string | null;
}>();

const page = usePage();

const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

onMounted(() => {
  console.log('Index.vue: Props received', {
    employees: props.employees,
    dropdowns: props.dropdowns,
    error: props.error,
    flash: (page.props as any).flash,
  });
  if (props.error) {
    console.log('Index.vue: Error prop detected:', props.error);
    alertMessage.value = props.error;
    alertType.value = 'error';
    setTimeout(() => {
      alertMessage.value = '';
      alertType.value = '';
    }, 3000);
  }
});

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Employee', href: '/employee' },
];

const searchQuery = ref('');
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedEmployee = ref<Employee | null>(null);

const filteredEmployees = computed(() => {
  if (!props.employees || !Array.isArray(props.employees)) {
    console.warn('Index.vue: No employees or invalid employees prop', props.employees);
    return [];
  }
  if (!searchQuery.value.trim()) {
    return props.employees;
  }
  const query = searchQuery.value.toLowerCase().trim();
  return props.employees.filter(employee => {
    const fullName = `${employee.first_name} ${employee.middle_name || ''} ${employee.last_name} ${employee.suffix || ''}`.toLowerCase();
    return (
      fullName.includes(query) ||
      (employee.position_name?.toLowerCase().includes(query) ?? false) ||
      (employee.emp_status?.toLowerCase().includes(query) ?? false) ||
      (employee.assignment_name?.toLowerCase().includes(query) ?? false) ||
      (employee.div_sec_unit?.toLowerCase().includes(query) ?? false)
    );
  });
});

const openCreateModal = () => {
  console.log('Opening create modal, current state:', isCreateModalOpen.value);
  isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
  console.log('Closing create modal');
  isCreateModalOpen.value = false;
};

const openEditModal = (employee: Employee) => {
  console.log('Opening edit modal for employee:', employee);
  selectedEmployee.value = { ...employee };
  isEditModalOpen.value = true;
};

const closeEditModal = () => {
  console.log('Closing edit modal');
  isEditModalOpen.value = false;
  selectedEmployee.value = null;
};

const openConfirmArchiveModal = (employeeId: number) => {
  console.log('Opening confirm archive modal for employee:', employeeId);
  if (confirm('Are you sure you want to archive this employee? This action cannot be undone.')) {
    archiveEmployee(employeeId);
  }
};

const archiveEmployee = (employeeId: number) => {
  console.log('Archiving employee:', employeeId);
  router.post(route('employee.store'), { archive_employee_id: employeeId }, {
    onSuccess: () => {
      console.log('Archive successful');
      // Update the employees list by filtering out the archived employee
      if (props.employees) {
        props.employees = props.employees.filter(emp => emp.id !== employeeId);
      }
      alertMessage.value = 'Employee archived successfully';
      alertType.value = 'success';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 3000);
    },
    onError: (errors) => {
      console.error('Archive failed:', errors);
      alertMessage.value = 'Failed to archive employee: ' + Object.values(errors).join(', ');
      alertType.value = 'error';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 3000);
    },
  });
};

watch(
  () => (page.props as any).flash,
  (flash) => {
    console.log('Index.vue: Watch triggered, flash:', flash);
    if (flash?.message) {
      console.log('Index.vue: Flash message detected:', flash.message);
      alertMessage.value = flash.message;
      alertType.value = 'success';
      setTimeout(() => {
        alertMessage.value = '';
        alertType.value = '';
      }, 2000);
    }
  },
  { immediate: true }
);
</script>

<template>
  <Head title="Employee" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4">
      <!-- Alert -->
      <div
        v-if="alertMessage"
        :class="{
          'bg-green-100 border-green-400 text-green-700': alertType === 'success',
          'bg-red-100 border-red-400 text-red-700': alertType === 'error',
        }"
        class="border-l-4 p-4 mb-4 rounded-r-md"
        role="alert"
      >
        <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
        <p>{{ alertMessage }}</p>
      </div>

      <!-- Search Input and Create Button -->
      <div class="flex justify-between items-center mb-4">
        <div class="flex gap-2">
          <Button
            @click="openCreateModal"
            class="bg-blue-600 text-white hover:bg-blue-700"
          >
            Create
          </Button>
          <Link
            :href="route('employee.archived')"
            class="bg-gray-200 text-white px-4 py-2 rounded-md"
          >
          <svg class="w-4 h-4 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="currentColor" viewBox="0 0 24 24">
  <path fill-rule="evenodd" d="M20 10H4v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8ZM9 13v-1h6v1a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1Z" clip-rule="evenodd"/>
  <path d="M2 6a2 2 0 0 1 2-2h16a2 2 0 1 1 0 4H4a2 2 0 0 1-2-2Z"/>
</svg>

          </Link>
        </div>
        <div class="relative w-full max-w-md">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
          />
          <svg
            class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
            />
          </svg>
        </div>
      </div>

      <!-- Create Modal -->
      <div
        v-if="isCreateModalOpen"
        class="fixed inset-0 z-50 flex items-center justify-center bg-transparent transition-opacity duration-300"
        @click.self="closeCreateModal"
      >
        <div class="bg-white rounded-xl shadow-lg w-full max-w-6xl p-6 relative border border-gray-300">
          <button
            @click="closeCreateModal"
            class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none"
            aria-label="Close modal"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <Create
            :orgUnits="props.dropdowns?.orgUnits ?? []"
            :positions="props.dropdowns?.positions ?? []"
            :assignmentPlaces="props.dropdowns?.assignmentPlaces ?? []"
            :empStatuses="props.dropdowns?.empStatuses ?? []"
            :close="closeCreateModal"
          />
        </div>
      </div>

      <!-- Edit Modal -->
      <div
        v-if="isEditModalOpen && selectedEmployee"
        class="fixed inset-0 z-50 flex items-center justify-center bg-transparent transition-opacity duration-300"
        @click.self="closeEditModal"
      >
        <div class="bg-white rounded-xl shadow-lg w-full max-w-6xl p-6 relative border border-gray-300">
          <button
            @click="closeEditModal"
            class="absolute top-3 right-3 text-gray-600 hover:text-gray-800 focus:outline-none"
            aria-label="Close modal"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
          <Edit
            :employee="selectedEmployee"
            :orgUnits="props.dropdowns?.orgUnits ?? []"
            :positions="props.dropdowns?.positions ?? []"
            :assignmentPlaces="props.dropdowns?.assignmentPlaces ?? []"
            :empStatuses="props.dropdowns?.empStatuses ?? []"
            :close="closeEditModal"
          />
        </div>
      </div>

      <!-- Employee Table -->
      <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
          <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
            <tr>
              <th scope="col" class="px-4 py-3">Employee ID</th>
              <th scope="col" class="px-4 py-3 text-center">Name</th>
              <th scope="col" class="px-4 py-3 text-center">Position</th>
              <th scope="col" class="px-4 py-3 text-center">Assignment</th>
              <th scope="col" class="px-4 py-3 text-center">Status</th>
              <th scope="col" class="px-4 py-3 text-center">Action</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="employee in filteredEmployees"
              :key="employee.id"
              class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
            >
              <th scope="row" class="px-4 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                {{ employee.id || 'N/A' }}
              </th>
              <td class="px-2 py-1 text-center">
                {{ employee.first_name }} {{ employee.middle_name || '' }} {{ employee.last_name }} {{ employee.suffix || '' }}
              </td>
              <td class="px-2 py-1 text-center">{{ employee.position_name || 'N/A' }}</td>
              <td class="px-2 py-1 text-center">{{ employee.assignment_name || 'N/A' }}</td>
              <td class="px-2 py-1 text-center">
                <span
                  :class="{
                    'text-white bg-green-600 hover:bg-green-800': employee.emp_status === 'Active',
                    'text-white bg-red-700 hover:bg-red-800': employee.emp_status === 'Inactive',
                    'text-white bg-blue-700 hover:bg-blue-800': employee.emp_status === 'On Leave',
                  }"
                  class="font-medium rounded-full text-sm px-5 py-1.5 text-center me-2 mb-2"
                >
                  {{ employee.emp_status || 'N/A' }}
                </span>
              </td>
              <td class="px-4 py-1 flex justify-center items-center gap-1">
                <Button
                  @click="openEditModal(employee)"
                  class="font-medium text-blue-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                >
                  Edit
                </Button>
                <Link :href="route('employee.view', { employee: employee.id })" class="font-medium text-green-600 dark:text-green-500 hover:underline">View</Link>
                <Button
                  @click="openConfirmArchiveModal(employee.id)"
                  class="font-medium text-yellow-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                >
                  Archive
                </Button>
              </td>
            </tr>
            <tr v-if="!filteredEmployees.length">
              <td colspan="7" class="text-center py-4">No employees found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>