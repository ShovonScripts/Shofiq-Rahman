<!-- resources/js/Pages/Profile.vue -->
<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { useForm, Head } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })

const props = defineProps({
  auth: { type: Object, default: () => ({}) },
})

// Forms
const profileForm = useForm({
  name: props.auth?.user?.name ?? '',
  email: props.auth?.user?.email ?? '',
})

const passwordForm = useForm({
  current_password: '',
  password: '',
  password_confirmation: '',
})

// Actions
const saveProfile = () => {
  profileForm.patch('/profile', { preserveScroll: true })
}
const changePassword = () => {
  passwordForm.patch('/profile/password', {
    preserveScroll: true,
    onSuccess: () =>
      passwordForm.reset('current_password', 'password', 'password_confirmation'),
  })
}
</script>

<template>
  <Head title="প্রোফাইল" />

  <div class="min-h-screen bg-gray-50">
    <!-- Outer container: edge-to-edge on mobile, boxed from sm+ -->
    <div class="max-w-4xl mx-auto px-0 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <header class="px-3 sm:px-0 mb-8 text-center">
        <h1 class="heading-bangla text-3xl md:text-4xl font-bold text-ink mb-2">
          আপনার প্রোফাইল
        </h1>
        <p class="text-muted font-sans">
          আপনার ব্যক্তিগত তথ্য এবং পাসওয়ার্ড পরিচালনা করুন
        </p>
      </header>

      <!-- Update Profile -->
      <section class="px-3 sm:px-0 mb-6">
        <div class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 sm:px-8 py-6">
            <h2 class="heading-bangla text-xl font-bold text-ink mb-6">
              প্রোফাইল তথ্য আপডেট করুন
            </h2>

            <form @submit.prevent="saveProfile" class="space-y-4" novalidate>
              <div>
                <label for="name" class="block text-sm font-medium text-ink mb-2">
                  নাম <span class="text-red-600">*</span>
                </label>
                <input
                  id="name"
                  type="text"
                  v-model="profileForm.name"
                  autocomplete="name"
                  placeholder="আপনার পুরো নাম"
                  :aria-invalid="!!profileForm.errors.name"
                  class="input-bangla w-full"
                  :class="{ 'ring-2 ring-red-500 border-red-300': profileForm.errors.name }"
                />
                <p v-if="profileForm.errors.name" class="text-red-600 text-sm mt-2 font-sans">
                  {{ profileForm.errors.name }}
                </p>
              </div>

              <div>
                <label for="email" class="block text-sm font-medium text-ink mb-2">
                  ইমেইল <span class="text-red-600">*</span>
                </label>
                <input
                  id="email"
                  type="email"
                  v-model="profileForm.email"
                  autocomplete="email"
                  placeholder="আপনার ইমেইল ঠিকানা"
                  :aria-invalid="!!profileForm.errors.email"
                  class="input-bangla w-full"
                  :class="{ 'ring-2 ring-red-500 border-red-300': profileForm.errors.email }"
                />
                <p v-if="profileForm.errors.email" class="text-red-600 text-sm mt-2 font-sans">
                  {{ profileForm.errors.email }}
                </p>
              </div>

              <!-- Actions -->
              <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                <button
                  type="submit"
                  class="btn-bangla btn-bangla-primary min-w-[180px] flex items-center justify-center gap-2"
                  :disabled="profileForm.processing"
                >
                  <svg v-if="profileForm.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                  </svg>
                  {{ profileForm.processing ? 'সেভ হচ্ছে…' : 'প্রোফাইল আপডেট করুন' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- Change Password -->
      <section class="px-3 sm:px-0">
        <div class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-4 sm:px-8 py-6">
            <h2 class="heading-bangla text-xl font-bold text-ink mb-6">
              পাসওয়ার্ড পরিবর্তন করুন
            </h2>

            <form @submit.prevent="changePassword" class="space-y-4" novalidate>
              <div>
                <label for="current_password" class="block text-sm font-medium text-ink mb-2">
                  বর্তমান পাসওয়ার্ড <span class="text-red-600">*</span>
                </label>
                <input
                  id="current_password"
                  type="password"
                  v-model="passwordForm.current_password"
                  autocomplete="current-password"
                  placeholder="আপনার বর্তমান পাসওয়ার্ড"
                  :aria-invalid="!!passwordForm.errors.current_password"
                  class="input-bangla w-full"
                  :class="{ 'ring-2 ring-red-500 border-red-300': passwordForm.errors.current_password }"
                />
                <p v-if="passwordForm.errors.current_password" class="text-red-600 text-sm mt-2 font-sans">
                  {{ passwordForm.errors.current_password }}
                </p>
              </div>

              <div>
                <label for="password" class="block text-sm font-medium text-ink mb-2">
                  নতুন পাসওয়ার্ড <span class="text-red-600">*</span>
                </label>
                <input
                  id="password"
                  type="password"
                  v-model="passwordForm.password"
                  autocomplete="new-password"
                  placeholder="নতুন পাসওয়ার্ড"
                  :aria-invalid="!!passwordForm.errors.password"
                  class="input-bangla w-full"
                  :class="{ 'ring-2 ring-red-500 border-red-300': passwordForm.errors.password }"
                />
                <p v-if="passwordForm.errors.password" class="text-red-600 text-sm mt-2 font-sans">
                  {{ passwordForm.errors.password }}
                </p>
              </div>

              <div>
                <label for="password_confirmation" class="block text-sm font-medium text-ink mb-2">
                  নতুন পাসওয়ার্ড নিশ্চিত করুন <span class="text-red-600">*</span>
                </label>
                <input
                  id="password_confirmation"
                  type="password"
                  v-model="passwordForm.password_confirmation"
                  autocomplete="new-password"
                  placeholder="নতুন পাসওয়ার্ড আবার লিখুন"
                  :aria-invalid="!!passwordForm.errors.password_confirmation"
                  class="input-bangla w-full"
                  :class="{ 'ring-2 ring-red-500 border-red-300': passwordForm.errors.password_confirmation }"
                />
                <p v-if="passwordForm.errors.password_confirmation" class="text-red-600 text-sm mt-2 font-sans">
                  {{ passwordForm.errors.password_confirmation }}
                </p>
              </div>

              <!-- Actions -->
              <div class="pt-2 flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3">
                <button
                  type="submit"
                  class="btn-bangla btn-bangla-secondary min-w-[200px] flex items-center justify-center gap-2"
                  :disabled="passwordForm.processing"
                >
                  <svg v-if="passwordForm.processing" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                  </svg>
                  {{ passwordForm.processing ? 'আপডেট হচ্ছে…' : 'পাসওয়ার্ড পরিবর্তন করুন' }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- Flash success -->
      <section v-if="$page.props.flash?.success" class="px-3 sm:px-0 mt-6">
        <div class="rounded-md sm:rounded-xl bg-green-50 border border-green-200 shadow-sm">
          <div class="px-4 sm:px-8 py-4 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-green-100 grid place-items-center">
              <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <p class="text-green-800 font-medium font-sans text-sm">
              {{ $page.props.flash.success }}
            </p>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<style scoped>
/* Reuse your global .input-bangla + .btn-bangla utilities.
   This file only ensures spacing/gutters are consistent. */
</style>
