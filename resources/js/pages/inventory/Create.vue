
<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

// Props to close the modal from Index.vue
defineProps<{
    closeModal: () => void;
}>();

// Form data with Inertia's useForm
const form = useForm({
    asset_id: '',
    name: '',
    category: '',
    location: '',
    purchase_date: '',
    value: '',
    condition: '',
    assigned: '',
    status: '',
});

// Reactive errors for client-side validation
const errors = ref<{ [key: string]: string }>({});


// Submit form to backend
const submit = () => {
    if (!validate()) return; // Stop if validation fails
    form.post(route('inventory.index'), {
        onSuccess: () => {
            form.reset(); // Reset form on success
            closeModal(); // Close modal
        },
        onError: (serverErrors) => {
            errors.value = { ...errors.value, ...serverErrors }; // Merge server errors
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <!-- Form title -->
        <h2 class="text-lg font-semibold text-gray-900">Create Asset</h2>

        <!-- First row: Name fields -->

 <div class="grid grid-cols-3 gap-4 text-black">
            <div>
                <Label for="asset_id">Asset ID</Label>
                <Input
                    id="asset_id"
                    v-model="form.asset_id"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="name">Name</Label>
                <Input
                    id="name"
                    v-model="form.name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="category">Category</Label>
                <Input
                    id="category"
                    v-model="form.category"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
        </div>

         <!-- Second row: Employee details -->
         <div class="grid grid-cols-3 gap-4 text-black">
            <div>
                <Label for="location">Location</Label>
                <Input
                    id="location"
                    v-model="form.location"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="purchase_date">Purchase Date</Label>
                <Input
                    id="purchase_date"
                    type="date"
                    v-model="form.purchase_date"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="value">Value</Label>
                <Input
    id="value"
    type="number"
    v-model.number="form.value"
    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
/>
            </div>
        </div>

        <!-- Third row: Employee details -->
        <div class="grid grid-cols-3 gap-4 text-black">
            <div>
                <Label for="condition">Condition</Label>
                <Input
                    id="condition"
                    v-model="form.condition"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="assigned">Assigned To</Label>
                <Input
                    id="assigned"
                    v-model="form.assigned"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="status">Status</Label>
                <select
                    id="status"
                    v-model="form.status"
                    class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.sex }"
                >
                    <option selected disabled value="">Select Status</option>
                    <option value="female">Check</option>
                    <option value="male">Repair</option>
                    <option value="other">Upgrade</option>
                </select>
            </div>
        </div>

        <!-- Form actions -->
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
