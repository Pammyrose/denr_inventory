<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import Modal from '@/pages/employee/Modal.vue';
import OrgUnitForm from '@/pages/employee/OrgUnitForm.vue';
import SalaryGradeForm from '@/pages/employee/SalaryGrade.vue';
import AssignmentPlaces from '@/pages/employee/AssignmentPlaces.vue';
import PositionForm from '@/pages/employee/PositionForm.vue';

const props = defineProps<{
    orgUnits?: { value: string; label: string }[];
    positions?: { value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }[];
    assignmentPlaces?: { value: string; label: string }[];
    empStatuses?: { value: string; label: string }[];
    error?: string;
    close: () => void;
}>();

const emit = defineEmits(['close']);

const orgUnitsLocal = ref(props.orgUnits || []);
const assignmentOptions = ref(props.assignmentPlaces || []);
const positionsLocal = ref(props.positions || []);
const employeeStatuses = ref(props.empStatuses || []);

onMounted(() => {
    console.log('Create.vue: Initial props:', {
        orgUnits: props.orgUnits,
        positions: props.positions,
        assignmentPlaces: props.assignmentPlaces,
        empStatuses: props.empStatuses,
    });
});

const form = useForm({
    first_name: '',
    middle_name: '',
    last_name: '',
    suffix: '',
    sex: '',
    email: '',
    password: '',
    password_confirmation: '',
    emp_status: '',
    position_id: '',
    assignment_id: '',
    org_unit_id: '',
    salary_grade: '',
});

const errors = ref<{ [key: string]: string }>({});
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>(''); // Controls alert visibility and type
const showModal = ref(false);
const modalContent = ref<'orgUnit' | 'salaryGrade' | 'assignmentPlace' | 'position' | null>(null);

const salaryGradeOptions = ref<{ value: string; label: string }[]>([{ value: '', label: 'Select Salary Grade' }]);

watch(
    () => form.position_id,
    (newPositionId) => {
        console.log('Create.vue: Position changed:', newPositionId);
        const selectedPosition = positionsLocal.value.find((p) => p.value === newPositionId);
        salaryGradeOptions.value = selectedPosition?.salary_grade
            ? [{ value: selectedPosition.salary_grade, label: selectedPosition.salary_grade }]
            : [{ value: '', label: 'Select Salary Grade' }];
        form.salary_grade = selectedPosition?.salary_grade || '';
    }
);

const validate = () => {
    errors.value = {};
    if (!form.first_name) errors.value.first_name = 'First name is required';
    if (!form.last_name) errors.value.last_name = 'Last name is required';
    if (!form.email) errors.value.email = 'Email is required';
    if (!form.sex) errors.value.sex = 'Sex is required';
    if (!form.emp_status) errors.value.emp_status = 'Employee status is required';
    if (!form.position_id) errors.value.position_id = 'Position is required';
    if (!form.assignment_id) errors.value.assignment_id = 'Assignment is required';
    if (!form.org_unit_id) errors.value.org_unit_id = 'Division Section Unit is required';
    if (!form.password) errors.value.password = 'Password is required';
    if (!form.password_confirmation) errors.value.password_confirmation = 'Password confirmation is required';
    if (form.password !== form.password_confirmation)
        errors.value.password_confirmation = 'Passwords do not match';
    return Object.keys(errors.value).length === 0;
};

const submit = () => {
    console.log('Create.vue: Submitting form with data:', form.data());
    alertMessage.value = ''; // Reset alert
    alertType.value = '';
    if (!validate()) {
        console.log('Create.vue: Validation failed:', errors.value);
        alertMessage.value = Object.values(errors.value).join('; ');
        alertType.value = 'error';
        return;
    }
    form.post(route('employee.store'), {
        preserveScroll: true,
        onSuccess: () => {
            console.log('Create.vue: Form submitted successfully');
            alertMessage.value = 'Employee created successfully.';
            alertType.value = 'success';
            setTimeout(() => {
                form.reset();
                props.close();
            }, 2000); // Delay to show alert before closing
        },
        onError: (serverErrors) => {
            console.log('Create.vue: Server errors:', serverErrors);
            errors.value = serverErrors;
            alertMessage.value = serverErrors.general || Object.values(serverErrors).join('; ') || 'Failed to create employee';
            alertType.value = 'error';
        },
    });
};

const openModal = (content: 'orgUnit' | 'salaryGrade' | 'assignmentPlace' | 'position') => {
    modalContent.value = content;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    modalContent.value = null;
};

const addOrgUnit = (newOrgUnit: { value: string; label: string }) => {
    if (!orgUnitsLocal.value.find((unit) => unit.value === newOrgUnit.value)) {
        orgUnitsLocal.value.push(newOrgUnit);
    }
    form.org_unit_id = newOrgUnit.value;
    closeModal();
};

const addSalaryGrade = (updatedPosition: { value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }) => {
    const positionIndex = positionsLocal.value.findIndex((p) => p.value === updatedPosition.value);
    if (positionIndex >= 0) {
        positionsLocal.value[positionIndex] = updatedPosition;
    } else {
        positionsLocal.value.push(updatedPosition);
    }
    if (form.position_id === updatedPosition.value) {
        form.salary_grade = updatedPosition?.salary_grade || '';
    }
    closeModal();
};

