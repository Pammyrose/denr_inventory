<script setup lang="ts">
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

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
}

const props = defineProps<{
    employee: Employee;
    orgUnits?: { value: string; label: string }[];
    positions?: { value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }[];
    assignmentPlaces?: { value: string; label: string }[];
    empStatuses?: { value: string; label: string }[];
    close: () => void;
}>();

// Compute the salary grade based on the selected position_id
const selectedPosition = computed(() => {
    return props.positions?.find(pos => pos.value === form.position_id);
});

const form = useForm({
    first_name: props.employee.first_name,
    middle_name: props.employee.middle_name || '',
    last_name: props.employee.last_name,
    suffix: props.employee.suffix || '',
    sex: props.employee.sex,
    email: props.employee.email,
    password: '',
    password_confirmation: '',
    emp_status: props.employee.emp_status,
    position_id: props.employee.position_id ? String(props.employee.position_id) : '',
    assignment_id: props.employee.assignment_id ? String(props.employee.assignment_id) : '',
    org_unit_id: props.employee.org_unit_id ? String(props.employee.org_unit_id) : '',
});

const errors = ref<{ [key: string]: string }>({});
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

const validate = () => {
    errors.value = {};
    if (!form.first_name) errors.value.first_name = 'First name is required';
    if (!form.last_name) errors.value.last_name = 'Last name is required';
    if (!form.sex) errors.value.sex = 'Sex is required';
    if (!form.emp_status) errors.value.emp_status = 'Employee status is required';
    if (!form.position_id) errors.value.position_id = 'Position is required';
    if (!form.assignment_id) errors.value.assignment_id = 'Assignment is required';
    if (!form.org_unit_id) errors.value.org_unit_id = 'Division Section Unit is required';
    if (form.password && form.password !== form.password_confirmation) errors.value.password_confirmation = 'Passwords do not match';
    return Object.keys(errors.value).length === 0;
};

const submit = () => {
    alertMessage.value = '';
    alertType.value = '';
    if (!validate()) {
        console.log('Edit.vue: Client-side validation failed:', errors.value);
        alertMessage.value = Object.values(errors.value).join('; ');
        alertType.value = 'error';
        return;
    }

    console.log('Edit.vue: Submitting form with data:', form.data());

    form.put(route('employee.update', { id: props.employee.id }), {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            alertMessage.value = 'Employee updated successfully.';
            alertType.value = 'success';
            setTimeout(() => {
                form.reset();
                props.close();
            }, 2000);
        },
        onError: (serverErrors) => {
            console.log('Edit.vue: Server validation errors:', serverErrors);
            errors.value = serverErrors;
            alertMessage.value = serverErrors.general || Object.values(serverErrors).join('; ') || 'Failed to update employee';
            alertType.value = 'error';
        },
    });
};
</script>

