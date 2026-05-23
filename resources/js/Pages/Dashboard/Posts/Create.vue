<script setup>
import AdminLayout from '../../../Layouts/AdminLayout.vue'
import { useForm, Link, Head } from '@inertiajs/vue3'
import { onBeforeUnmount, ref } from 'vue'

// ✅ Define layout
defineOptions({ layout: AdminLayout })

// ✅ Props from controller
const props = defineProps({
  allGenres: { type: Array, default: () => [] },
  allCategories: { type: Array, default: () => [] },
})

// ✅ Reactive form
const form = useForm({
  title: '',
  body: '',
  cover: null,
  meta_title: '',
  meta_description: '',
  genre_ids: [],
  category_ids: [],
})

// ✅ Image preview
const previewUrl = ref(null)

const onCoverChange = (e) => {
  const file = e.target.files?.[0] ?? null
  form.cover = file

  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
  previewUrl.value = file ? URL.createObjectURL(file) : null
}

// ✅ Submit handler
const submit = () => {
  if (form.processing) return

  form.post('/dashboard/posts', {
    forceFormData: true,
    onSuccess: () => {
      form.reset()
      if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value)
        previewUrl.value = null
      }
    },
  })
}

// ✅ Cleanup
onBeforeUnmount(() => {
  if (previewUrl.value) URL.revokeObjectURL(previewUrl.value)
})
</script>

