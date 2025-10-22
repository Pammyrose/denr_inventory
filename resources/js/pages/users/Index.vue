<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Create from './Create.vue';
import Edit from './Edit.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// Define the User interface
interface User {
    id: number;
    full_name: string;
    email: string;
}

// Define props for users
interface Props {
    users: User[];
    error?: string | null; // Added to handle error prop
}

const props = defineProps<Props>();
const page = usePage();

// Alert state
const alertMessage = ref<string>(''); // Changed from string | null to string
const alertType = ref<'success' | 'error' | ''>(''); // Added to handle success/error types

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

// Modal visibility
const isCreateModalOpen = ref(false);
const isEditModalOpen = ref(false);
const selectedUser = ref<User | null>(null);

// Log props for debugging and handle error prop
onMounted(() => {
    console.log('Index.vue Props:', { users: props.users, error: props.error });
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

// Watch for flash messages
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

// Modal controls
const openCreateModal = () => {
    console.log('Opening create modal');
    isCreateModalOpen.value = true;
};

const closeCreateModal = () => {
    console.log('Closing create modal');
    isCreateModalOpen.value = false;
};

const openEditModal = (user: User) => {
    console.log('Opening edit modal for user:', user);
    selectedUser.value = user;
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    console.log('Closing edit modal');
    isEditModalOpen.value = false;
    selectedUser.value = null;
};

// Delete user
const deleteUser = (userId: number) => {
    if (!confirm('Are you sure you want to delete this user?')) return;
    router.delete(route('users.destroy', userId), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('User deleted successfully');
            alertMessage.value = 'User deleted successfully';
            alertType.value = 'success';
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        },
        onError: (errors) => {
            console.error('Error deleting user:', errors);
            const errorMessage = Object.values(errors).join(', ') || 'Failed to delete user';
            alertMessage.value = errorMessage;
            alertType.value = 'error';
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        },
    });
};

// View user
const viewUser = (userId: number) => {
    router.visit(route('users.show', userId));
};
</script>

<template>
    <Head title="Users" />

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

            <!-- Create button -->
            <Button
                @click="openCreateModal"
                class="bg-blue-600 text-white hover:bg-blue-700"
            >
                Create
            </Button>

            <!-- Create Modal -->
            <div
                v-if="isCreateModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-transparent transition-opacity duration-300"
                @click.self="closeCreateModal"
            >
                <div class="bg-white rounded-xl shadow-lg w-1/3 max-w-3xl p-6 relative border border-gray-300">
                    <!-- Close button -->
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
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <!-- Create form -->
                    <Create :closeModal="closeCreateModal" />
                </div>
            </div>

            <!-- Edit Modal -->
            <div
                v-if="isEditModalOpen && selectedUser"
                class="fixed inset-0 z-50 flex items-center justify-center bg-transparent transition-opacity duration-300"
                @click.self="closeEditModal"
            >
                <div class="bg-white rounded-xl shadow-lg w-1/3 max-w-3xl p-6 relative border border-gray-300">
                    <!-- Close button -->
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
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>

                    <!-- Edit form -->
                    <Edit :user="selectedUser" :closeModal="closeEditModal" />
                </div>
            </div>

            <!-- Users table -->
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
                        <tr>
                            <th scope="col" class="px-6 py-3">No</th>
                            <th scope="col" class="px-6 py-3">Full Name</th>
                            <th scope="col" class="px-6 py-3">Email</th>
                            <th scope="col" class="px-6 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="(user, index) in props.users"
                            :key="user.id"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
                        >
                            <th
                                scope="row"
                                class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white"
                            >
                                {{ index + 1 }}
                            </th>
                            <td class="px-6 py-4">{{ user.full_name }}</td>
                            <td class="px-6 py-4">{{ user.email }}</td>
                            <td class="px-6 py-4 flex justify-center items-center gap-2">
                                <button
                                    @click="openEditModal(user)"
                                    class="font-medium text-blue-600 dark:text-blue-500 hover:underline"
                                >
                                    Edit
                                </button>
                                <button
                                    @click="viewUser(user.id)"
                                    class="font-medium text-green-600 dark:text-green-500 hover:underline"
                                >
                                    View
                                </button>
                                <button
                                    @click="deleteUser(user.id)"
                                    class="font-medium text-red-600 dark:text-red-500 hover:underline"
                                >
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <!-- No user found message -->
                <div v-if="!props.users.length" class="text-center py-4">
                    No user found.
                </div>
            </div>
        </div>
    </AppLayout>
</template>