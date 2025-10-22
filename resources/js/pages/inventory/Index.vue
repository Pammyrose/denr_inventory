<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue';
import { router, usePage, useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import Create from './Create.vue';
import Edit from './Edit.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

// Define the Inventory interface
interface InventoryItem {
    id: string;
    name: string;
    category: string;
    location: string;
    purchase_date: string;
    unit_qty: string;
    condition: string;
    assigned_to: string;
    employee_id: number | null;
    return_date: string;
    status: string;
}

// Define the Employee interface
interface Employee {
    id: number;
    full_name: string;
}

// Define props for inventory items, employees, and error
interface Props {
    assets: InventoryItem[];
    employees: Employee[];
    error?: string | null; // Added to handle error prop like in reference
}

const props = defineProps<Props>();

const page = usePage();

// Alert state
const alertMessage = ref<string>(''); // Changed from alertMessage.value = null
const alertType = ref<'success' | 'error' | ''>(''); // Added to handle success/error types

// Log props for debugging
onMounted(() => {
    console.log('Index.vue Props:', { assets: props.assets, employees: props.employees, error: props.error });
    // Handle error prop like in reference
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

// Watch for flash messages like in reference
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

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Inventory',
        href: '/inventory',
    },
];

// Search input
const searchQuery = ref('');

// Computed property to filter assets based on search query
const filteredAssets = computed(() => {
    if (!searchQuery.value.trim()) {
        return props.assets;
    }
    const query = searchQuery.value.toLowerCase().trim();
    return props.assets.filter(item =>
        item.name.toLowerCase().includes(query) ||
        item.category.toLowerCase().includes(query) ||
        item.location.toLowerCase().includes(query) ||
        item.assigned_to.toLowerCase().includes(query)
    );
});

// Modal visibility for Create
const isCreateModalOpen = ref(false);
// Modal visibility for Edit
const isEditModalOpen = ref(false);
// Modal visibility for Delete Confirmation
const isConfirmDeleteModalOpen = ref(false);
const itemToDelete = ref<string | null>(null);

// Modal controls for Delete Confirmation
const openConfirmDeleteModal = (id: string) => {
    itemToDelete.value = id;
    isConfirmDeleteModalOpen.value = true;
};

const closeConfirmDeleteModal = () => {
    isConfirmDeleteModalOpen.value = false;
    itemToDelete.value = null;
};

// Modal controls for Create
const openCreateModal = () => {
    console.log('Opening create modal');
    isCreateModalOpen.value = true;
    console.log('isCreateModalOpen:', isCreateModalOpen.value);
};

const closeCreateModal = () => {
    console.log('Closing create modal');
    isCreateModalOpen.value = false;
};

// Modal controls for Edit
const openEditModal = (item: InventoryItem) => {
    selectedItem.value = {
        ...item,
        assigned_to: item.employee_id,
    };
    console.log('Opening edit modal with item:', selectedItem.value);
    isEditModalOpen.value = true;
};

const closeEditModal = () => {
    isEditModalOpen.value = false;
    selectedItem.value = null;
};

// Selected inventory item for editing
const selectedItem = ref<InventoryItem | null>(null);

const deleteItem = (item: InventoryItem | undefined) => {
    if (!item) {
        console.error('No item provided for deletion');
        alertMessage.value = 'Error: No item selected for deletion';
        alertType.value = 'error';
        setTimeout(() => {
            alertMessage.value = '';
            alertType.value = '';
        }, 3000);
        return;
    }
    const form = useForm({});
    console.log('Attempting to delete asset ID:', item.id);
    form.delete(route('inventory.destroy', { id: item.id }), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Asset deleted successfully');
            alertMessage.value = 'Asset deleted successfully';
            alertType.value = 'success';
            closeConfirmDeleteModal();
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        },
        onError: (errors) => {
            console.error('Error deleting asset:', errors);
            const errorMessage = Object.values(errors).join(', ') || 'Failed to delete asset';
            alertMessage.value = errorMessage;
            alertType.value = 'error';
            setTimeout(() => {
                alertMessage.value = '';
                alertType.value = '';
            }, 3000);
        },
    });
};

// Close create modal after navigation
onMounted(() => {
    router.on('finish', () => {
        console.log('Navigation finished, closing create modal');
        isCreateModalOpen.value = false;
    });
});
</script>