<template>
  <div class="py-6">
    <Head title="Create New Post" />

    <!-- ✅ Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl font-bold text-gray-900">Create New Post</h1>
        <p class="text-gray-600 mt-1">Create a new blog post or article</p>
      </div>
      <Link 
        href="/dashboard/posts" 
        class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
      >
        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back to Posts
      </Link>
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

    <!-- ✅ Validation Errors -->
    <div
      v-if="Object.keys(form.errors).length"
      class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4"
    >
      <div class="flex items-center mb-2">
        <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
        </svg>
        <span class="font-medium text-red-800">Form contains errors:</span>
      </div>
      <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
        <li v-for="(msg, key) in form.errors" :key="key">{{ msg }}</li>
      </ul>
    </div>

    <!-- ✅ Post Form -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="p-4 sm:p-6">
        <form class="space-y-6" @submit.prevent="submit" novalidate>
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2" for="title">
              Title *
            </label>
            <input
              id="title"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              v-model="form.title"
              required
              maxlength="255"
              :disabled="form.processing"
              :class="{ 'border-red-500 ring-red-500': form.errors.title }"
              placeholder="Enter post title"
            />
            <div class="text-red-600 text-sm mt-1" v-if="form.errors.title">
              {{ form.errors.title }}
            </div>
            <div class="text-sm text-gray-500 mt-1">
              {{ form.title.length }}/255 characters
            </div>
          </div>

          <!-- Body -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2" for="body">
              Body *
            </label>
            <textarea
              id="body"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              rows="12"
              v-model="form.body"
              required
              :disabled="form.processing"
              :class="{ 'border-red-500 ring-red-500': form.errors.body }"
              placeholder="Write your post content..."
            ></textarea>
            <div class="text-red-600 text-sm mt-1" v-if="form.errors.body">
              {{ form.errors.body }}
            </div>
            <div class="text-sm text-gray-500 mt-1">
              {{ form.body.length }} characters
            </div>
          </div>

          <!-- Categories -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2" for="categories">
              Categories (Select multiple)
            </label>
            <select
              id="categories"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 min-h-32"
              v-model="form.category_ids"
              multiple
              :disabled="form.processing"
              :class="{ 'border-red-500 ring-red-500': form.errors.category_ids }"
            >
              <option v-for="c in props.allCategories" :key="c.id" :value="c.id">
                {{ c.name }}
              </option>
            </select>
            <div class="text-red-600 text-sm mt-1" v-if="form.errors.category_ids">
              {{ form.errors.category_ids }}
            </div>
            <div class="text-sm text-gray-500 mt-1">
              Hold Ctrl (or Cmd) to select multiple categories
            </div>
          </div>

          <!-- Genres -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2" for="genres">
              Genres
            </label>
            <select
              id="genres"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 min-h-32"
              v-model="form.genre_ids"
              multiple
              :disabled="form.processing"
              :class="{ 'border-red-500 ring-red-500': form.errors.genre_ids }"
            >
              <option v-for="g in props.allGenres" :key="g.id" :value="g.id">
                {{ g.name }}
              </option>
            </select>
            <div class="text-red-600 text-sm mt-1" v-if="form.errors.genre_ids">
              {{ form.errors.genre_ids }}
            </div>
            <div class="text-sm text-gray-500 mt-1">
              Hold Ctrl (or Cmd) to select multiple genres
            </div>
          </div>

          <!-- Cover Image -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2" for="cover">
              Cover Image
            </label>
            <input
              id="cover"
              type="file"
              accept="image/*"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors duration-200"
              @change="onCoverChange"
              :disabled="form.processing"
              :class="{ 'border-red-500 ring-red-500': form.errors.cover }"
            />
            <div class="text-red-600 text-sm mt-1" v-if="form.errors.cover">
              {{ form.errors.cover }}
            </div>

            <!-- Image Preview -->
            <div v-if="previewUrl" class="mt-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
              <p class="text-sm font-medium text-gray-700 mb-2">Image Preview:</p>
              <img
                :src="previewUrl"
                alt="Cover image preview"
                class="rounded-lg border border-gray-300 shadow-sm mx-auto"
                style="max-height: 200px; object-fit: contain;"
              />
            </div>
          </div>

          <!-- ✅ SEO Section -->
          <div class="border-t border-gray-200 pt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">SEO Settings</h3>
            
            <!-- Meta Title -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2" for="meta_title">
                Meta Title (Optional)
              </label>
              <input
                id="meta_title"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                v-model="form.meta_title"
                maxlength="70"
                :disabled="form.processing"
                :class="{ 'border-red-500 ring-red-500': form.errors.meta_title }"
                placeholder="Title for Google search"
              />
              <div class="text-red-600 text-sm mt-1" v-if="form.errors.meta_title">
                {{ form.errors.meta_title }}
              </div>
              <div class="text-sm text-gray-500 mt-1">
                {{ form.meta_title.length }}/70 characters - 
                <span :class="form.meta_title.length > 70 ? 'text-red-600' : 'text-green-600'">
                  {{ form.meta_title.length > 70 ? 'Too long' : 'Good length' }}
                </span>
              </div>
            </div>

            <!-- Meta Description -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2" for="meta_description">
                Meta Description (Optional)
              </label>
              <textarea
                id="meta_description"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                rows="3"
                v-model="form.meta_description"
                maxlength="160"
                :disabled="form.processing"
                :class="{ 'border-red-500 ring-red-500': form.errors.meta_description }"
                placeholder="Brief description for Google search"
              ></textarea>
              <div class="text-red-600 text-sm mt-1" v-if="form.errors.meta_description">
                {{ form.errors.meta_description }}
              </div>
              <div class="text-sm text-gray-500 mt-1">
                {{ form.meta_description.length }}/160 characters - 
                <span :class="form.meta_description.length > 160 ? 'text-red-600' : 'text-green-600'">
                  {{ form.meta_description.length > 160 ? 'Too long' : 'Good length' }}
                </span>
              </div>
            </div>
          </div>

          <!-- ✅ Form Actions -->
          <div class="flex flex-col sm:flex-row gap-3 pt-6 border-t border-gray-200">
            <button 
              class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed flex-1 sm:flex-none"
              :disabled="form.processing"
            >
              <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
              </svg>
              {{ form.processing ? 'Saving...' : 'Save as Draft' }}
            </button>
            
            <Link
              href="/dashboard/posts"
              class="inline-flex items-center justify-center px-6 py-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 flex-1 sm:flex-none"
              :class="{ 'opacity-50 pointer-events-none': form.processing }"
            >
              <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              Cancel
            </Link>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Multi-select height */
select[multiple] { 
  min-height: 8rem;
}
</style>