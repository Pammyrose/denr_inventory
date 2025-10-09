<!-- 
<template>
    <div class="flex justify-center items-center min-h-screen">
        <div class="flex justify-end mb-4">
            <button
                @click="printAssetTag"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                Print Asset Tag
            </button>
        </div>
        <div v-if="asset" class="max-w-5xl mx-auto bg-white border-2 border-red-700 shadow-lg" id="asset-tag">
     
            <div class="text-center py-2 bg-red-700 text-white font-bold uppercase tracking-wide text-sm">
                "COA" CIRCULAR NO. 80-124 JAN. 18, 1980
            </div>
            <div class="p-2">
           
                <div class="text-center mb-8 flex items-center justify-start">
                    <img src="/rnp.png" alt="Logo" class="w-20 h-20 ml-5" @error="imageError" @load="imageLoaded" />
                    <div class="flex-1 text-center">
                        <h1 class="text-lg font-bold text-green-800 tracking-wide">Republic of the Philippines</h1>
                        <p class="text-base font-bold text-gray-900">GOVERNMENT PROPERTY</p>
                        <span class="border-b px-11 border-black">{{ asset.location }}</span>
                        <p class="text-xs">Office / Location</p>
                    </div>
                    <div class="-mt-14">
                        No.<span class="border-b px-2 border-black">{{ asset.id }}</span>
                        <p class="text-xs">Inventory Tag</p>
                    </div>
                </div>

          
                <div class="grid grid-cols-1">
                    <div class="flex justify-start">
                        <span class="font-semibold">Asset:</span>
                        <span class="border-b px-55 border-black">{{ asset.name }}</span>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-10 mt-2">
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-semibold">Property No.</span>
                            <span class="border-b px-11 border-black">{{ asset.property_no }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Serviceable</span>
                            <span class="border-b px-12 border-black">{{ asset.serviceable }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Unit / Quantity</span>
                            <span class="border-b px-12 border-black">{{ asset.unit_qty }}</span>
                        </div>
                    </div>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="font-semibold">Serial No</span>
                            <span class="border-b px-12 border-black">{{ asset.serial_no }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Unserviceable</span>
                            <span class="border-b px-6 border-black">{{ asset.unserviceable }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="font-semibold">Acquisition Cost:</span>
                            <span class="border-b px-6 border-black">₱{{ asset.value.toLocaleString('en-PH', { minimumFractionDigits: 2 }) }}</span>
                        </div>
                    </div>
                </div>

      
                <div class="flex justify-between pl-10 pr-10 pt-4">
                    <div>
                        <span class="border-b px-12 border-black">{{ asset.purchase_date }}</span>
                        <p class="text-xs text-center">Date: (Acquired)</p>
                    </div>
                    <div>
                        <span class="border-b px-12 border-black">{{ asset.assigned_date }}</span>
                        <p class="text-xs text-center">Date: (Assigned)</p>
                    </div>
                </div>
                <div class="flex justify-between pl-10 pr-10 pt-4 gap-10">
                    <div>
                        <span class="border-b border-black">{{ asset.coa_representative }}</span>
                        <p class="text-xs text-center">COA Representative</p>
                    </div>
                    <div>
                        <span class="border-b border-black">{{ asset.assigned_to }}</span>
                        <p class="text-xs text-center">Property Custodian</p>
                    </div>
                </div>
            </div>
    
            <div class="pl-2 text-black text-xs tracking-wide text-center">
                See device for actual COA sticker
            </div>
            <div class="mt-4 pt-2 bg-red-700 text-white text-sm font-bold uppercase tracking-wide text-center">
                TAMPERING THIS STICKER IS PUNISHABLE BY LAW
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
    property_no: string;
    serial_no: string;
    serviceable: string;
    unserviceable: string;
    unit_qty: string;
    coa_representative: string;
    coa_date: string;
    assigned_date: string;
}

defineProps<{
    asset?: Asset | null;
    error?: string | null;
}>();

const imageLoadError = ref(false);

const imageError = () => {
    imageLoadError.value = true;
    console.error('Logo image failed to load. Ensure rnp.png is in the /public folder at the project root.');
};

const imageLoaded = () => {
    console.log('Logo image loaded successfully.');
};

const printAssetTag = () => {
    const printContent = document.getElementById('asset-tag');
    if (!printContent) {
        console.error('Asset tag element not found.');
        return;
    }

    const printWindow = window.open('', '_blank');
    if (!printWindow) {
        console.error('Failed to open print window.');
        return;
    }

    printWindow.document.write(`
        <html>
            <head>
                <title>Print Asset Tag</title>
                <style>
                    @media print {
                        body { margin: 0; padding: 0; }
                        .asset-tag-container { width: 100%; max-width: 5in; margin: 0 auto; }
                        .border-2 { border: 2px solid #b91c1c; }
                        .bg-red-700 { background-color: #b91c1c; }
                        .text-white { color: white; }
                        .text-green-800 { color: #166534; }
                        .text-gray-900 { color: #1f2937; }
                        .text-xs { font-size: 0.75rem; }
                        .text-sm { font-size: 0.875rem; }
                        .text-base { font-size: 1rem; }
                        .text-lg { font-size: 1.125rem; }
                        .font-bold { font-weight: 700; }
                        .uppercase { text-transform: uppercase; }
                        .tracking-wide { letter-spacing: 0.025em; }
                        .p-2 { padding: 0.5rem; }
                        .pt-2 { padding-top: 0.5rem; }
                        .pt-4 { padding-top: 1rem; }
                        .pl-2 { padding-left: 0.5rem; }
                        .pl-10 { padding-left: 2.5rem; }
                        .pr-10 { padding-right: 2.5rem; }
                        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
                        .mb-8 { margin-bottom: 2rem; }
                        .mt-2 { margin-top: 0.5rem; }
                        .mt-4 { margin-top: 1rem; }
                        .-mt-14 { margin-top: -3.5rem; }
                        .grid { display: grid; }
                        .grid-cols-1 { grid-template-columns: 1fr; }
                        .grid-cols-2 { grid-template-columns: repeat(2, 1fr); }
                        .gap-10 { gap: 2.5rem; }
                        .space-y-2 > * + * { margin-top: 0.5rem; }
                        .flex { display: flex; }
                        .justify-start { justify-content: flex-start; }
                        .justify-between { justify-content: space-between; }
                        .items-center { align-items: center; }
                        .text-center { text-align: center; }
                        .border-b { border-bottom: 1px solid black; }
                        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
                        .px-6 { padding-left: 1.5rem; padding-right: 1.5rem; }
                        .px-11 { padding-left: 2.75rem; padding-right: 2.75rem; }
                        .px-12 { padding-left: 3rem; padding-right: 3rem; }
                        .px-55 { padding-left: 13.75rem; padding-right: 13.75rem; }
                        .w-20 { width: 5rem; }
                        .h-20 { height: 5rem; }
                        .ml-5 { margin-left: 1.25rem; }
                        .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05); }
                    }
                </style>
            </head>
            <body>
                <div class="asset-tag-container">
                    ${printContent.innerHTML}
                </div>
                <script>
                    window.onload = () => {
                        window.print();
                        window.onafterprint = () => window.close();
                    };
                </script>
            </body>
        </html>
    `);
    printWindow.document.close();
};

defineExpose({
    printAssetTag,
});
</script>

<style scoped>

@media print {
    button {
        display: none;
    }
}
</style> -->