<template>
    <Head title="Inventory" />

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
                <Button
                    @click="openCreateModal"
                    class="bg-blue-600 text-white hover:bg-blue-700"
                >
                    Create
                </Button>
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
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <Create :closeModal="closeCreateModal" :employees="props.employees" />
                </div>
            </div>

            <!-- Edit Modal -->
            <div
                v-if="isEditModalOpen && selectedItem"
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
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"
                            />
                        </svg>
                    </button>
                    <Edit :item="selectedItem" :employees="props.employees" :closeModal="closeEditModal" />
                </div>
            </div>

            <!-- Confirmation Delete Modal -->
            <div
                v-if="isConfirmDeleteModalOpen"
                class="fixed inset-0 z-50 flex items-center justify-center bg-transparent transition-opacity duration-300"
                @click.self="closeConfirmDeleteModal"
            >
                <div class="bg-white rounded-xl shadow-lg w-full max-w-md p-6 relative border border-gray-300">
                    <button
                        @click="closeConfirmDeleteModal"
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
                    <h2 class="text-lg font-semibold">Confirm Deletion</h2>
                    <p class="mt-2">Are you sure you want to delete this asset? This action cannot be undone.</p>
                    <div class="mt-4 flex justify-end gap-2">
                        <Button
                            @click="closeConfirmDeleteModal"
                            class="bg-gray-300 text-gray-800 hover:bg-gray-400"
                        >
                            Cancel
                        </Button>
                        <Button
                            @click="deleteItem(props.assets.find(item => item.id === itemToDelete))"
                            class="bg-red-600 text-white hover:bg-red-700"
                        >
                            Delete
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Inventory table -->
            <div class="relative overflow-x-auto shadow-md sm:rounded-lg mt-4">
                <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                    <thead class="text-xs text-white uppercase bg-gradient-to-b from-green-300 to-green-600">
                        <tr>
                            <th scope="col" class="px-4 py-3">Asset ID</th>
                            <th scope="col" class="px-4 py-3 text-center">Name</th>
                            <th scope="col" class="px-4 py-3 text-center">Category</th>
                            <th scope="col" class="px-4 py-3 text-center">Location</th>
                            <th scope="col" class="px-4 py-3 text-center">Purchase Date</th>
                            <th scope="col" class="px-4 py-3 text-center">Units / Qty</th>
                            <th scope="col" class="px-4 py-3 text-center">Condition</th>
                            <th scope="col" class="px-4 py-3 text-center">Assigned To</th>
                            <th scope="col" class="px-4 py-3 text-center">Return Date</th>
                            <th scope="col" class="px-4 py-3 text-center">Status</th>
                            <th scope="col" class="px-4 py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="item in filteredAssets" :key="item.id"
                            class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 border-gray-200 hover:bg-gray-50 dark:hover:bg-gray-600"
                        >
                            <th scope="row" class="px-4 py-1 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                {{ item.id || 'N/A' }}
                            </th>
                            <td class="px-2 py-1 text-center">{{ item.name || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.category || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.location || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.purchase_date || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.unit_qty || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.condition || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.assigned_to || 'Unassigned' }}</td>
                            <td class="px-2 py-1 text-center">{{ item.return_date || 'N/A' }}</td>
                            <td class="px-2 py-1 text-center">
                                <span
                                    :class="{
                                        'text-white bg-yellow-600 hover:bg-yellow-700': item.status === 'Good',
                                        'text-white bg-red-700 hover:bg-red-800': item.status === 'Check',
                                        'text-white bg-green-600 hover:bg-green-800': item.status === 'Repair',
                                        'text-white bg-blue-700 hover:bg-blue-800': item.status === 'Upgrade',
                                    }"
                                    class="font-medium rounded-full text-sm px-5 py-1.5 text-center me-2 mb-2 dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-300"
                                >
                                    {{ item.status || 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-1 flex justify-center items-center gap-1">
                                <Button
                                    @click="openEditModal(item)"
                                    class="font-medium text-blue-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                                >
                                    Edit
                                </Button>
                                <a :href="route('inventory.view', { inventory: item.id })" class="font-medium text-green-600 dark:text-green-500 hover:underline">View</a>
                                <Button
                                    @click="openConfirmDeleteModal(item.id)"
                                    class="font-medium text-red-600 bg-transparent hover:bg-transparent hover:underline border-0 shadow-none"
                                >
                                    Delete
                                </Button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="!filteredAssets.length" class="text-center py-4">
                    No assets found.
                </div>
            </div>
        </div>
    </AppLayout>
</template>