<script setup>
import { Link, useForm, Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({ achievements: { type: Array, default: () => [] } })

const form = useForm({
  title: '', year: '', description_md: '', icon: '', sort_order: 0,
})

const submit = () => {
  form.post(route('dashboard.about.achievements.store'))
}
</script>

<template>
  <Head title="Achievements Management" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Achievements Management</h1>
      <p class="text-muted text-sm mt-1">Showcase your accomplishments and milestones</p>
    </div>
    <Link :href="route('dashboard.about.achievements.create')" class="btn-bangla btn-bangla-primary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
      Add New Achievement
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
          <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Total Achievements</p>
          <p class="text-2xl font-bold text-gray-900">{{ props.achievements.length }}</p>
        </div>
      </div>
    </div>
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Latest Achievement</p>
          <p class="text-2xl font-bold text-gray-900">{{ props.achievements.length ? Math.max(...props.achievements.map(a => a.year).filter(Boolean)) || '—' : '—' }}</p>
        </div>
      </div>
    </div>
    <div class="card-bangla">
      <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
          <p class="text-sm text-muted">Earliest Achievement</p>
          <p class="text-2xl font-bold text-gray-900">{{ props.achievements.length ? Math.min(...props.achievements.map(a => a.year).filter(Boolean)) || '—' : '—' }}</p>
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
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Achievement</th>
            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Year</th>
            <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="!props.achievements.length">
            <td colspan="3" class="px-6 py-12 text-center">
              <div class="flex flex-col items-center justify-center text-gray-500">
                <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <p class="text-lg font-semibold text-gray-900 mb-1">No achievements found</p>
                <p class="text-sm text-muted">Get started by adding your first achievement</p>
              </div>
            </td>
          </tr>
          <tr v-for="a in props.achievements" :key="a.id" class="hover:bg-gray-50 transition-colors duration-150">
            <td class="px-6 py-4">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                  <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                  <span class="text-gray-900 font-semibold block">{{ a.title }}</span>
                  <p v-if="a.description" class="text-sm text-muted mt-1">{{ a.description.substring(0, 100) }}{{ a.description.length > 100 ? '...' : '' }}</p>
                </div>
              </div>
            </td>
            <td class="px-6 py-4">
              <span v-if="a.year" class="badge-bangla badge-bangla-secondary">{{ a.year }}</span>
              <span v-else class="text-gray-400">—</span>
            </td>
            <td class="px-6 py-4">
              <div class="flex justify-end gap-2">
                <Link :href="route('dashboard.about.achievements.edit', a.id)" class="btn-bangla btn-bangla-ghost text-sm flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                  Edit
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>
