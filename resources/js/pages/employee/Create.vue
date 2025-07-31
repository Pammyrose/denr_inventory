
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
    form.post(route('employee.index'), {
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
        <h2 class="text-lg font-semibold text-gray-900">Create Employee</h2>

        <!-- First row: Name fields -->
        <div class="grid grid-cols-4 gap-4 text-black">
            <div>
                <Label for="firstName">First Name</Label>
                <Input
                    id="firstName"
                    v-model="form.firstName"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.firstName }"
                />
                <p v-if="errors.firstName" class="mt-1 text-sm text-red-500">{{ errors.firstName }}</p>
            </div>
            <div>
                <Label for="middleName">Middle Name</Label>
                <Input
                    id="middleName"
                    v-model="form.middleName"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="lastName">Last Name</Label>
                <Input
                    id="lastName"
                    v-model="form.lastName"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.lastName }"
                />
                <p v-if="errors.lastName" class="mt-1 text-sm text-red-500">{{ errors.lastName }}</p>
            </div>
            <div>
                <Label for="suffix">Suffix</Label>
                <Input
                    id="suffix"
                    v-model="form.suffix"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
        </div>

        <!-- Second row: Sex and Email -->
        <div class="flex justify-center gap-6 text-black">
            <div>
                <Label for="sex">Sex</Label>
                <select
                    id="sex"
                    v-model="form.sex"
                    class="mt-1 block w-[200px] rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.sex }"
                >
                    <option selected disabled value="">Select Gender</option>
                    <option value="female">Female</option>
                    <option value="male">Male</option>
                    <option value="other">Other</option>
                </select>
                <p v-if="errors.sex" class="mt-1 text-sm text-red-500">{{ errors.sex }}</p>
            </div>
            <div>
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    class="mt-1 block w-[200px] rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    :class="{ 'border-red-500': errors.email }"
                />
                <p v-if="errors.email" class="mt-1 text-sm text-red-500">{{ errors.email }}</p>
            </div>
        </div>

        <!-- Third row: Employee details -->
        <div class="grid grid-cols-3 gap-4 text-black">
            <div>
                <Label for="emp_status">Employee Status</Label>
                <Input
                    id="emp_status"
                    v-model="form.emp_status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="position">Position</Label>
                <Input
                    id="position"
                    v-model="form.position"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="assignment">Assignment</Label>
                <Input
                    id="assignment"
                    v-model="form.assignment"
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
