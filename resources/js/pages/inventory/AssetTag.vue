<template>
    <div class="flex justify-center items-center min-h-screen">
    <div v-if="asset" class="max-w-2xl mx-auto p-8 bg-white border-20 border-green-800 shadow-lg ">
        <!-- Header: COA Circular and Title -->
        <div class="text-center mb-8 flex items-center justify-start">
            <img src="/denr_logo.png" alt="Logo" class="w-20 h-20 mr-2" @error="imageError" @load="imageLoaded" />
            <div class="flex-1 text-center">
                <h1 class="text-lg font-bold text-green-800 uppercase tracking-wide">The Department of Environment and Natural Resources CAR</h1>
                <p class="text-base font-bold text-gray-900">GOVERNMENT PROPERTY</p>
                <p class="text-xs">INVENTORY TAG</p>
            </div>
        </div>

        <!-- Main Fields Grid -->
        <div class="grid grid-cols-2 gap-10">
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="font-semibold">Property No.:</span>
                    <span class="border-b px-11 border-black">{{ asset.id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Asset:</span>
                    <span class="border-b px-12 border-black">{{ asset.name }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Office/Location:</span>
                    <span class="border-b px-12 border-black">{{ asset.location }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Unit/Quantity:</span>
                    <span>1</span>
                </div>
            </div>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="font-semibold">Condition:</span>
                    <span class="border-b px-12 border-black">{{ asset.condition }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Date (Acquired):</span>
                    <span class="border-b px-6 border-black">{{ asset.purchase_date }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="font-semibold">Acquisition Cost:</span>
                    <span class="border-b px-6 border-black">₱{{ asset.value.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                </div>
            </div>
        </div>

        <!-- Warning Footer -->
        <div class="mt-4 pt-2 border-t-2 border-green-700 text-xs font-bold uppercase tracking-wide text-green-700">
        </div>

 

        <!-- Assigned To and Status (Additional Info Below Tag) -->
         <div class="grid grid-cols-2">
                    <div class="mt-4 text-center text-sm">
            <p><strong>Assigned To:</strong> {{ asset.assigned_to }}</p>
        </div>
        <div class="mt-4 text-center text-sm">
            <p><strong>Signature:</strong></p></div>
         </div>

    </div>
    
    

    <div v-else-if="error" class="text-center text-red-600">
        {{ error }}
    </div>

    <div v-else class="text-center text-gray-500">
        No asset data available.
    </div>
    </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Asset {
    id: string;
    name: string;
    category: string;
    location: string;
    purchase_date: string;
    value: number;
    condition: string;
    assigned_to: string;
    status: string;
    qr_code?: string;
}

defineProps<{
    asset?: Asset | null;
    error?: string | null;
}>();

const imageLoadError = ref(false);

const imageError = () => {
    imageLoadError.value = true;
    console.error('Logo image failed to load. Ensure denr_logo.png is in the /public folder at the project root.');
};

const imageLoaded = () => {
    console.log('Logo image loaded successfully.');
};
</script>