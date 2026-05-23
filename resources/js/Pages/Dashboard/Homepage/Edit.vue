<!-- resources/js/Pages/Dashboard/Homepage/Edit.vue -->
<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm, router } from '@inertiajs/vue3'
defineOptions({ layout: AdminLayout })

const props = defineProps({
  banners: { type: Array, default: () => [] },
  author:  { type: Object, default: () => ({ name:'', bio:'', photo_url:null }) },
})

/* =========================
   BANNERS
   ========================= */
const bannerForm = useForm({ title:'', link:'', image:null })

const addBanner = () => {
  bannerForm.post('/dashboard/homepage/banners', {
    forceFormData: true,
    preserveScroll: true,
    onSuccess: () => {
      bannerForm.reset('title','link','image')
    }
  })
}

const updateBanner = (id, payload) => {
  const form = useForm(payload)
  form.put(`/dashboard/homepage/banners/${id}`, { preserveScroll: true })
}

const toggleBanner = (id, isActive) => {
  router.patch(`/dashboard/homepage/banners/${id}/toggle`, { is_active: isActive }, { preserveScroll: true })
}

const deleteBanner = (id) => {
  if (!confirm('Delete banner?')) return
  router.delete(`/dashboard/homepage/banners/${id}`, { preserveScroll: true })
}

const move = (idx, dir) => {
  const arr = [...props.banners]
  const j = dir === 'up' ? idx - 1 : idx + 1
  if (j < 0 || j >= arr.length) return
  const tmp = arr[idx]; arr[idx] = arr[j]; arr[j] = tmp
  const payload = arr.map((b, i) => ({ id: b.id, sort_order: i }))
  useForm({ items: payload }).post('/dashboard/homepage/banners/sort', { preserveScroll: true })
}

/* =========================
   AUTHOR
   ========================= */
const authorForm = useForm({
  name:  props.author?.name || '',
  bio:   props.author?.bio || '',
  photo: null
})

const saveAuthor = () => {
  authorForm
    .transform(d => ({ ...d, _method: 'PUT' }))
    .post('/dashboard/homepage/author', {
      forceFormData: true,
      preserveScroll: true,
    })
}

const removeAuthorPhoto = () => {
  router.delete('/dashboard/homepage/author/photo', { preserveScroll: true })
}
</script>

