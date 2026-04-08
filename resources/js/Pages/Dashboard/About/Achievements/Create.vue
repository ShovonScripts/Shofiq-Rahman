<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const form = useForm({
  title: '', year: '', description_md: '', icon: '', sort_order: 0,
})

const submit = () => {
  form.post(route('dashboard.about.achievements.store'))
}
</script>

<template>
  <Head title="Add Achievement" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Add New Achievement</h1>
      <p class="text-muted text-sm mt-1">Add a milestone or accomplishment</p>
    </div>
    <Link :href="route('dashboard.about.achievements.index')" class="btn-bangla btn-bangla-secondary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      All Achievements
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
        <label class="label-bangla">Title *</label>
        <input v-model="form.title" type="text" class="input-bangla" placeholder="Enter achievement title" />
        <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label class="label-bangla">Year *</label>
          <input v-model="form.year" type="number" inputmode="numeric" class="input-bangla" placeholder="2024" />
          <p v-if="form.errors.year" class="text-sm text-red-600 mt-1">{{ form.errors.year }}</p>
        </div>
        <div>
          <label class="label-bangla">Icon (optional)</label>
          <input v-model="form.icon" type="text" class="input-bangla" placeholder="e.g. bi-trophy" />
          <p v-if="form.errors.icon" class="text-sm text-red-600 mt-1">{{ form.errors.icon }}</p>
        </div>
      </div>

      <div>
        <label class="label-bangla">Description (Markdown) *</label>
        <textarea v-model="form.description_md" rows="6" class="input-bangla" placeholder="Describe your achievement..."></textarea>
        <p v-if="form.errors.description_md" class="text-sm text-red-600 mt-1">{{ form.errors.description_md }}</p>
      </div>

      <div>
        <label class="label-bangla">Sort Order</label>
        <input v-model.number="form.sort_order" type="number" class="input-bangla" placeholder="0" />
        <p v-if="form.errors.sort_order" class="text-sm text-red-600 mt-1">{{ form.errors.sort_order }}</p>
        <p class="text-xs text-muted mt-1">Lower numbers appear first</p>
      </div>

      <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
        <Link :href="route('dashboard.about.achievements.index')" class="btn-bangla btn-bangla-ghost">Cancel</Link>
        <button :disabled="form.processing" class="btn-bangla btn-bangla-primary flex items-center gap-2">
          <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <span v-if="form.processing">Saving...</span>
          <span v-else>Save Achievement</span>
        </button>
      </div>
    </form>
  </div>
</template>
