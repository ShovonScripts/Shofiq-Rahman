<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  genres: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
})

const form = useForm({
  name: '', slug: '', description: '',
})

const slugify = (s) =>
  (s || '').toString().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')

const onNameBlur = () => {
  if (!form.slug?.trim() && form.name) {
    form.slug = slugify(form.name)
  }
}

const submit = () => {
  form.post('/dashboard/categories', { preserveScroll: true })
}
</script>

<template>
  <Head title="Create Category" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Create Category</h1>
      <p class="text-muted text-sm mt-1">Add a new content category</p>
    </div>
    <Link href="/dashboard/categories" class="btn-bangla btn-bangla-secondary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Back to Categories
    </Link>
  </div>

  <!-- Flash -->
  <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3 mb-6">
    <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    <span class="text-green-800 font-medium">{{ $page.props.flash.success }}</span>
  </div>

  <!-- Form -->
  <div class="card-bangla">
    <form @submit.prevent="submit" class="p-6 space-y-6">
      <div>
        <label class="label-bangla">Name</label>
        <input v-model="form.name" @blur="onNameBlur" class="input-bangla" placeholder="Category name" />
        <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <label class="label-bangla">Slug (optional)</label>
          <input v-model="form.slug" class="input-bangla" placeholder="auto-generated if left blank" />
          <p class="text-xs text-muted mt-1">URL-friendly version of the name</p>
          <p v-if="form.errors.slug" class="text-sm text-red-600 mt-1">{{ form.errors.slug }}</p>
        </div>
      </div>

      <div>
        <label class="label-bangla">Description</label>
        <textarea v-model="form.description" rows="3" class="input-bangla" placeholder="Optional description"></textarea>
        <p v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</p>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
        <Link href="/dashboard/categories" class="btn-bangla btn-bangla-ghost">Cancel</Link>
        <button type="submit" :disabled="form.processing" class="btn-bangla btn-bangla-primary flex items-center gap-2">
          <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <span v-if="form.processing">Saving...</span>
          <span v-else>Create Category</span>
        </button>
      </div>
    </form>
  </div>
</template>
