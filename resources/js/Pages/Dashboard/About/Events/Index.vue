<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ events: { type: Array, default: () => [] } })

const destroy = (id) => {
  if (confirm('Are you sure you want to delete this event?')) {
    router.delete(route('dashboard.about.events.destroy', id), { preserveScroll: true })
  }
}

const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' })
}
</script>

<template>
  <Head title="Life Events Management" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Life Events Management</h1>
      <p class="text-muted text-sm mt-1">Manage your timeline milestones and significant events</p>
    </div>
    <Link :href="route('dashboard.about.events.create')" class="btn-bangla btn-bangla-primary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Add New Event
    </Link>
  </div>

  <!-- Flash -->
  <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3 mb-6">
    <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    <span class="text-green-800 font-medium">{{ $page.props.flash.success }}</span>
  </div>

  <!-- Stats -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Total Events</p>
          <p class="text-2xl font-bold text-gray-900">{{ props.events.length }}</p>
        </div>
      </div>
    </div>
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Published</p>
          <p class="text-2xl font-bold text-gray-900">{{ props.events.filter(e => e.is_published).length }}</p>
        </div>
      </div>
    </div>
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Latest Event</p>
          <p class="text-2xl font-bold text-gray-900">
            {{ props.events.length ? formatDate(Math.max(...props.events.map(e => new Date(e.happened_at)).filter(d => !isNaN(d)))) : '—' }}
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- Table -->
  <div class="card-bangla overflow-hidden">
    <div class="overflow-x-auto">
      <table class="table-bangla">
        <thead>
          <tr>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Event Details</th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Date & Location</th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!props.events.length">
            <td colspan="4" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center justify-center text-gray-500">
                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <p class="text-lg font-semibold text-gray-900 mb-1">No events found</p>
                <p class="text-sm text-muted">Get started by adding your first life event</p>
              </div>
            </td>
          </tr>
          <tr v-for="event in props.events" :key="event.id" class="hover:bg-gray-50 transition-colors duration-150">
            <td class="px-6 py-4">
              <Link :href="route('dashboard.about.events.edit', event.id)" class="text-gray-900 hover:text-blue-600 font-semibold block">
                {{ event.headline }}
              </Link>
              <p v-if="event.description_md" class="text-sm text-muted mt-1 line-clamp-2">{{ event.description_md.substring(0, 120) }}{{ event.description_md.length > 120 ? '...' : '' }}</p>
            </td>
            <td class="px-6 py-4">
              <div class="space-y-1">
                <div class="flex items-center text-gray-700 text-sm">
                  <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                  {{ formatDate(event.happened_at) }}
                </div>
                <div v-if="event.location" class="flex items-center text-sm text-muted">
                  <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                  {{ event.location }}
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <span class="badge-bangla" :class="event.is_published ? 'badge-bangla-success' : 'badge-bangla-warning'">
                {{ event.is_published ? 'Published' : 'Draft' }}
              </span>
            </td>
            <td class="px-6 py-4">
              <div class="flex justify-end gap-2">
                <Link :href="route('dashboard.about.events.edit', event.id)" class="btn-bangla btn-bangla-ghost text-sm flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  Edit
                </Link>
                <button @click="destroy(event.id)" class="btn-bangla btn-bangla-ghost text-sm text-red-600 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                  Delete
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