const addAssignmentPlace = (newAssignmentPlace: { value: string; label: string }) => {
    if (!assignmentOptions.value.find((opt) => opt.value === newAssignmentPlace.value)) {
        assignmentOptions.value.push(newAssignmentPlace);
    }
    form.assignment_id = newAssignmentPlace.value;
    closeModal();
};

const addPosition = (newPosition: { value: string; label: string; salary_grade: string | null; item_code: string; org_code: string }) => {
    if (!positionsLocal.value.find((pos) => pos.value === newPosition.value)) {
        positionsLocal.value.push(newPosition);
    }
    form.position_id = newPosition.value;
    closeModal();
};

const openSalaryGradeModal = () => {
    if (!form.position_id) {
        errors.value.position_id = 'Please select a position first';
        alertMessage.value = errors.value.position_id;
        alertType.value = 'error';
        return;
    }
    openModal('salaryGrade');
};

const addAssignment = () => {
    openModal('assignmentPlace');
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
            <h2 class="text-lg font-semibold text-gray-900">Create Employee</h2>
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
                        <option v-for="unit in orgUnitsLocal" :key="unit.value" :value="unit.value">
                            {{ unit.label }}
                        </option>
                    </select>
                    <Button type="button" @click="openModal('orgUnit')" class="absolute right-1 top-[2.25rem] bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                    <span v-if="errors.org_unit_id" class="text-red-600 text-sm">{{ errors.org_unit_id }}</span>
                </div>

                <div class="relative">
                    <Label for="position_id">Position</Label>
                    <select id="position_id" v-model="form.position_id" required>
                        <option value="" disabled>Select Position</option>
                        <option v-for="pos in positionsLocal" :key="pos.value" :value="pos.value">
                            {{ pos.item_code }} - {{ pos.label }}
                        </option>
                    </select>
                    <Button type="button" @click="openModal('position')" class="absolute right-1 top-[2.25rem] bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                    <span v-if="errors.position_id" class="text-red-600 text-sm">{{ errors.position_id }}</span>
                </div>

                <div class="relative">
                    <Label for="salary_grade">Salary Grade</Label>
                    <select id="salary_grade" v-model="form.salary_grade" disabled>
                        <option v-for="opt in salaryGradeOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <Button type="button" @click="openSalaryGradeModal" class="absolute right-1 top-[2.25rem] bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
                </div>
            </div>

            <!-- Status / Assignment / Email -->
            <div class="grid grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="emp_status">Employee Status</Label>
                    <select id="emp_status" v-model="form.emp_status" required>
                        <option value="" disabled>Select Employee Status</option>
                        <option v-for="status in employeeStatuses" :key="status.value" :value="status.value">
                            {{ status.label }}
                        </option>
                    </select>
                    <span v-if="errors.emp_status" class="text-red-600 text-sm">{{ errors.emp_status }}</span>
                </div>

                <div class="relative">
                    <Label for="assignment_id">Assignment</Label>
                    <select id="assignment_id" v-model="form.assignment_id">
                        <option value="" disabled>Select Assignment</option>
                        <option v-for="opt in assignmentOptions" :key="opt.value" :value="opt.value">
                            {{ opt.label }}
                        </option>
                    </select>
                    <Button type="button" @click="addAssignment" class="absolute right-1 top-[2.25rem] bg-gray-300 text-gray-600 hover:bg-gray-500 hover:text-white">+</Button>
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
                    Submit
                </Button>
            </div>
        </form>

        <Modal v-if="showModal" :close="closeModal">
            <template #header>
                <h2 class="text-lg font-semibold text-gray-900">
                    {{
                        modalContent === 'orgUnit' ? 'Add Division Section Unit' :
                        modalContent === 'salaryGrade' ? 'Update Salary Grade' :
                        modalContent === 'assignmentPlace' ? 'Add Assignment Place' :
                        'Add Position'
                    }}
                </h2>
            </template>
            <template #body>
                <OrgUnitForm v-if="modalContent === 'orgUnit'" @add-org-unit="addOrgUnit" @close="closeModal" />
                <SalaryGradeForm
                    v-if="modalContent === 'salaryGrade'"
                    :positions="positionsLocal"
                    :preselectedItemCode="form.position_id"
                    @store-salary-grade="addSalaryGrade"
                    @close="closeModal"
                />
                <AssignmentPlaces v-if="modalContent === 'assignmentPlace'" @add-assignment-place="addAssignmentPlace" @close="closeModal" />
                <PositionForm v-if="modalContent === 'position'" @add-position="addPosition" @close="closeModal" />
            </template>
        </Modal>
    </div>
</template>

<style scoped>
select {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background-color: #fff;
}
select.appearance-none {
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    padding-right: 2.5rem;
}
.relative .absolute {
    top: 2.1rem;
    right: 0rem;
    transform: translateY(-51%);
    height: 2.6rem;
}
</style>