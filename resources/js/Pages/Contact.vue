<!-- resources/js/Pages/Contact.vue -->
<script setup>
import AppLayout from '../Layouts/AppLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'

defineOptions({ layout: AppLayout })

const form = useForm({
  name: '',
  email: '',
  subject: '',
  message: '',
})

const onSubmit = () => {
  form.post('/contact', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Head>
      <title>যোগাযোগ</title>
      <meta
        name="description"
        content="লেখকের সাথে সরাসরি যোগাযোগ করুন। আপনার মতামত, প্রশ্ন বা যে কোনো বার্তা পাঠান।"
      />
    </Head>

    <!-- Outer container: edge-to-edge on mobile -->
    <div class="max-w-4xl mx-auto px-0 sm:px-6 lg:px-8 py-6 sm:py-8">
      <!-- Header -->
      <div class="px-3 sm:px-0 text-center mb-8 sm:mb-12">
        <h1 class="heading-bangla text-3xl md:text-4xl font-bold text-ink mb-3">
          যোগাযোগ করুন
        </h1>
        <p class="text-lg text-muted max-w-2xl mx-auto leading-relaxed-bangla font-sans">
          আপনার মূল্যবান মতামত, প্রশ্ন অথবা যে কোনো বার্তা আমাদের কাছে পৌঁছে দিন। আমরা যত দ্রুত সম্ভব আপনার সাথে যোগাযোগ করব।
        </p>
      </div>

      <!-- Contact Form Card -->
      <section class="px-3 sm:px-0">
        <div class="rounded-md sm:rounded-xl bg-white border border-gray-100 shadow-sm overflow-hidden">
          <div class="px-3 sm:px-8 py-6 sm:py-8">
            <h2 class="heading-bangla text-xl font-bold text-ink mb-6">
              বার্তা পাঠান
            </h2>

            <form @submit.prevent="onSubmit" class="space-y-6" novalidate>
              <!-- Name & Email -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label for="name" class="block text-sm font-medium text-ink mb-2">
                    আপনার নাম <span class="text-red-600">*</span>
                  </label>
                  <input
                    id="name"
                    type="text"
                    v-model="form.name"
                    required
                    :disabled="form.processing"
                    :aria-invalid="!!form.errors.name"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-all font-sans"
                    placeholder="আপনার পুরো নাম লিখুন"
                  />
                  <p v-if="form.errors.name" class="text-red-600 text-sm mt-2 font-sans">
                    {{ form.errors.name }}
                  </p>
                </div>

                <div>
                  <label for="email" class="block text-sm font-medium text-ink mb-2">
                    ইমেইল ঠিকানা <span class="text-red-600">*</span>
                  </label>
                  <input
                    id="email"
                    type="email"
                    v-model="form.email"
                    required
                    autocomplete="email"
                    :disabled="form.processing"
                    :aria-invalid="!!form.errors.email"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-all font-sans"
                    placeholder="you@example.com"
                  />
                  <p v-if="form.errors.email" class="text-red-600 text-sm mt-2 font-sans">
                    {{ form.errors.email }}
                  </p>
                </div>
              </div>

              <!-- Subject -->
              <div>
                <label for="subject" class="block text-sm font-medium text-ink mb-2">
                  বিষয় <span class="text-muted text-sm font-normal">(ঐচ্ছিক)</span>
                </label>
                <input
                  id="subject"
                  type="text"
                  v-model="form.subject"
                  :disabled="form.processing"
                  :aria-invalid="!!form.errors.subject"
                  class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-all font-sans"
                  placeholder="বার্তার বিষয় লিখুন"
                />
                <p v-if="form.errors.subject" class="text-red-600 text-sm mt-2 font-sans">
                  {{ form.errors.subject }}
                </p>
              </div>

              <!-- Message -->
              <div>
                <label for="message" class="block text-sm font-medium text-ink mb-2">
                  আপনার বার্তা <span class="text-red-600">*</span>
                </label>
                <textarea
                  id="message"
                  rows="6"
                  v-model="form.message"
                  required
                  :disabled="form.processing"
                  :aria-invalid="!!form.errors.message"
                  class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:ring-2 focus:ring-primary-600 focus:border-primary-600 transition-all resize-none font-sans"
                  placeholder="আপনার মূল্যবান বার্তা এখানে লিখুন…"
                ></textarea>
                <p v-if="form.errors.message" class="text-red-600 text-sm mt-2 font-sans">
                  {{ form.errors.message }}
                </p>
              </div>

              <!-- Submit (standardized) -->
              <div
                class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4 pt-2"
              >
                <div class="text-sm text-muted font-sans order-2 sm:order-1">
                  <span class="text-red-600">*</span> চিহ্নিত ফিল্ডগুলো আবশ্যক
                </div>

                <button
                  type="submit"
                  :disabled="form.processing"
                  :aria-disabled="form.processing ? 'true' : 'false'"
                  class="order-1 sm:order-2 btn-bangla btn-bangla-primary min-w-[150px] flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                  <!-- Loading state -->
                  <span v-if="form.processing" class="flex items-center gap-2">
                    <!-- Standard spinner (no corrupted glyphs) -->
                    <svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                      <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0a12 12 0 00-8 20.784L6 18.137A7.96 7.96 0 014 12z" />
                    </svg>
                    পাঠানো হচ্ছে…
                  </span>

                  <!-- Idle state -->
                  <span v-else class="flex items-center gap-2">
                    <!-- Paper plane icon -->
                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                    </svg>
                    বার্তা পাঠান
                  </span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- Success -->
      <div v-if="$page.props.flash?.success" class="px-3 sm:px-0 mt-8">
        <div class="rounded-md sm:rounded-xl bg-green-50 border border-green-200 shadow-sm">
          <div class="px-3 sm:px-6 py-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-green-100 grid place-items-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-green-800 font-semibold font-sans">বার্তা সফলভাবে পাঠানো হয়েছে!</h3>
              <p class="text-green-700 text-sm mt-0.5 font-sans">{{ $page.props.flash.success }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Client-side error summary -->
      <div v-if="form.hasErrors" class="px-3 sm:px-0 mt-8">
        <div class="rounded-md sm:rounded-xl bg-red-50 border border-red-200 shadow-sm">
          <div class="px-3 sm:px-6 py-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 grid place-items-center">
              <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <div>
              <h3 class="text-red-800 font-semibold font-sans">বার্তা পাঠানো যায়নি</h3>
              <p class="text-red-700 text-sm mt-0.5 font-sans">দুঃখিত, কিছু ভুল হয়েছে। দয়া করে আবার চেষ্টা করুন।</p>
            </div>
          </div>
        </div>
      </div>
    </div> <!-- /outer -->
  </div>
</template>
