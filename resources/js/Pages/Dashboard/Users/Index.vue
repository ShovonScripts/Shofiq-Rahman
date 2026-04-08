<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { ref, onMounted } from 'vue'

// ✅ Attach layout
defineOptions({ layout: AdminLayout })

// ✅ Props
const props = defineProps({
  filters: Object,
  users: Object,
})

// ✅ Refs
const q = ref(props.filters?.q ?? '')
const resetTarget = ref(null)
const showResetModal = ref(false)

// ✅ Forms
const createForm = useForm({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  role: 'reader'
})

const resetForm = useForm({
  password: '',
  password_confirmation: ''
})

// ✅ Methods
const search = () => {
  router.get('/dashboard/users', { q: q.value }, { preserveState: true, preserveScroll: true })
}

const createUser = () => {
  if (createForm.password !== createForm.password_confirmation) {
    createForm.setError('password_confirmation', 'Passwords do not match')
    return
  }
  createForm.post('/dashboard/users', {
    preserveScroll: true,
    onSuccess: () => createForm.reset('name', 'email', 'password', 'password_confirmation', 'role'),
  })
}

const changeRole = (id, role) => {
  useForm({ role }).patch(`/dashboard/users/${id}/role`, { preserveScroll: true })
}

const toggleActive = (id) => {
  router.patch(`/dashboard/users/${id}/toggle-active`, {}, { preserveScroll: true })
}

const openReset = (id) => {
  resetTarget.value = id
  resetForm.reset('password', 'password_confirmation')
  showResetModal.value = true
}

const doReset = () => {
  if (!resetTarget.value) return
  resetForm.post(`/dashboard/users/${resetTarget.value}/reset-password`, {
    preserveScroll: true,
    onSuccess: () => {
      showResetModal.value = false
      resetTarget.value = null
    }
  })
}

const destroyUser = (id) => {
  if (!confirm('Are you sure you want to delete this user?')) return
  router.delete(`/dashboard/users/${id}`, { preserveScroll: true })
}

// ✅ Get status counts
const getRoleCount = (role) => {
  return props.users.data.filter(u => u.role === role).length
}

const getActiveCount = () => {
  return props.users.data.filter(u => u.is_active).length
}

const getVerifiedCount = () => {
  return props.users.data.filter(u => u.email_verified_at).length
}

// ✅ Format date
const formatDate = (date) => {
  if (!date) return '—'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  })
}
</script>

