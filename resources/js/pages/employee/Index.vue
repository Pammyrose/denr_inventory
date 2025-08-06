
<script setup lang="ts">
import { ref } from 'vue';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Create from './Create.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/vue3';

interface Employees{
    id: number,
    first_name: string,
    middle_name: string,
    last_name: string,
    suffix: string,
    sex: string,
    email: string,
    emp_status: string,
    position_name: string,
    assignment_name: string,

}

interface Props{
    employees: Employees[];
}

const props = defineProps<Props>();

const page = usePage()

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Employee',
        href: '/employee',
    },
];

// Modal visibility
const isModalOpen = ref(false);

// Modal controls
const openModal = () => {
    isModalOpen.value = true;
};
const closeModal = () => {
    isModalOpen.value = false;
};
</script>

<template>
    <Head title="Employee" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <!-- Create button -->
            <Button
                @click="openModal"
                class="bg-blue-600 text-white hover:bg-blue-700"
            >
                Create
            </Button>

            <!-- Modal -->
            <div
                v-if="isModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-transparent transition-opacity duration-300"
                @click.self="closeModal"
            >
                <div class="bg-white rounded-xl shadow-lg w-full max-w-3xl p-6 relative border border-gray-300">
                    <!-- Close button -->
                    <button
                        @click="closeModal"
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
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <!-- Create form -->
                    <Create :closeModal="closeModal" />
                </div>
            </div>

            <!-- Employee table -->
            
            <div v-if="page.props.flash?.message" class="alert">
        {{ page.props.flash.message }}
      </div>

      <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
            <tr>
                <th scope="col" class="px-6 py-3">
                    Name
                </th>
                <th scope="col" class="px-6 py-3">
                    Position
                </th>
                <th scope="col" class="px-6 py-3">
                    Status
                </th>
                <th scope="col" class="px-6 py-3 text-center">
                    Action
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="employee in props.employees"
  :key="employee.id"
  class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
>
  <th
    scope="row"
    class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white"
  >
    {{ employee.first_name }} {{ employee.middle_name }} {{ employee.last_name }} {{ employee.suffix }}
  </th>
  <td class="px-6 py-4">
    {{ employee.position_name }}
  </td>
  <td class="px-6 py-4">
    {{ employee.emp_status }}
  </td>
  <td class="px-6 py-4 flex justify-center items-center gap-2">
    <a href="#" class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Edit</a>
    <a href="#" class="font-medium text-green-600 dark:text-green-500 hover:underline">View</a>
    <a href="#" class="font-medium text-red-600 dark:text-red-500 hover:underline">Delete</a>
  </td>
</tr>

        </tbody>
    </table>
</div>

        </div>
    </AppLayout>
</template>
