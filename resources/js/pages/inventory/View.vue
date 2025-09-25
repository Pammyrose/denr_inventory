<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Button from '@/components/ui/button/Button.vue';
import { type BreadcrumbItem } from '@/types';

interface Asset {
    id: string;
    name: string;
    category: string;
    location: string;
    purchase_date: string;
    value: number;
    condition: string;
    assigned_to: string;
    employee_id: number | null;
    status: string;
    qr_code: string | null;
}

const props = defineProps<{
    asset?: Asset;
}>();

console.log('View.vue props:', props.asset); // Debug the asset prop

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Inventory', href: '/inventory' },
    { title: 'View Asset', href: '#' },
];

const downloadQrCode = () => {
    if (!props.asset?.qr_code) return;
    const link = document.createElement('a');
    link.href = props.asset.qr_code;
    link.download = `asset_${props.asset.id}.png`; // Changed to .png
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
};
</script>

<template>
    <Head :title="asset ? `Asset ${asset.id}` : 'Asset Not Found'" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <h2 class="text-2xl font-semibold text-gray-900 mb-4">Asset Details</h2>
            <div v-if="asset" class="bg-white shadow-md rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p><strong>Asset ID:</strong> <span class="text-black text-sm">{{ asset.id }}</span></p>
                        <p><strong>Name:</strong> <span class="text-black text-sm">{{ asset.name }}</span></p>
                        <p><strong>Category:</strong> <span class="text-black text-sm">{{ asset.category }}</span></p>
                        <p><strong>Location:</strong> <span class="text-black text-sm">{{ asset.location }}</span></p>
                        <p><strong>Purchase Date:</strong> <span class="text-black text-sm">{{ asset.purchase_date }}</span></p>
                    </div>
                    <div>
                        <p><strong>Value:</strong> <span class="text-black text-sm">₱{{ asset.value.toFixed(2) }}</span></p>
                        <p><strong>Condition:</strong> <span class="text-black text-sm">{{ asset.condition }}</span></p>
                        <p><strong>Assigned To:</strong> <span class="text-black text-sm">{{ asset.assigned_to }}</span></p>
                        <p><strong>Status:</strong> <span class="text-black text-sm">{{ asset.status }}</span></p>
                    </div>
                </div>
                <div v-if="asset.qr_code" class="mt-4 text-center">
                    <h3 class="text-lg font-semibold">QR Code</h3>
                    <img :src="asset.qr_code" alt="Asset QR Code" class="mx-auto mt-2" style="width: 200px; height: 200px;" />
                    <Button @click="downloadQrCode" class="mt-2 bg-blue-600 text-white hover:bg-blue-700">
                        Download QR Code
                    </Button>
                </div>
                <div v-else class="mt-4 text-center text-red-600">
                    <p>Failed to generate QR code. Please try again.</p>
                </div>
            </div>
            <div v-else class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">Asset not found or failed to load.</span>
            </div>
        </div>
    </AppLayout>
</template>