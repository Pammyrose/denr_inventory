<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

interface InventoryItem {
    id: string;
    name: string;
    category: string;
    location: string;
    purchase_date: string;
    value: string | number;
    condition: string;
    assigned_to: number | null;
    status: string;
    image: string | null;
    return_date: string | null;
}

interface Employee {
    id: number;
    full_name: string;
}

const props = defineProps<{
    item: InventoryItem;
    employees: Employee[];
}>();

// Debug props
console.log('Edit.vue props:', {
    item: props.item,
    assigned_to: props.item.assigned_to,
    employees: props.employees
});

const form = useForm({
    name: props.item.name || '',
    category: props.item.category || '',
    location: props.item.location || '',
    purchase_date: props.item.purchase_date || '',
    value: props.item.value ? String(props.item.value) : '',
    condition: props.item.condition || '',
    assigned_to: props.item.assigned_to ? Number(props.item.assigned_to) : null,
    status: props.item.status || '',
    return_date: props.item.return_date || '',
    image: null as File | null,
});

const errors = ref<{ [key: string]: string }>({});
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

// Image preview handling
const imagePreview = ref<string | null>(props.item.image || null);
const onImageChange = (event: Event) => {
    const input = event.target as HTMLInputElement;
    if (input.files && input.files[0]) {
        form.image = input.files[0];
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(input.files[0]);
        console.log('Image selected:', {
            name: form.image.name,
            size: form.image.size,
            type: form.image.type
        });
    } else {
        form.image = null;
        imagePreview.value = props.item.image || null;
        console.log('Image cleared');
    }
};

// Watch form data for debugging
watch(() => form.data(), (newData) => {
    console.log('Form data changed:', newData);
});

const validate = () => {
    errors.value = {};
    if (!form.name) errors.value.name = 'Name is required';
    if (!form.category) errors.value.category = 'Category is required';
    if (!form.location) errors.value.location = 'Location is required';
    if (!form.purchase_date) errors.value.purchase_date = 'Purchase date is required';
    if (!form.value || isNaN(parseFloat(form.value))) errors.value.value = 'Value must be a valid number';
    if (parseFloat(form.value) < 0 || parseFloat(form.value) > 9999999.99) {
        errors.value.value = 'Value must be between 0 and 9999999.99';
    }
    if (!form.condition) errors.value.condition = 'Condition is required';
    if (!form.status) errors.value.status = 'Status is required';
    if (form.assigned_to && !props.employees.some(emp => emp.id === form.assigned_to)) {
        errors.value.assigned_to = 'Invalid employee selected';
    }
    if (form.return_date && !/^\d{4}-\d{2}-\d{2}$/.test(form.return_date)) {
        errors.value.return_date = 'Return date must be a valid date';
    }
    if (form.image && !['image/jpeg', 'image/png', 'image/jpg', 'image/gif'].includes(form.image.type)) {
        errors.value.image = 'Image must be a valid image file (jpeg, png, jpg, gif)';
    }
    if (form.image && form.image.size > 2048 * 1024) {
        errors.value.image = 'Image size must not exceed 2MB';
    }
    console.log('Client-side validation errors:', errors.value);
    return Object.keys(errors.value).length === 0;
};

const submit = () => {
    alertMessage.value = '';
    alertType.value = '';
    form.value = parseFloat(form.value) || 0;
    if (!validate()) {
        console.log('Client-side validation failed:', errors.value);
        alertMessage.value = Object.values(errors.value).join('; ');
        alertType.value = 'error';
        return;
    }

    const formData = form.data();
    console.log('Submitting form with data:', formData);

    form.put(route('inventory.update', { id: props.item.id }), {
        preserveState: true,
        preserveScroll: true,
        forceFormData: true,
        onBefore: () => {
            console.log('Starting form submission');
        },
        onSuccess: () => {
            console.log('Form submitted successfully');
            alertMessage.value = 'Inventory item updated successfully.';
            alertType.value = 'success';
            setTimeout(() => {
                form.reset();
                alertMessage.value = '';
                alertType.value = '';
                imagePreview.value = null;
                router.visit(route('inventory.index'), {
                    method: 'get',
                    preserveState: false,
                    onSuccess: () => {
                        console.log('Redirected to inventory.index');
                    },
                });
            }, 1000);
        },
        onError: (serverErrors) => {
            console.log('Server validation errors:', serverErrors);
            errors.value = serverErrors;
            alertMessage.value = serverErrors.image || serverErrors.general || Object.values(serverErrors).join('; ') || 'Failed to update inventory item';
            alertType.value = 'error';
        },
        onFinish: () => {
            console.log('Request finished, errors:', errors.value);
        },
    });
};
</script>