<template>
  <div>
    <Head title="Homepage Content Management" />
    
    <!-- ✅ Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl font-bold text-gray-900">Homepage Content</h1>
        <p class="text-gray-600 mt-1">Manage homepage banners and author information</p>
      </div>
    </div>

    <!-- ✅ Flash Message -->
    <div
      v-if="$page.props.flash?.success"
      class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4"
    >
      <div class="flex items-center">
        <svg class="w-5 h-5 text-green-600 mr-3" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="text-green-800 font-medium">{{ $page.props.flash.success }}</span>
      </div>
    </div>

    <!-- ✅ Banners Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mb-8">
      <div class="p-4 sm:p-6">
        <div class="flex items-center justify-between mb-6">
          <div>
            <h2 class="text-lg font-semibold text-gray-900">Hero Banners</h2>
            <p class="text-gray-600 mt-1">Manage your homepage banner carousel (Max 5)</p>
          </div>
          <div class="bg-blue-50 text-blue-700 px-3 py-1 rounded-full text-sm font-medium">
            {{ banners?.length || 0 }}/5 Banners
          </div>
        </div>

        <!-- ✅ Add Banner Form -->
        <form class="grid grid-cols-1 md:grid-cols-12 gap-4 mb-6 p-4 bg-gray-50 rounded-lg" @submit.prevent="addBanner" enctype="multipart/form-data">
          <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
            <input 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              v-model="bannerForm.title"
              placeholder="Banner title"
            >
            <div v-if="bannerForm.errors.title" class="text-red-600 text-sm mt-1">{{ bannerForm.errors.title }}</div>
          </div>
          <div class="md:col-span-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Link (Optional)</label>
            <input 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              v-model="bannerForm.link" 
              placeholder="/article/slug or https://..."
            >
            <div v-if="bannerForm.errors.link" class="text-red-600 text-sm mt-1">{{ bannerForm.errors.link }}</div>
          </div>
          <div class="md:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-2">Image (16:9 ratio)</label>
            <input 
              type="file" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors duration-200"
              @change="e=>bannerForm.image=e.target.files?.[0] ?? null"
              accept="image/*"
            >
            <div v-if="bannerForm.errors.image" class="text-red-600 text-sm mt-1">{{ bannerForm.errors.image }}</div>
          </div>
          <div class="md:col-span-2 flex items-end">
            <button 
              class="w-full inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="bannerForm.processing"
            >
              <svg v-if="bannerForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
              </svg>
              {{ bannerForm.processing ? 'Adding...' : 'Add Banner' }}
            </button>
          </div>
        </form>

        <!-- ✅ Banners Table -->
        <div v-if="banners?.length" class="overflow-hidden rounded-lg border border-gray-200">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Image</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Link</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="(b, i) in banners" :key="b.id" class="hover:bg-gray-50 transition-colors duration-150">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <img 
                      :src="b.image_url" 
                      class="rounded-lg shadow-sm border border-gray-200"
                      style="width:120px; aspect-ratio:16/9; object-fit:cover;"
                      :alt="b.title"
                    >
                  </td>
                  <td class="px-6 py-4">
                    <input 
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                      :value="b.title" 
                      @change="e=>updateBanner(b.id,{title:e.target.value})"
                    >
                  </td>
                  <td class="px-6 py-4">
                    <input 
                      class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                      :value="b.link" 
                      @change="e=>updateBanner(b.id,{link:e.target.value})"
                    >
                  </td>
                  <td class="px-6 py-4">
                    <label class="relative inline-flex items-center cursor-pointer">
                      <input 
                        type="checkbox" 
                        class="sr-only peer" 
                        :checked="b.is_active" 
                        @change="e=>toggleBanner(b.id, e.target.checked)"
                      >
                      <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                      <span class="ml-3 text-sm text-gray-600">
                        {{ b.is_active ? 'Active' : 'Inactive' }}
                      </span>
                    </label>
                  </td>
                  <td class="px-6 py-4">
                    <div class="flex space-x-1">
                      <button 
                        class="inline-flex items-center p-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 disabled:opacity-30 disabled:cursor-not-allowed"
                        @click="move(i,'up')"
                        :disabled="i === 0"
                        title="Move up"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                      </button>
                      <button 
                        class="inline-flex items-center p-2 text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 disabled:opacity-30 disabled:cursor-not-allowed"
                        @click="move(i,'down')"
                        :disabled="i === banners.length - 1"
                        title="Move down"
                      >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                      </button>
                    </div>
                  </td>
                  <td class="px-6 py-4">
                    <button 
                      class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                      @click="deleteBanner(b.id)"
                    >
                      <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                      </svg>
                      Delete
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- ✅ Empty State -->
        <div v-else class="text-center py-12 bg-gray-50 rounded-lg border-2 border-dashed border-gray-300">
          <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <h3 class="mt-4 text-sm font-medium text-gray-900">No banners</h3>
          <p class="mt-1 text-sm text-gray-500">Get started by adding your first banner.</p>
        </div>

        <!-- ✅ Help Text -->
        <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
          <div class="flex">
            <svg class="w-5 h-5 text-blue-600 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div>
              <p class="text-sm text-blue-800">
                All images are stored in local storage (public disk). Maintaining 16:9 ratio will make the carousel look good. Maximum 5 banners can be added.
              </p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ Author Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="p-4 sm:p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Author Information</h2>
        
        <form class="space-y-6" @submit.prevent="saveAuthor" enctype="multipart/form-data">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:p-6">
            <!-- Name -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
              <input 
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                v-model="authorForm.name"
                placeholder="Author's full name"
              >
              <div v-if="authorForm.errors.name" class="text-red-600 text-sm mt-1">{{ authorForm.errors.name }}</div>
            </div>
            
            <!-- Photo -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Profile Photo</label>
              <input 
                type="file" 
                class="w-full border border-gray-300 rounded-lg px-3 py-2.5 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors duration-200"
                @change="e=>authorForm.photo=e.target.files?.[0] ?? null"
                accept="image/*"
              >
              <div v-if="authorForm.errors.photo" class="text-red-600 text-sm mt-1">{{ authorForm.errors.photo }}</div>

              <!-- Current Photo Preview -->
              <div v-if="props.author?.photo_url" class="mt-3">
                <p class="text-sm text-gray-600 mb-2">Current photo:</p>
                <img 
                  :src="props.author.photo_url" 
                  class="rounded-lg shadow-sm border border-gray-200"
                  style="height:120px; aspect-ratio:1/1; object-fit:cover;"
                  alt="Current profile photo"
                >
                <div class="mt-2">
                  <button type="button" class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200" @click="removeAuthorPhoto">
                    Remove Photo
                  </button>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Bio -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Bio (Markdown Supported)</label>
            <textarea 
              rows="6" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              v-model="authorForm.bio" 
              placeholder="Write about the author..."
            ></textarea>
            <div v-if="authorForm.errors.bio" class="text-red-600 text-sm mt-1">{{ authorForm.errors.bio }}</div>
            <p class="text-sm text-gray-500 mt-2">
              Markdown formatting is supported. You can use links, bold, italic, etc.
            </p>
          </div>
          
          <!-- ✅ Submit Button -->
          <div class="flex justify-end pt-6 border-t border-gray-200">
            <button 
              class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
              :disabled="authorForm.processing"
            >
              <svg v-if="authorForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              {{ authorForm.processing ? 'Saving...' : 'Save Author Information' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar for table */
.overflow-x-auto {
  scrollbar-width: thin;
  scrollbar-color: #cbd5e1 #f1f5f9;
}

.overflow-x-auto::-webkit-scrollbar {
  height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}
</style>