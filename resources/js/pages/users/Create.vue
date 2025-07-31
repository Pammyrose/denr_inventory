
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
    firstName: '',
    middleName: '',
    lastName: '',
    suffix: '',
    sex: '',
    email: '',
    emp_status: '',
    position: '',
    assignment: '',
});

// Reactive errors for client-side validation
const errors = ref<{ [key: string]: string }>({});

// Validate required fields before submission
const validate = () => {
    errors.value = {};
    if (!form.firstName) errors.value.firstName = 'First name is required';
    if (!form.lastName) errors.value.lastName = 'Last name is required';
    if (!form.email) errors.value.email = 'Email is required';
    if (!form.sex) errors.value.sex = 'Sex is required';
    return Object.keys(errors.value).length === 0;
};

// Submit form to backend
const submit = () => {
    if (!validate()) return; // Stop if validation fails
    form.post(route('users.index'), {
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
        <h2 class="text-lg font-semibold text-gray-900">Create Users</h2>

        <!-- First row: Name fields -->
        <div class="grid grid-row-4 gap-4 text-black">
            <div>
                <Label for="firstName">Name</Label>
                <Input
                    id="firstName"
                    v-model="form.firstName"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.firstName }"
                />
                <p v-if="errors.firstName" class="mt-1 text-sm text-red-500">{{ errors.firstName }}</p>
            </div>
            <div>
                <Label for="middleName">Email</Label>
                <Input
                    id="middleName"
                    v-model="form.middleName"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="lastName">New Password</Label>
                <Input
                    id="lastName"
                    v-model="form.lastName"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.lastName }"
                />
                <p v-if="errors.lastName" class="mt-1 text-sm text-red-500">{{ errors.lastName }}</p>
            </div>
            <div>
                <Label for="suffix">Confirm Password</Label>
                <Input
                    id="suffix"
                    v-model="form.suffix"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
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
