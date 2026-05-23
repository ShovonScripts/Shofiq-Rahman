<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

// ✅ Layout
defineOptions({ layout: AdminLayout })

// ✅ Props from Controller
const props = defineProps({
  form: Object,
  settings: Object,
})

// ✅ Settings Form
const settings = useForm({
  site_name: props.form?.site_name ?? '',
  tagline: props.form?.tagline ?? '',
  rss_title: props.form?.rss_title ?? '',
  comment_policy: props.form?.comment_policy ?? '',
  mail_from_name: props.form?.mail_from_name ?? '',
  mail_from_address: props.form?.mail_from_address ?? '',
  social_links: {
    facebook: props.form?.social_links?.facebook ?? '',
    twitter: props.form?.social_links?.twitter ?? '',
    youtube: props.form?.social_links?.youtube ?? '',
  },
  logo: null,
  favicon: null,
})

// ✅ Current URLs for preview
const currentLogoUrl = computed(() => props.form?.site_logo_url ?? null)
const currentFaviconUrl = computed(() => props.form?.site_favicon_url ?? null)

// ✅ Local preview for new selections
const logoPreview = ref(null)
const faviconPreview = ref(null)

const onLogoChange = (e) => {
  const file = e.target.files?.[0]
  settings.logo = file ?? null
  if (logoPreview.value) URL.revokeObjectURL(logoPreview.value)
  logoPreview.value = file ? URL.createObjectURL(file) : null
}

const onFaviconChange = (e) => {
  const file = e.target.files?.[0]
  settings.favicon = file ?? null
  if (faviconPreview.value) URL.revokeObjectURL(faviconPreview.value)
  faviconPreview.value = file ? URL.createObjectURL(file) : null
}

// ✅ Save Settings
const save = () => {
  settings
    .transform((data) => ({
      ...data,
      _method: 'patch',
    }))
    .post(route('dashboard.settings.update'), {
      preserveScroll: true,
      forceFormData: true,
      onSuccess: () => {
        if (logoPreview.value) URL.revokeObjectURL(logoPreview.value)
        if (faviconPreview.value) URL.revokeObjectURL(faviconPreview.value)
        settings.logo = null
        settings.favicon = null
      },
    })
}

// ✅ Test Email Form
const testEmailForm = useForm({ to: '' })
const sendTestEmail = () => {
  testEmailForm.post(route('dashboard.settings.test'), { preserveScroll: true })
}
</script>

