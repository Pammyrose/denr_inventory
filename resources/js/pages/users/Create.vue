<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Button from '@/components/ui/button/Button.vue';
import Input from '@/components/ui/input/Input.vue';
import Label from '@/components/ui/label/Label.vue';
import InputError from '@/components/InputError.vue';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    closeModal: () => void;
}>();

const form = useForm({
    full_name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const errors = ref<{ [key: string]: string }>({});
const alertMessage = ref<string>('');
const alertType = ref<'success' | 'error' | ''>('');

const validate = () => {
    errors.value = {};
    if (!form.full_name) errors.value.full_name = 'Full name is required';
    if (!form.email) errors.value.email = 'Email is required';
    if (!form.password) errors.value.password = 'Password is required';
    if (!form.password_confirmation) errors.value.password_confirmation = 'Password confirmation is required';
    if (form.password !== form.password_confirmation) errors.value.password_confirmation = 'Passwords do not match';
    return Object.keys(errors.value).length === 0;
};

const submit = () => {
    alertMessage.value = '';
    alertType.value = '';
    if (!validate()) {
        alertMessage.value = Object.values(errors.value).join('; ');
        alertType.value = 'error';
        return;
    }
    form.post(route('users.store'), {
        preserveScroll: true,
        onSuccess: () => {
            alertMessage.value = 'User created successfully.';
            alertType.value = 'success';
            setTimeout(() => {
                form.reset();
                alertMessage.value = '';
                alertType.value = '';
                props.closeModal();
                router.visit(route('user.index'), {
                    method: 'get',
                    preserveState: false,
                });
            }, 3000);
        },
        onError: (serverErrors) => {
            errors.value = { ...errors.value, ...serverErrors };
            alertMessage.value = serverErrors.general || Object.values(serverErrors).join('; ') || 'Failed to create user';
            alertType.value = 'error';
        },
    });
};
</script>

<template>
    <div class="relative">
        <div
            v-if="alertMessage"
            :class="{
                'bg-green-100 border-green-400 text-green-700': alertType === 'success',
                'bg-red-100 border-red-400 text-red-700': alertType === 'error',
            }"
            class="border-l-4 p-4 mb-4 rounded-r-md"
            role="alert"
            style="position: relative; z-index: 1000;"
        >
            <p class="font-medium">{{ alertType === 'success' ? 'Success' : 'Error' }}</p>
            <p>{{ alertMessage }}</p>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Create User</h2>
            <div class="grid gap-4 text-black">
                <div>
                    <Label for="full_name">Full Name</Label>
                    <Input
                        id="full_name"
                        type="text"
                        v-model="form.full_name"
                        required
                        autocomplete="name"
                        placeholder="Full name"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.full_name }"
                    />
                    <InputError :message="errors.full_name" />
                </div>
                <div>
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autocomplete="email"
                        placeholder="email@example.com"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.email }"
                    />
                    <InputError :message="errors.email" />
                </div>
                <div>
                    <Label for="password">Password</Label>
                    <Input
                        id="password"
                        type="password"
                        v-model="form.password"
                        required
                        autocomplete="new-password"
                        placeholder="Password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.password }"
                    />
                    <InputError :message="errors.password" />
                </div>
                <div>
                    <Label for="password_confirmation">Confirm Password</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        v-model="form.password_confirmation"
                        required
                        autocomplete="new-password"
                        placeholder="Confirm password"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        :class="{ 'border-red-500': errors.password_confirmation }"
                    />
                    <InputError :message="errors.password_confirmation" />
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
                    :disabled="form.processing"
                >
                    <LoaderCircle v-if="form.processing" class="h-4 w-4 animate-spin mr-2" />
                    Create account
                </Button>
            </div>
        </form>
    </div>
</template>