<template>
    <div class="relative">
        <!-- Alert -->
        <div
            v-if="alertMessage && alertType"
            class="bg-green-100 border-l-4 border-green-400 text-green-700 p-4 rounded-r-md"
            :class="{ 'bg-red-100 border-red-400 text-red-700': alertType === 'error' }"
            role="alert"
        >
            <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
            <p>{{ alertMessage }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Update Inventory Item</h2>

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
                        v-model="form.purchase_date"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.purchase_date" class="text-red-600 text-sm">{{ errors.purchase_date }}</span>
                </div>
                <div>
                    <Label for="value">Value</Label>
                    <Input
                        id="value"
                        v-model="form.value"
                        type="number"
                        step="0.01"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.value" class="text-red-600 text-sm">{{ errors.value }}</span>
                </div>
                <div>
                    <Label for="condition">Condition</Label>
                    <select
                        id="condition"
                        v-model="form.condition"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="" disabled>Select Condition</option>
                        <option value="New">New</option>
                        <option value="Good">Good</option>
                        <option value="Fair">Fair</option>
                        <option value="Poor">Poor</option>
                    </select>
                    <span v-if="errors.condition" class="text-red-600 text-sm">{{ errors.condition }}</span>
                </div>
                                <div>
                    <Label for="property_no">Property Number</Label>
                    <Input
                        id="property_no"
                        v-model="form.property_no"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.property_no" class="text-red-600 text-sm">{{ errors.property_no }}</span>
                </div>
                <div>
                    <Label for="serial_no">Serial Number</Label>
                    <Input
                        id="serial_no"
                        v-model="form.serial_no"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.serial_no" class="text-red-600 text-sm">{{ errors.serial_no }}</span>
                </div>
                <div>
                    <Label for="serviceable">Serviceable</Label>
                    <Input
                        id="serviceable"
                        v-model="form.serviceable"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.serviceable" class="text-red-600 text-sm">{{ errors.serviceable }}</span>
                </div>
                                <div>
                    <Label for="unserviceable">Unserviceable</Label>
                    <Input
                        id="unserviceable"
                        v-model="form.unserviceable"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.unserviceable" class="text-red-600 text-sm">{{ errors.unserviceable }}</span>
                </div>
                <div>
                    <Label for="coa_representative">COA Representative</Label>
                    <Input
                        id="coa_representative"
                        v-model="form.coa_representative"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.coa_representative" class="text-red-600 text-sm">{{ errors.coa_representative }}</span>
                </div>
                <div>
                    <Label for="coa_date">COA Date</Label>
                    <Input
                        id="coa_date"
                        type="date"
                        v-model="form.coa_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.coa_date" class="text-red-600 text-sm">{{ errors.coa_date }}</span>
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4 text-black">
                <div>
                    <Label for="assigned_to">Assigned To</Label>
                    <select
                        id="assigned_to"
                        v-model="form.assigned_to"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option :value="null">Unassigned</option>
                        <option v-for="employee in props.employees" :key="employee.id" :value="employee.id">
                            {{ employee.full_name }}
                        </option>
                    </select>
                    <span v-if="errors.assigned_to" class="text-red-600 text-sm">{{ errors.assigned_to }}</span>
                </div>
                                                <div>
                    <Label for="assigned_date">Assigned Date</Label>
                    <Input
                        id="assigned_date"
                        type="date"
                        v-model="form.assigned_date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.assigned_date" class="text-red-600 text-sm">{{ errors.assigned_date }}</span>
                </div>
                                                                <div>
                    <Label for="unit_qty">Unit / Qty</Label>
                    <Input
                        id="unit_qty"
                        type="number"
                        v-model="form.unit_qty"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.assigned_date" class="text-red-600 text-sm">{{ errors.unit_qty }}</span>
                </div>
                <div>
                    <Label for="status">Status</Label>
                    <select
                        id="status"
                        v-model="form.status"
                        class="mt-1 block w-full rounded-md border border-gray-300 bg-white py-2 px-3 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option value="" disabled>Select Status</option>
                        <option value="Good">Good</option>
                        <option value="Check">Check</option>
                        <option value="Repair">Repair</option>
                        <option value="Upgrade">Upgrade</option>
                    </select>
                    <span v-if="errors.status" class="text-red-600 text-sm">{{ errors.status }}</span>
                </div>
                                <div>
                    <Label for="image">Image</Label>
                    <Input
                        id="image"
                        type="file"
                        accept="image/*"
                        @change="onImageChange"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.image" class="text-red-600 text-sm">{{ errors.image }}</span>
                    <img
                        v-if="imagePreview"
                        :src="imagePreview"
                        alt="Image Preview"
                        class="mt-2 max-w-full h-auto"
                        style="max-width: 200px;"
                    />
                </div>
                <div>
                    <Label for="return_date">Return Date</Label>
                    <Input
                        id="return_date"
                        v-model="form.return_date"
                        type="date"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    />
                    <span v-if="errors.return_date" class="text-red-600 text-sm">{{ errors.return_date }}</span>
                </div>
            </div>

         

            <div class="flex justify-end gap-3">
                <Button
                    type="button"
                    @click="router.visit(route('inventory.index'))"
                    class="bg-gray-200 text-gray-800 hover:bg-gray-300"
                >
                    Cancel
                </Button>
                <Button
                    type="submit"
                    class="bg-blue-600 text-white hover:bg-blue-700"
                    :disabled="form.processing"
                >
                    Update
                </Button>
            </div>
        </form>
    </div>
</template>