<template>
  <div>
    <Head title="Site Settings" />

    <!-- ✅ Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-8">
      <div class="mb-4 sm:mb-0">
        <h1 class="text-2xl font-bold text-gray-900">Site Settings</h1>
        <p class="text-gray-600 mt-1">Configure your website's appearance and functionality</p>
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

    <!-- ✅ Settings Form -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden mb-8">
      <form @submit.prevent="save" class="p-4 sm:p-6 space-y-8" enctype="multipart/form-data">
        <!-- Site Information Section -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Site Information
          </h2>
          <div class="grid md:grid-cols-2 gap-4 sm:p-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Site Name *</label>
              <input
                v-model="settings.site_name"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                :class="{ 'border-red-300': settings.errors.site_name }"
                placeholder="Your Website Name"
                required
              />
              <div v-if="settings.errors.site_name" class="text-sm text-red-600 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ settings.errors.site_name }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Tagline</label>
              <input
                v-model="settings.tagline"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                placeholder="Your website tagline"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">RSS Title *</label>
              <input
                v-model="settings.rss_title"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                :class="{ 'border-red-300': settings.errors.rss_title }"
                placeholder="RSS Feed Title"
                required
              />
              <div v-if="settings.errors.rss_title" class="text-sm text-red-600 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ settings.errors.rss_title }}
              </div>
            </div>
          </div>

          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Comment Policy</label>
            <textarea
              v-model="settings.comment_policy"
              rows="4"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              placeholder="Your comment policy for users"
            ></textarea>
          </div>
        </div>

        <!-- Mail Configuration Section -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            Mail Configuration
          </h2>
          <div class="grid md:grid-cols-2 gap-4 sm:p-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Mail From Name *</label>
              <input
                v-model="settings.mail_from_name"
                type="text"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                :class="{ 'border-red-300': settings.errors.mail_from_name }"
                placeholder="Sender Name"
                required
              />
              <div v-if="settings.errors.mail_from_name" class="text-sm text-red-600 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ settings.errors.mail_from_name }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Mail From Address *</label>
              <input
                v-model="settings.mail_from_address"
                type="email"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                :class="{ 'border-red-300': settings.errors.mail_from_address }"
                placeholder="sender@example.com"
                required
              />
              <div v-if="settings.errors.mail_from_address" class="text-sm text-red-600 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ settings.errors.mail_from_address }}
              </div>
            </div>
          </div>
        </div>

        <!-- Social Links Section -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9m0 9c-5 0-9-4-9-9s4-9 9-9"/>
            </svg>
            Social Links
          </h2>
          <div class="grid md:grid-cols-3 gap-4 sm:p-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Facebook</label>
              <input
                v-model="settings.social_links.facebook"
                type="url"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                :class="{ 'border-red-300': settings.errors['social_links.facebook'] }"
                placeholder="https://facebook.com/yourpage"
              />
              <div v-if="settings.errors['social_links.facebook']" class="text-sm text-red-600 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ settings.errors['social_links.facebook'] }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Twitter/X</label>
              <input
                v-model="settings.social_links.twitter"
                type="url"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                :class="{ 'border-red-300': settings.errors['social_links.twitter'] }"
                placeholder="https://x.com/yourhandle"
              />
              <div v-if="settings.errors['social_links.twitter']" class="text-sm text-red-600 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ settings.errors['social_links.twitter'] }}
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">YouTube</label>
              <input
                v-model="settings.social_links.youtube"
                type="url"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
                :class="{ 'border-red-300': settings.errors['social_links.youtube'] }"
                placeholder="https://youtube.com/@yourchannel"
              />
              <div v-if="settings.errors['social_links.youtube']" class="text-sm text-red-600 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ settings.errors['social_links.youtube'] }}
              </div>
            </div>
          </div>
        </div>

        <!-- Branding Section -->
        <div>
          <h2 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
            <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            Branding
          </h2>
          <div class="grid md:grid-cols-2 gap-4 sm:p-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Site Logo</label>
              <input
                type="file"
                accept=".svg,.png,.jpg,.jpeg,.webp,.ico"
                @change="onLogoChange"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                :class="{ 'border-red-300': settings.errors.logo }"
              />
              <p class="text-xs text-gray-500 mt-1">SVG, PNG, JPG, WEBP, ICO • Max 2MB</p>
              <div v-if="settings.errors.logo" class="text-sm text-red-600 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ settings.errors.logo }}
              </div>

              <!-- Logo Preview -->
              <div v-if="logoPreview || currentLogoUrl" class="mt-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                <p class="text-sm font-medium text-gray-700 mb-2">Logo Preview:</p>
                <div class="flex items-center gap-4">
                  <img
                    :src="logoPreview || currentLogoUrl"
                    alt="Logo preview"
                    class="h-12 object-contain"
                  />
                  <span class="text-sm text-gray-500">Current logo will be replaced</span>
                </div>
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Favicon</label>
              <input
                type="file"
                accept=".ico,.png,.svg,.jpg,.jpeg,.webp"
                @change="onFaviconChange"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                :class="{ 'border-red-300': settings.errors.favicon }"
              />
              <p class="text-xs text-gray-500 mt-1">ICO, PNG, SVG, JPG, WEBP • Max 1MB</p>
              <div v-if="settings.errors.favicon" class="text-sm text-red-600 mt-2 flex items-center">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ settings.errors.favicon }}
              </div>

              <!-- Favicon Preview -->
              <div v-if="faviconPreview || currentFaviconUrl" class="mt-3 p-3 border border-gray-200 rounded-lg bg-gray-50">
                <p class="text-sm font-medium text-gray-700 mb-2">Favicon Preview:</p>
                <div class="flex items-center gap-4">
                  <img
                    :src="faviconPreview || currentFaviconUrl"
                    alt="Favicon preview"
                    class="h-8 w-8 object-contain"
                  />
                  <span class="text-sm text-gray-500">Current favicon will be replaced</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between pt-6 border-t border-gray-200">
          <div></div> <!-- Spacer -->
          <button 
            :disabled="settings.processing" 
            class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="settings.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span v-if="settings.processing">Saving Settings...</span>
            <span v-else>Save Settings</span>
          </button>
        </div>
      </form>
    </div>

    <!-- ✅ Test Email Section -->
    <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden">
      <div class="p-4 sm:p-6 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900 flex items-center">
          <svg class="w-5 h-5 text-gray-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
          </svg>
          Test Email Configuration
        </h2>
      </div>
      <form @submit.prevent="sendTestEmail" class="p-4 sm:p-6">
        <div class="grid md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Recipient Email</label>
            <input
              type="email"
              placeholder="your@email.com"
              v-model="testEmailForm.to"
              class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200"
              :class="{ 'border-red-300': testEmailForm.errors.to }"
            />
            <div v-if="testEmailForm.errors.to" class="text-sm text-red-600 mt-2 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              {{ testEmailForm.errors.to }}
            </div>
          </div>
          
          <div class="flex items-end">
            <button 
              :disabled="testEmailForm.processing" 
              class="inline-flex items-center px-6 py-2.5 text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed w-full justify-center"
            >
              <svg v-if="testEmailForm.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-gray-700" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <svg v-else class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <span v-if="testEmailForm.processing">Sending Test...</span>
              <span v-else>Send Test Email</span>
            </button>
          </div>
        </div>

        <div class="mt-4 p-3 bg-gray-50 rounded-lg">
          <p class="text-sm text-gray-600">
            <strong>Current From Address:</strong><br>
            {{ settings.site_name }} ({{ settings.mail_from_name }} &lt;{{ settings.mail_from_address }}&gt;)
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<style scoped>
/* Custom styling for better form appearance */
input:focus, textarea:focus, select:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Ensure file input is styled properly */
input[type="file"]::-webkit-file-upload-button {
  background: #dbeafe;
  color: #1d4ed8;
  border: 0;
  padding: 0.5rem 1rem;
  border-radius: 9999px;
  font-weight: 600;
  font-size: 0.875rem;
  cursor: pointer;
}

input[type="file"]::-webkit-file-upload-button:hover {
  background: #bfdbfe;
}
</style>