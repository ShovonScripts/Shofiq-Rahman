<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  achievement: { type: Object, default: () => ({ title: '', year: '', description_md: '', icon: '', sort_order: 0 }) },
})

const form = useForm({
  title: props.achievement.title || '',
  year: props.achievement.year || '',
  description_md: props.achievement.description_md || '',
  icon: props.achievement.icon || '',
  sort_order: props.achievement.sort_order || 0,
})

const submit = () => {
  form.put(route('dashboard.about.achievements.update', props.achievement.id), {
    preserveScroll: true,
  })
}
</script>

<template>
  <Head title="Edit Achievement" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Edit Achievement</h1>
      <p class="text-muted text-sm mt-1">Update your accomplishment or milestone</p>
    </div>
    <Link :href="route('dashboard.about.achievements.index')" class="btn-bangla btn-bangla-secondary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Back to Achievements
    </Link>
  </div>

  <!-- Flash Messages -->
  <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3 mb-6">
    <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    <span class="text-green-800 font-medium">{{ $page.props.flash.success }}</span>
  </div>

  <div v-if="form.hasErrors" class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3 mb-6">
    <div class="w-5 h-5 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-red-600" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
    </div>
    <span class="text-red-800 font-medium">Please fix the errors in the form.</span>
  </div>

  <!-- Form -->
  <div class="card-bangla">
    <form @submit.prevent="submit" class="p-6 space-y-8">
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Achievement Details</h3>
        <p class="text-muted text-sm">Update achievement information</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Title *</label>
          <input v-model="form.title" type="text" class="input-bangla" placeholder="Enter achievement title" required />
          <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</p>
        </div>
      </div>

      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Additional Information</h3>
        <p class="text-muted text-sm">Year and optional icon</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="label-bangla">Year *</label>
            <input v-model="form.year" type="number" inputmode="numeric" class="input-bangla" placeholder="2024" required />
            <p v-if="form.errors.year" class="text-sm text-red-600 mt-1">{{ form.errors.year }}</p>
          </div>
          <div>
            <label class="label-bangla">Icon (Optional)</label>
            <input v-model="form.icon" type="text" class="input-bangla" placeholder="Emoji or icon name" />
            <p class="text-xs text-muted mt-1">Emoji or icon name for this achievement</p>
            <p v-if="form.errors.icon" class="text-sm text-red-600 mt-1">{{ form.errors.icon }}</p>
          </div>
        </div>
      </div>

      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Achievement Description</h3>
        <p class="text-muted text-sm">Describe this accomplishment</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Description (Markdown Supported) *</label>
          <textarea v-model="form.description_md" rows="6" class="input-bangla" placeholder="Describe your achievement in detail..." required></textarea>
          <p class="text-xs text-muted mt-1">Supports Markdown formatting</p>
          <p v-if="form.errors.description_md" class="text-sm text-red-600 mt-1">{{ form.errors.description_md }}</p>
        </div>
      </div>

      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Settings</h3>
        <p class="text-muted text-sm">Display order configuration</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Sort Order</label>
          <input v-model.number="form.sort_order" type="number" class="input-bangla" min="0" />
          <p class="text-xs text-muted mt-1">Lower numbers appear first</p>
          <p v-if="form.errors.sort_order" class="text-sm text-red-600 mt-1">{{ form.errors.sort_order }}</p>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 px-6">
        <Link :href="route('dashboard.about.achievements.index')" class="btn-bangla btn-bangla-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          Cancel
        </Link>
        <button type="submit" :disabled="form.processing" class="btn-bangla btn-bangla-primary flex items-center gap-2">
          <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span v-if="form.processing">Updating...</span>
          <span v-else>Update Achievement</span>
        </button>
      </div>
    </form>
  </div>
</template>
