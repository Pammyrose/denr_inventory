<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
  user: {
      type: Object,
      default: null
  }
});
</script>

<template>
  <Head title="Profile" />
  <AppLayout :breadcrumbs="[{ title: 'Profile' }]">
      <div class="p-4">
          <div class="bg-white p-6 rounded shadow">
            
              <div v-if="user">
                  <!-- Employee Details -->
                  <div v-if="user.employee" class="mt-6">
                      <h2 class="text-lg font-semibold mb-2">Employee Details</h2>
                      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                          <p><strong>First Name:</strong> {{ user.employee.first_name }}</p>
                          <p><strong>Middle Name:</strong> {{ user.employee.middle_name }}</p>
                          <p><strong>Last Name:</strong> {{ user.employee.last_name }}</p>
                          <p><strong>Suffix:</strong> {{ user.employee.suffix }}</p>
                          <p><strong>Sex:</strong> {{ user.employee.sex }}</p>
                          <p><strong>Employment Status:</strong> {{ user.employee.emp_status }}</p>
                          <p><strong>Position:</strong> {{ user.employee.position_name }}</p>
                          <p><strong>Assignment:</strong> {{ user.employee.assignment_name }}</p>
                          <p><strong>Division/Section/Unit:</strong> {{ user.employee.div_sec_unit }}</p>
                      </div>
                  </div>
                  <div v-else class="mt-6">
                      <p>No employee details available.</p>
                  </div>

                  <!-- Assets Section -->
                  <div v-if="user.assets && user.assets.length > 0" class="mt-6">
                      <h2 class="text-lg font-semibold mb-2">Assigned Assets</h2>
                      <table class="w-full border-collapse border border-gray-300">
                          <thead>
                              <tr class="bg-gray-100">
                                  <th class="border border-gray-300 p-2">Name</th>
                                  <th class="border border-gray-300 p-2">Category</th>
                                  <th class="border border-gray-300 p-2">Location</th>
                                  <th class="border border-gray-300 p-2">Assigned Date</th>
                              </tr>
                          </thead>
                          <tbody>
                              <tr v-for="asset in user.assets" :key="asset.id" class="hover:bg-gray-50 text-black">
                                  <td class="border border-gray-300 p-2">{{ asset.name }}</td>
                                  <td class="border border-gray-300 p-2">{{ asset.category }}</td>
                                  <td class="border border-gray-300 p-2">{{ asset.location }}</td>
                                  <td class="border border-gray-300 p-2">{{ asset.purchase_date }}</td>
                              </tr>
                          </tbody>
                      </table>
                  </div>
                  <div v-else class="mt-6">
                      <p>No assets connected to this user.</p>
                  </div>
              </div>
              <div v-else>
                  <p>Loading profile...</p>
              </div>
          </div>
      </div>
  </AppLayout>
</template>