<template>
  <div>
    <Head title="Users Management" />

    <!-- ✅ Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl font-bold text-gray-900">Users Management</h1>
        <p class="text-gray-600 mt-1">Manage user accounts, roles, and permissions</p>
      </div>
      <div class="flex gap-3">
        <div class="relative">
          <input 
            v-model="q"
            @keyup.enter="search"
            type="text" 
            placeholder="Search name or email..."
            class="pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64"
          />
          <svg class="w-5 h-5 text-gray-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        <button 
          @click="search"
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
        >
          Search
        </button>
      </div>
    </div>

    <!-- ✅ Flash Messages -->
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

    <div
      v-if="$page.props.flash?.error"
      class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4"
    >
      <div class="flex items-center">
        <svg class="w-5 h-5 text-red-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-red-800 font-medium">{{ $page.props.flash.error }}</span>
      </div>
    </div>

    <!-- ✅ Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-blue-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Total Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ users.total }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-green-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Active Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ getActiveCount() }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-purple-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Verified</p>
            <p class="text-2xl font-bold text-gray-900">{{ getVerifiedCount() }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg border border-gray-200 p-6 shadow-sm">
        <div class="flex items-center">
          <div class="bg-yellow-100 p-3 rounded-lg mr-4">
            <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-600">Admins</p>
            <p class="text-2xl font-bold text-gray-900">{{ getRoleCount('admin') }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- ✅ Create User Form -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mb-8">
      <div class="p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Create New User
        </h2>
      </div>
      <form @submit.prevent="createUser" class="p-6 space-y-4">
        <div class="grid md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
            <input 
              v-model="createForm.name" 
              type="text" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              :class="{ 'border-red-300': createForm.errors.name }"
              placeholder="Full name"
            />
            <div v-if="createForm.errors.name" class="text-sm text-red-600 mt-1 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ createForm.errors.name }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Email *</label>
            <input 
              v-model="createForm.email" 
              type="email" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              :class="{ 'border-red-300': createForm.errors.email }"
              placeholder="email@example.com"
            />
            <div v-if="createForm.errors.email" class="text-sm text-red-600 mt-1 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ createForm.errors.email }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
            <input 
              v-model="createForm.password" 
              type="password" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              :class="{ 'border-red-300': createForm.errors.password }"
              placeholder="Password"
            />
            <div v-if="createForm.errors.password" class="text-sm text-red-600 mt-1 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ createForm.errors.password }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm *</label>
            <input 
              v-model="createForm.password_confirmation" 
              type="password" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              :class="{ 'border-red-300': createForm.errors.password_confirmation }"
              placeholder="Confirm password"
            />
            <div v-if="createForm.errors.password_confirmation" class="text-sm text-red-600 mt-1 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ createForm.errors.password_confirmation }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
            <select 
              v-model="createForm.role" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            >
              <option value="reader">Reader</option>
              <option value="author">Author</option>
              <option value="admin">Admin</option>
            </select>
          </div>
        </div>

        <div class="flex justify-end">
          <button 
            :disabled="createForm.processing" 
            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="createForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span v-if="createForm.processing">Creating User...</span>
            <span v-else>Create User</span>
          </button>
        </div>
      </form>
    </div>

    <!-- ✅ Users Table -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                User
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Role
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Verified
              </th>
              <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Joined Date
              </th>
              <th class="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>

          <tbody class="bg-white divide-y divide-gray-200">
            <!-- Empty State -->
            <tr v-if="!users.data.length">
              <td colspan="6" class="px-6 py-12 text-center">
                <div class="flex flex-col items-center justify-center text-gray-500">
                  <svg class="w-16 h-16 mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"/>
                  </svg>
                  <p class="text-lg font-medium text-gray-900 mb-1">No users found</p>
                  <p class="text-sm">Get started by creating your first user</p>
                </div>
              </td>
            </tr>

            <!-- Users List -->
            <tr 
              v-for="user in users.data" 
              :key="user.id" 
              class="hover:bg-gray-50 transition-colors duration-150"
            >
              <td class="px-6 py-4">
                <div>
                  <div class="text-gray-900 font-semibold">{{ user.name }}</div>
                  <div class="text-sm text-gray-600">{{ user.email }}</div>
                </div>
              </td>

              <td class="px-6 py-4">
                <select 
                  :value="user.role" 
                  @change="e => changeRole(user.id, e.target.value)"
                  class="border border-gray-300 rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                >
                  <option value="reader">Reader</option>
                  <option value="author">Author</option>
                  <option value="admin">Admin</option>
                </select>
              </td>

              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize"
                  :class="{
                    'bg-green-100 text-green-800': user.is_active,
                    'bg-gray-100 text-gray-800': !user.is_active
                  }"
                >
                  <span 
                    class="w-2 h-2 rounded-full mr-2"
                    :class="{
                      'bg-green-500': user.is_active,
                      'bg-gray-500': !user.is_active
                    }"
                  ></span>
                  {{ user.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>

              <td class="px-6 py-4">
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium capitalize"
                  :class="{
                    'bg-green-100 text-green-800': user.email_verified_at,
                    'bg-yellow-100 text-yellow-800': !user.email_verified_at
                  }"
                >
                  <span 
                    class="w-2 h-2 rounded-full mr-2"
                    :class="{
                      'bg-green-500': user.email_verified_at,
                      'bg-yellow-500': !user.email_verified_at
                    }"
                  ></span>
                  {{ user.email_verified_at ? 'Verified' : 'Unverified' }}
                </span>
              </td>

              <td class="px-6 py-4">
                <span class="text-gray-700">
                  {{ formatDate(user.created_at) }}
                </span>
              </td>

              <td class="px-6 py-4">
                <div class="flex justify-end gap-2">
                  <button
                    @click="toggleActive(user.id)"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium rounded-lg transition-colors duration-200"
                    :class="user.is_active 
                      ? 'text-yellow-700 bg-yellow-50 border border-yellow-200 hover:bg-yellow-100 focus:ring-2 focus:ring-yellow-500' 
                      : 'text-green-700 bg-green-50 border border-green-200 hover:bg-green-100 focus:ring-2 focus:ring-green-500'
                    "
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                        :d="user.is_active 
                          ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636'
                          : 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'
                        "
                      />
                    </svg>
                    {{ user.is_active ? 'Deactivate' : 'Activate' }}
                  </button>

                  <button
                    @click="openReset(user.id)"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                    </svg>
                    Reset Password
                  </button>

                  <button
                    @click="destroyUser(user.id)"
                    class="inline-flex items-center px-3 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors duration-200"
                  >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ✅ Pagination -->
      <div
        v-if="users.prev_page_url || users.next_page_url"
        class="px-6 py-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4"
      >
        <div class="text-sm text-gray-700">
          Showing {{ users.from }} to {{ users.to }} of {{ users.total }} users
        </div>
        
        <div class="flex gap-2">
          <Link
            v-if="users.prev_page_url"
            :href="users.prev_page_url"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            preserve-scroll
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Previous
          </Link>

          <Link
            v-if="users.next_page_url"
            :href="users.next_page_url"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            preserve-scroll
          >
            Next
            <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </Link>
        </div>
      </div>
    </div>

    <!-- ✅ Reset Password Modal -->
    <div 
      v-show="showResetModal" 
      class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center"
    >
      <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-semibold text-gray-900">Reset Password</h3>
        </div>
        
        <form @submit.prevent="doReset" class="p-6 space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
            <input 
              v-model="resetForm.password" 
              type="password" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              :class="{ 'border-red-300': resetForm.errors.password }"
              placeholder="Enter new password"
            />
            <div v-if="resetForm.errors.password" class="text-sm text-red-600 mt-1 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ resetForm.errors.password }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
            <input 
              v-model="resetForm.password_confirmation" 
              type="password" 
              class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              :class="{ 'border-red-300': resetForm.errors.password_confirmation }"
              placeholder="Confirm new password"
            />
            <div v-if="resetForm.errors.password_confirmation" class="text-sm text-red-600 mt-1 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ resetForm.errors.password_confirmation }}
            </div>
          </div>

          <div class="flex justify-end gap-3 pt-4">
            <button
              type="button"
              @click="showResetModal = false"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="resetForm.processing"
              class="inline-flex items-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <svg v-if="resetForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span v-if="resetForm.processing">Resetting...</span>
              <span v-else>Reset Password</span>
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