<script setup lang="ts">
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

defineProps<{
    closeModal: () => void;
}>();

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    sex: '',
    email: '',
    emp_status: '',
    position_name: '',
    assignment_name: '',
    div_sec_unit: '',
});

const errors = ref<{ [key: string]: string }>({});

const validate = () => {
    errors.value = {};
    if (!form.first_name) errors.value.first_name = 'First name is required';
    if (!form.last_name) errors.value.last_name = 'Last name is required';
    if (!form.email) errors.value.email = 'Email is required';
    if (!form.sex) errors.value.sex = 'Sex is required';
    if (!form.emp_status) errors.value.emp_status = 'Employee status is required';
    if (!form.position_name) errors.value.position_name = 'Position is required';
    if (!form.assignment_name) errors.value.assignment_name = 'Assignment is required';
    if (!form.div_sec_unit) errors.value.div_sec_unit = 'Division Section Unit is required';
    return Object.keys(errors.value).length === 0;
};

const submit = () => {
    if (!validate()) {
        console.log('Client-side validation failed:', errors.value);
        return;
    }

    console.log('Submitting form with data:', form.data());

    form.post(route('employee.store'), {
    preserveState: true,
    preserveScroll: true,
    onBefore: () => {
        console.log('Starting form submission');
    },
    onSuccess: () => {
    setTimeout(() => {
        window.location.href = route('employee.index');
    }, 1000); // delay for 1 second
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
        <h2 class="text-lg font-semibold text-gray-900">Create Employee</h2>

        <div class="grid grid-cols-4 gap-4 text-black">
            <div>
                <Label for="first_name">First Name</Label>
                <Input
                    id="first_name"
                    v-model="form.first_name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.first_name" class="text-red-600 text-sm">{{ errors.first_name }}</span>
            </div>
            <div>
                <Label for="middle_name">Middle Name</Label>
                <Input
                    id="middle_name"
                    v-model="form.middle_name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
            </div>
            <div>
                <Label for="last_name">Last Name</Label>
                <Input
                    id="last_name"
                    v-model="form.last_name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.last_name" class="text-red-600 text-sm">{{ errors.last_name }}</span>
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

        <div class="flex justify-center gap-6 text-black">
            <div>
                <Label for="sex">Sex</Label>
                <select
                    id="sex"
                    v-model="form.sex"
                    class="mt-1 block w-[200px] rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                >
                    <option value="" disabled>Select Sex</option>
                    <option value="M">Male</option>
                    <option value="F">Female</option>
                </select>
                <span v-if="errors.sex" class="text-red-600 text-sm">{{ errors.sex }}</span>
            </div>
            <div>
                <Label for="email">Email</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-1 block w-[200px] rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.email" class="text-red-600 text-sm">{{ errors.email }}</span>
            </div>
        </div>

        <div class="grid grid-cols-4 gap-4 text-black">
            <div>
                <Label for="emp_status">Employee Status</Label>
                <Input
                    id="emp_status"
                    v-model="form.emp_status"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.emp_status" class="text-red-600 text-sm">{{ errors.emp_status }}</span>
            </div>
            <div>
                <Label for="position_name">Position</Label>
                <Input
                    id="position_name"
                    v-model="form.position_name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.position_name" class="text-red-600 text-sm">{{ errors.position_name }}</span>
            </div>
            <div>
                <Label for="assignment_name">Assignment</Label>
                <Input
                    id="assignment_name"
                    v-model="form.assignment_name"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.assignment_name" class="text-red-600 text-sm">{{ errors.assignment_name }}</span>
            </div>
            <div>
                <Label for="div_sec_unit">Division Section Unit</Label>
                <Input
                    id="div_sec_unit"
                    v-model="form.div_sec_unit"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
                <span v-if="errors.div_sec_unit" class="text-red-600 text-sm">{{ errors.div_sec_unit }}</span>
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