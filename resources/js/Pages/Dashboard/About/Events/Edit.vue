<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  event: { type: Object, default: () => ({ happened_at: '', headline: '', description_md: '', location: '', sort_order: 0 }) },
})

const form = useForm({
  happened_at: props.event.happened_at || '',
  headline: props.event.headline || '',
  description_md: props.event.description_md || '',
  location: props.event.location || '',
  sort_order: props.event.sort_order || 0,
  is_published: Boolean(props.event.is_published),
  image: null,
})

const submit = () => {
  form.put(route('dashboard.about.events.update', props.event.id), {
    forceFormData: true, preserveScroll: true,
  })
}

const handleFileChange = (e) => {
  form.image = e.target.files?.[0] ?? null
}
</script>

<template>
  <Head title="Edit Life Event" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Edit Life Event</h1>
      <p class="text-muted text-sm mt-1">Update this milestone or significant event</p>
    </div>
    <Link :href="route('dashboard.about.events.index')" class="btn-bangla btn-bangla-secondary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Back to Events
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
    <form @submit.prevent="submit" class="p-6 space-y-8" enctype="multipart/form-data">
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Event Details</h3>
        <p class="text-muted text-sm">Basic event information</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="label-bangla">Event Date *</label>
            <input v-model="form.happened_at" type="date" class="input-bangla" required />
            <p v-if="form.errors.happened_at" class="text-sm text-red-600 mt-1">{{ form.errors.happened_at }}</p>
          </div>
          <div>
            <label class="label-bangla">Location</label>
            <input v-model="form.location" type="text" class="input-bangla" placeholder="City, Country or Venue" />
            <p v-if="form.errors.location" class="text-sm text-red-600 mt-1">{{ form.errors.location }}</p>
          </div>
        </div>
      </div>

      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Event Headline</h3>
        <p class="text-muted text-sm">Main title for the event</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Headline *</label>
          <input v-model="form.headline" type="text" class="input-bangla" placeholder="Enter a compelling headline" required />
          <p v-if="form.errors.headline" class="text-sm text-red-600 mt-1">{{ form.errors.headline }}</p>
        </div>
      </div>

      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Event Description</h3>
        <p class="text-muted text-sm">Describe the event</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Description (Markdown Supported) *</label>
          <textarea v-model="form.description_md" rows="6" class="input-bangla" placeholder="Describe the event in detail..." required></textarea>
          <p class="text-xs text-muted mt-1">Supports Markdown formatting</p>
          <p v-if="form.errors.description_md" class="text-sm text-red-600 mt-1">{{ form.errors.description_md }}</p>
        </div>
      </div>

      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Settings & Media</h3>
        <p class="text-muted text-sm">Publish status, image and ordering</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="grid md:grid-cols-3 gap-6">
          <div>
            <label class="label-bangla">Sort Order</label>
            <input v-model.number="form.sort_order" type="number" class="input-bangla" min="0" />
            <p class="text-xs text-muted mt-1">Lower numbers appear first</p>
            <p v-if="form.errors.sort_order" class="text-sm text-red-600 mt-1">{{ form.errors.sort_order }}</p>
          </div>
          <div class="flex items-center">
            <input id="is_published" v-model="form.is_published" type="checkbox" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 focus:ring-2" />
            <label for="is_published" class="ml-2 text-sm text-gray-700">Publish this event</label>
          </div>
          <div>
            <label class="label-bangla">Event Image</label>
            <input type="file" accept="image/*" @change="handleFileChange" class="input-bangla" />
            <p class="text-xs text-muted mt-1">Optional event image</p>
            <p v-if="form.errors.image" class="text-sm text-red-600 mt-1">{{ form.errors.image }}</p>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 px-6">
        <Link :href="route('dashboard.about.events.index')" class="btn-bangla btn-bangla-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          Cancel
        </Link>
        <button type="submit" :disabled="form.processing" class="btn-bangla btn-bangla-primary flex items-center gap-2">
          <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span v-if="form.processing">Updating...</span>
          <span v-else>Update Event</span>
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
input:focus, textarea:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
</style>
