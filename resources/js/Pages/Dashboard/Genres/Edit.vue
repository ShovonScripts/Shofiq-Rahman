<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { useForm, Link, Head } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({ genre: Object })

const form = useForm({
  name: props.genre.name || '',
  slug: props.genre.slug || '',
})

const slugify = (s) =>
  (s || '').toString().normalize('NFKD').replace(/[\u0300-\u036f]/g, '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '')

const onNameBlur = () => {
  if (!form.slug?.trim() && form.name) {
    form.slug = slugify(form.name)
  }
}

const submit = () => {
  form.put(`/dashboard/genres/${props.genre.id}`, { preserveScroll: true })
}
</script>

<template>
  <Head title="Edit Genre" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Edit Genre</h1>
      <p class="text-muted text-sm mt-1">Update genre details</p>
    </div>
    <Link href="/dashboard/genres" class="btn-bangla btn-bangla-secondary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      All Genres
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
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Genre Information</h3>
        <p class="text-muted text-sm">Update genre name and slug</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Name</label>
          <input v-model="form.name" @blur="onNameBlur" class="input-bangla" />
          <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
        </div>
        <div>
          <label class="label-bangla">Slug (optional)</label>
          <input v-model="form.slug" class="input-bangla" placeholder="auto from name if left blank" />
          <p v-if="form.errors.slug" class="text-sm text-red-600 mt-1">{{ form.errors.slug }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 px-6">
        <Link href="/dashboard/genres" class="btn-bangla btn-bangla-ghost">Cancel</Link>
        <button type="submit" :disabled="form.processing" class="btn-bangla btn-bangla-primary flex items-center gap-2">
          <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <span v-if="form.processing">Saving...</span>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</template>
