<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

defineProps<{
    closeModal: () => void;
    employees: {
        id: number;
        first_name: string;
        last_name: string;
    }[];
}>();

const form = useForm({
    asset_id: '',
    name: '',
    category: '',
    location: '',
    purchase_date: '',
    value: '',
    condition: '',
    assigned: '', // Will store employee_id if changed
    status: '',
});

const errors = ref<{ [key: string]: string }>({});

const validate = () => {
    errors.value = {}; // Clear previous errors
    if (!form.asset_id) errors.value.asset_id = 'Asset ID is required';
    if (!form.name) errors.value.name = 'Name is required';
    if (!form.category) errors.value.category = 'Category is required';
    if (!form.location) errors.value.location = 'Location is required';
    if (!form.purchase_date) errors.value.purchase_date = 'Purchase date is required';
    if (!form.condition) errors.value.condition = 'Condition is required';
    if (!form.status) errors.value.status = 'Status is required';
    return Object.keys(errors.value).length === 0; // Return true if no errors
};

const submit = () => {
    if (!validate()) {
        console.log('Client-side validation failed:', errors.value);
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
            setTimeout(() => {
                window.location.href = route('inventory.index');
            }, 1000); // Delay for 1 second
        },
        onError: (serverErrors) => {
            console.log('Server validation errors:', serverErrors);
            errors.value = serverErrors;
        },
        onFinish: () => {
            console.log('Request finished, errors:', errors.value);
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <h2 class="text-lg font-semibold text-gray-900">Create Asset</h2>

        <div class="grid grid-cols-3 gap-4 text-black">
            <div>
                <Label for="asset_id">Asset ID</Label>
                <Input
                    id="asset_id"
                    v-model="form.asset_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.asset_id" class="text-red-600 text-sm">{{ errors.asset_id }}</span>
            </div>
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
        </div>

        <div class="grid grid-cols-3 gap-4 text-black">
            <div>
                <Label for="location">Location</Label>
                <Input
                    id="location"
                    v-model="form.location"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.location" class="text-red-600 text-sm">{{ errors.location }}</span>
            </div>
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
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.value" class="text-red-600 text-sm">{{ errors.value }}</span>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 text-black">
            <div>
                <Label for="condition">Condition</Label>
                <Input
                    id="condition"
                    v-model="form.condition"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.condition" class="text-red-600 text-sm">{{ errors.condition }}</span>
            </div>
            <div>
                <Label for="assigned">Assigned To</Label>
                <select
                    id="assigned"
                    v-model="form.assigned"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="">Select Employee</option>
                    <option
                        v-for="employee in employees"
                        :key="employee.id"
                        :value="employee.id" 
                    >
                        {{ employee.first_name }} {{ employee.last_name }}
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
                @click="closeModal"
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
</template>