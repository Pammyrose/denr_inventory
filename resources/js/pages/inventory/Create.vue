<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

interface Employee {
    id: number;
    full_name: string;
}

interface Props {
    closeModal: () => void;
    employees: Employee[];
    qrCode?: string;
    asset?: {
        id: string;
        name: string;
        category: string;
        location: string;
        purchase_date: string;
        value: number;
        condition: string;
        assigned_to: number | null;
        status: string;
    };
    message?: string;
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    category: '',
    location: '',
    purchase_date: '',
    value: '',
    condition: '',
    assigned: null,
    status: '',
});

const errors = ref<{ [key: string]: string }>({});
const alertMessage = ref(props.message || '');
const alertType = ref<'success' | 'error' | ''>(props.message ? 'success' : '');

const validate = () => {
    errors.value = {};
    if (!form.name) errors.value.name = 'Name is required';
    if (!form.category) errors.value.category = 'Category is required';
    if (!form.location) errors.value.location = 'Location is required';
    if (!form.purchase_date) errors.value.purchase_date = 'Purchase date is required';
    if (!form.value && form.value !== 0) errors.value.value = 'Value is required';
    if (!form.condition) errors.value.condition = 'Condition is required';
    if (!form.status) errors.value.status = 'Status is required';
    if (form.assigned && !Number.isInteger(Number(form.assigned))) {
        errors.value.assigned = 'Assigned employee must be a valid ID';
    }
    return Object.keys(errors.value).length === 0;
};

const submit = () => {
    alertMessage.value = ''; // Reset alert
    alertType.value = '';
    if (!validate()) {
        console.log('Client-side validation failed:', errors.value);
        alertMessage.value = Object.values(errors.value).join('; ');
        alertType.value = 'error';
        return;
    }

    console.log('Submitting form with data:', form.data());

    form.post(route('inventory.store'), {
        preserveState: true,
        preserveScroll: true,
        onBefore: () => {
            console.log('Starting form submission');
        },
        onSuccess: () => {
            alertMessage.value = 'Asset created successfully.';
            alertType.value = 'success';
            setTimeout(() => {
                form.reset();
                alertMessage.value = '';
                alertType.value = '';
                props.closeModal();
                router.visit(route('inventory.index'), {
                    method: 'get',
                    preserveState: false,
                });
            }, 3000);
        },
        onError: (serverErrors) => {
            console.log('Server validation errors:', serverErrors);
            errors.value = serverErrors;
            alertMessage.value = serverErrors.general || Object.values(serverErrors).join('; ') || 'Failed to create asset';
            alertType.value = 'error';
        },
        onFinish: () => {
            console.log('Request finished, errors:', errors.value);
        },
    });
};

const downloadQrCode = () => {
    if (!props.qrCode) return;
    const link = document.createElement('a');
    link.href = props.qrCode;
    link.download = `asset_${props.asset?.id || 'qr'}.png`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <div>
        <!-- Success Message and QR Code -->
        <div v-if="alertType === 'success' && props.qrCode && props.asset" class="space-y-4">
            <div
                class="bg-green-100 border-l-4 border-green-400 text-green-700 p-4 rounded-r-md"
                role="alert"
            >
                <p class="font-medium">Success</p>
                <p>{{ alertMessage }}</p>
            </div>
            <div class="text-center">
                <h3 class="text-lg font-semibold">Asset QR Code</h3>
                <img :src="props.qrCode" alt="Asset QR Code" class="mx-auto mt-2" style="width: 200px; height: 200px;" />
                <Button @click="downloadQrCode" class="mt-2 bg-blue-600 text-white hover:bg-blue-700">
                    Download QR Code
                </Button>
            </div>
        </div>

        <!-- Create Form -->
        <form v-else @submit.prevent="submit" class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Create Asset</h2>

            <!-- Alert for Errors -->
            <div
                v-if="alertType === 'error' && alertMessage"
                class="bg-red-100 border-l-4 border-red-400 text-red-700 p-4 rounded-r-md"
                role="alert"
            >
                <p class="font-medium">Error</p>
                <p>{{ alertMessage }}</p>
            </div>

            <div class="grid grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="name">Name</Label>
                    <Input
                        id="name"
                        v-model="form.name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.name" class="text-red-600 text-sm">{{ errors.name }}</span>
                </div>
                <div>
                    <Label for="category">Category</Label>
                    <Input
                        id="category"
                        v-model="form.category"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.category" class="text-red-600 text-sm">{{ errors.category }}</span>
                </div>
                <div>
                    <Label for="location">Location</Label>
                    <Input
                        id="location"
                        v-model="form.location"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.location" class="text-red-600 text-sm">{{ errors.location }}</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="purchase_date">Purchase Date</Label>
                    <Input
                        id="purchase_date"
                        type="date"
                        v-model="form.purchase_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.purchase_date" class="text-red-600 text-sm">{{ errors.purchase_date }}</span>
                </div>
                <div>
                    <Label for="value">Value</Label>
                    <Input
                        id="value"
                        type="number"
                        v-model.number="form.value"
                        min="0"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.value" class="text-red-600 text-sm">{{ errors.value }}</span>
                </div>
                <div>
                    <Label for="condition">Condition</Label>
                    <Input
                        id="condition"
                        v-model="form.condition"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.condition" class="text-red-600 text-sm">{{ errors.condition }}</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="assigned">Assigned To</Label>
                    <select
                        id="assigned"
                        v-model="form.assigned"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option :value="null">Select Employee</option>
                        <option
                            v-for="employee in employees"
                            :key="employee.id"
                            :value="employee.id"
                        >
                            {{ employee.full_name }}
                        </option>
                    </select>
                    <span v-if="errors.assigned" class="text-red-600 text-sm">{{ errors.assigned }}</span>
                </div>
                <div>
                    <Label for="status">Status</Label>
                    <select
                        id="status"
                        v-model="form.status"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="" disabled>Select Status</option>
                        <option value="Check">Check</option>
                        <option value="Repair">Repair</option>
                        <option value="Upgrade">Upgrade</option>
                    </select>
                    <span v-if="errors.status" class="text-red-600 text-sm">{{ errors.status }}</span>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    @click="props.closeModal"
                    class="bg-gray-200 text-gray-800 hover:bg-gray-300"
                >
                    Cancel
                </Button>
                <Button
                    type="submit"
                    class="bg-blue-600 text-white hover:bg-blue-700"
                >
                    Submit
                </Button>
            </div>
        </form>
    </div>
</template>