<template>
    <div class="relative">
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

        <form @submit.prevent="submit" class="space-y-10">
            <h2 class="text-lg font-semibold text-gray-900">Update Employee</h2>
            <div v-if="Object.keys(errors).length" class="text-red-600 text-sm mb-4">
                <p v-for="(error, field) in errors" :key="field">{{ error }}</p>
            </div>

            <!-- Name fields -->
            <div class="grid grid-cols-4 gap-4 text-black">
                <div>
                    <Label for="first_name">First Name</Label>
                    <Input id="first_name" v-model="form.first_name" />
                    <span v-if="errors.first_name" class="text-red-600 text-sm">{{ errors.first_name }}</span>
                </div>
                <div>
                    <Label for="middle_name">Middle Name</Label>
                    <Input id="middle_name" v-model="form.middle_name" />
                </div>
                <div>
                    <Label for="last_name">Last Name</Label>
                    <Input id="last_name" v-model="form.last_name" />
                    <span v-if="errors.last_name" class="text-red-600 text-sm">{{ errors.last_name }}</span>
                </div>
                <div>
                    <Label for="suffix">Suffix</Label>
                    <Input id="suffix" v-model="form.suffix" />
                </div>
            </div>

            <!-- Org/Position/Salary -->
            <div class="grid grid-cols-4 gap-4 text-black">
                <div>
                    <Label for="sex">Sex</Label>
                    <select id="sex" v-model="form.sex">
                        <option value="" disabled>Select Sex</option>
                        <option value="M">Male</option>
                        <option value="F">Female</option>
                    </select>
                    <span v-if="errors.sex" class="text-red-600 text-sm">{{ errors.sex }}</span>
                </div>

                <div class="relative">
                    <Label for="org_unit_id">Division Section Unit</Label>
                    <select id="org_unit_id" v-model="form.org_unit_id">
                        <option value="" disabled>Select Division Section Unit</option>
                        <option v-for="unit in props.orgUnits" :key="unit.value" :value="unit.value">
                            {{ unit.label }}
                        </option>
                    </select>
                    <span v-if="errors.org_unit_id" class="text-red-600 text-sm">{{ errors.org_unit_id }}</span>
                </div>

                <div class="relative">
                    <Label for="position_id">Position</Label>
                    <select id="position_id" v-model="form.position_id" required>
                        <option value="" disabled>Select Position</option>
                        <option v-for="pos in props.positions" :key="pos.value" :value="pos.value">
                            {{ pos.item_code }} - {{ pos.label }}
                        </option>
                    </select>
                    <span v-if="errors.position_id" class="text-red-600 text-sm">{{ errors.position_id }}</span>
                </div>

                <div class="relative">
                    <Label for="salary_grade">Salary Grade</Label>
                    <Input
                        id="salary_grade"
                        :value="selectedPosition?.salary_grade || 'N/A'"
                        disabled
                    />
                    <span v-if="errors.salary_grade" class="text-red-600 text-sm">{{ errors.salary_grade }}</span>
                </div>
            </div>

            <!-- Status / Assignment / Email -->
            <div class="grid grid-cols-3 gap-4 text-black">
                <div class="relative">
                    <Label for="emp_status">Employee Status</Label>
                    <select id="emp_status" v-model="form.emp_status" required>
                        <option value="" disabled>Select Employee Status</option>
                        <option v-for="status in props.empStatuses" :key="status.value" :value="status.value">
                            {{ status.label }}
                        </option>
                    </select>
                    <span v-if="errors.emp_status" class="text-red-600 text-sm">{{ errors.emp_status }}</span>
                </div>
                <div class="relative">
                    <Label for="assignment_id">Assignment</Label>
                    <select id="assignment_id" v-model="form.assignment_id">
                        <option value="" disabled>Select Assignment</option>
                        <option v-for="opt in props.assignmentPlaces" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <span v-if="errors.assignment_id" class="text-red-600 text-sm">{{ errors.assignment_id }}</span>
                </div>

                <div>
                    <Label for="email">Email</Label>
                    <Input id="email" type="email" v-model="form.email" />
                    <span v-if="errors.email" class="text-red-600 text-sm">{{ errors.email }}</span>
                </div>
            </div>

            <!-- Password -->
            <div class="grid grid-cols-2 gap-4 text-black">
                <div>
                    <Label for="password">Password</Label>
                    <Input id="password" type="password" v-model="form.password" />
                    <span v-if="errors.password" class="text-red-600 text-sm">{{ errors.password }}</span>
                </div>
                <div>
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input id="password_confirmation" type="password" v-model="form.password_confirmation" />
                    <span v-if="errors.password_confirmation" class="text-red-600 text-sm">{{ errors.password_confirmation }}</span>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <Button type="button" @click="props.close" class="bg-gray-200 text-gray-800 hover:bg-gray-300">
                    Cancel
                </Button>
                <Button type="submit" class="bg-blue-600 text-white hover:bg-blue-700" :disabled="form.processing">
                    Update
                </Button>
            </div>
        </form>
    </div>
</template>

<style scoped>
select,
input[disabled] {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: #f3f4f6;
}
</style>