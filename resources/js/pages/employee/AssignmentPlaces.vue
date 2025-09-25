
<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';

const emit = defineEmits(['add-assignment-place', 'close']);

const assignmentPlaceForm = useForm({
    name: '',
    desc: '',
});

const assignmentPlaceErrors = ref<{ [key: string]: string }>({});

const validateAssignmentPlace = () => {
    assignmentPlaceErrors.value = {};
    if (!assignmentPlaceForm.name) assignmentPlaceErrors.value.name = 'Name is required';
    return Object.keys(assignmentPlaceErrors.value).length === 0;
};

const submitAssignmentPlace = () => {
    if (!validateAssignmentPlace()) {
        console.log('Client-side validation failed for assignment place:', assignmentPlaceErrors.value);
        return;
    }

    console.log('Submitting assignment place form with data:', assignmentPlaceForm.data());

    assignmentPlaceForm.post(route('employee.assignmentPlace'), {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('Assignment place submission successful. Flash data:', page.props.flash);
            const newAssignmentPlace = page.props.flash.assignmentPlace || { value: page.props.flash.id, label: assignmentPlaceForm.name };
            emit('add-assignment-place', newAssignmentPlace);
            assignmentPlaceForm.reset();
            emit('close');
        },
        onError: (serverErrors) => {
            console.log('Server validation errors for assignment place:', serverErrors);
            assignmentPlaceErrors.value = serverErrors;
        },
    });
};
</script>

<template>
    <form @submit.prevent="submitAssignmentPlace" class="space-y-6">
        <div>
            <Label for="name">Name</Label>
            <Input
                id="name"
                v-model="assignmentPlaceForm.name"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span v-if="assignmentPlaceErrors.name" class="text-red-600 text-sm">{{ assignmentPlaceErrors.name }}</span>
        </div>
        <div>
            <Label for="desc">Description</Label>
            <Input
                id="desc"
                v-model="assignmentPlaceForm.desc"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
            <span v-if="assignmentPlaceErrors.desc" class="text-red-600 text-sm">{{ assignmentPlaceErrors.desc }}</span>
        </div>
        <div class="flex justify-end gap-3">
            <Button
                type="button"
                @click="emit('close')"
                class="bg-gray-200 text-gray-800 hover:bg-gray-300"
            >
                Cancel
            </Button>
            <Button
                type="submit"
                class="bg-blue-600 text-white hover:bg-blue-700"
                :disabled="assignmentPlaceForm.processing"
            >
                Submit
            </Button>
        </div>
    </form>
</template>

<style scoped>
/* Form styling */
</style>
