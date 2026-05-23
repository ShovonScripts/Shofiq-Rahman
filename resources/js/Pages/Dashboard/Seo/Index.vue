<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue'
import { Head, useForm, Link } from '@inertiajs/vue3'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  seo: { type: Object, required: true },
})

const form = useForm({
  meta_title: props.seo.meta_title || '',
  meta_description: props.seo.meta_description || '',
  meta_keywords: props.seo.meta_keywords || '',
  meta_image: props.seo.meta_image || '',
  google_analytics_id: props.seo.google_analytics_id || '',
  google_tag_manager_id: props.seo.google_tag_manager_id || '',
  google_site_verification: props.seo.google_site_verification || '',
  bing_site_verification: props.seo.bing_site_verification || '',
  yandex_site_verification: props.seo.yandex_site_verification || '',
  facebook_pixel_id: props.seo.facebook_pixel_id || '',
  facebook_app_id: props.seo.facebook_app_id || '',
  twitter_handle: props.seo.twitter_handle || '',
})

const submit = () => {
  form.patch('/dashboard/seo', {
    preserveScroll: true,
    onSuccess: () => form.reset(),
  })
}

const pages = [
  { name: 'Home', route: '/' },
  { name: 'Blog', route: '/blog' },
  { name: 'Books', route: '/books' },
  { name: 'About', route: '/about' },
  { name: 'Contact', route: '/contact' },
]
</script>

<template>
  <Head title="SEO Manager" />
  <div class="space-y-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 heading-bangla">SEO Manager</h1>
        <p class="text-muted text-sm mt-1">Configure global SEO settings for your site</p>
      </div>
      <div class="flex gap-2">
        <Link href="/dashboard/seo/analyze" class="btn-bangla btn-bangla-secondary flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
          </svg>
          Analyze
        </Link>
        <a href="/sitemap.xml" target="_blank" class="btn-bangla btn-bangla-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
          </svg>
          Sitemap
        </a>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <!-- Global Meta -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
          <h2 class="text-lg font-semibold text-gray-900">Global Meta Tags</h2>
          <p class="text-sm text-muted mt-0.5">Default meta tags for pages without specific SEO data</p>
        </div>
        <div class="p-4 sm:p-6 space-y-5">
          <div>
            <label class="label-bangla" for="meta_title">Default Meta Title</label>
            <input id="meta_title" v-model="form.meta_title" type="text" maxlength="70"
              class="input-bangla" placeholder="Site title — e.g. Shofiq Rahman | Writer" />
            <div class="flex justify-between mt-1">
              <div class="text-red-600 text-sm" v-if="form.errors.meta_title">{{ form.errors.meta_title }}</div>
              <span class="text-xs text-gray-400 ml-auto">{{ form.meta_title.length }}/70</span>
            </div>
          </div>

          <div>
            <label class="label-bangla" for="meta_description">Default Meta Description</label>
            <textarea id="meta_description" v-model="form.meta_description" rows="3" maxlength="160"
              class="input-bangla" placeholder="Brief site description for search results"></textarea>
            <div class="flex justify-between mt-1">
              <div class="text-red-600 text-sm" v-if="form.errors.meta_description">{{ form.errors.meta_description }}</div>
              <span class="text-xs text-gray-400 ml-auto">{{ form.meta_description.length }}/160</span>
            </div>
          </div>

          <div>
            <label class="label-bangla" for="meta_keywords">Meta Keywords (comma-separated)</label>
            <input id="meta_keywords" v-model="form.meta_keywords" type="text" maxlength="255"
              class="input-bangla" placeholder="bangla literature, books, poetry, essays" />
            <div v-if="form.errors.meta_keywords" class="text-red-600 text-sm mt-1">{{ form.errors.meta_keywords }}</div>
          </div>

          <div>
            <label class="label-bangla" for="meta_image">Default OG Image URL</label>
            <input id="meta_image" v-model="form.meta_image" type="url" maxlength="500"
              class="input-bangla" placeholder="https://example.com/images/og-default.jpg" />
            <p class="text-xs text-gray-400 mt-1">Recommended size: 1200×630px. Used when a page has no specific image.</p>
            <div v-if="form.errors.meta_image" class="text-red-600 text-sm mt-1">{{ form.errors.meta_image }}</div>
          </div>
        </div>
      </div>

      <!-- Analytics & Verification -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
          <h2 class="text-lg font-semibold text-gray-900">Analytics & Verification</h2>
          <p class="text-sm text-muted mt-0.5">Tracking codes and search engine verification</p>
        </div>
        <div class="p-4 sm:p-6 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="label-bangla" for="google_analytics_id">Google Analytics ID (G-)</label>
              <input id="google_analytics_id" v-model="form.google_analytics_id" type="text"
                class="input-bangla" placeholder="G-XXXXXXXXXX" />
              <div v-if="form.errors.google_analytics_id" class="text-red-600 text-sm mt-1">{{ form.errors.google_analytics_id }}</div>
            </div>
            <div>
              <label class="label-bangla" for="google_tag_manager_id">Google Tag Manager ID</label>
              <input id="google_tag_manager_id" v-model="form.google_tag_manager_id" type="text"
                class="input-bangla" placeholder="GTM-XXXXXXX" />
              <div v-if="form.errors.google_tag_manager_id" class="text-red-600 text-sm mt-1">{{ form.errors.google_tag_manager_id }}</div>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
              <label class="label-bangla" for="google_site_verification">Google Site Verification</label>
              <input id="google_site_verification" v-model="form.google_site_verification" type="text"
                class="input-bangla" placeholder="Google verification code" />
              <p class="text-xs text-gray-400 mt-1">Content inside <code>google-site-verification</code> meta tag</p>
              <div v-if="form.errors.google_site_verification" class="text-red-600 text-sm mt-1">{{ form.errors.google_site_verification }}</div>
            </div>
            <div>
              <label class="label-bangla" for="bing_site_verification">Bing Site Verification</label>
              <input id="bing_site_verification" v-model="form.bing_site_verification" type="text"
                class="input-bangla" placeholder="Bing verification code" />
              <p class="text-xs text-gray-400 mt-1">Content inside <code>msvalidate.01</code> meta tag</p>
              <div v-if="form.errors.bing_site_verification" class="text-red-600 text-sm mt-1">{{ form.errors.bing_site_verification }}</div>
            </div>
            <div>
              <label class="label-bangla" for="yandex_site_verification">Yandex Site Verification</label>
              <input id="yandex_site_verification" v-model="form.yandex_site_verification" type="text"
                class="input-bangla" placeholder="Yandex verification code" />
              <p class="text-xs text-gray-400 mt-1">Content inside <code>yandex-verification</code> meta tag</p>
              <div v-if="form.errors.yandex_site_verification" class="text-red-600 text-sm mt-1">{{ form.errors.yandex_site_verification }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Social / Open Graph -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
          <h2 class="text-lg font-semibold text-gray-900">Social & Open Graph</h2>
          <p class="text-sm text-muted mt-0.5">Configure how your site appears when shared on social media</p>
        </div>
        <div class="p-4 sm:p-6 space-y-5">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
              <label class="label-bangla" for="facebook_app_id">Facebook App ID</label>
              <input id="facebook_app_id" v-model="form.facebook_app_id" type="text"
                class="input-bangla" placeholder="123456789012345" />
              <p class="text-xs text-gray-400 mt-1">Required for Facebook Insights on shared links</p>
              <div v-if="form.errors.facebook_app_id" class="text-red-600 text-sm mt-1">{{ form.errors.facebook_app_id }}</div>
            </div>
            <div>
              <label class="label-bangla" for="facebook_pixel_id">Facebook Pixel ID</label>
              <input id="facebook_pixel_id" v-model="form.facebook_pixel_id" type="text"
                class="input-bangla" placeholder="123456789012345" />
              <div v-if="form.errors.facebook_pixel_id" class="text-red-600 text-sm mt-1">{{ form.errors.facebook_pixel_id }}</div>
            </div>
          </div>

          <div>
            <label class="label-bangla" for="twitter_handle">Twitter/X Handle</label>
            <input id="twitter_handle" v-model="form.twitter_handle" type="text"
              class="input-bangla" placeholder="@username" />
            <p class="text-xs text-gray-400 mt-1">Used in <code>twitter:site</code> meta tag. Include @ or just the handle.</p>
            <div v-if="form.errors.twitter_handle" class="text-red-600 text-sm mt-1">{{ form.errors.twitter_handle }}</div>
          </div>
        </div>
      </div>

      <!-- Important Pages Preview -->
      <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-gray-50/50">
          <h2 class="text-lg font-semibold text-gray-900">Page Preview</h2>
          <p class="text-sm text-muted mt-0.5">Expected title and description for each important page</p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-gray-100">
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Page</th>
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expected Title</th>
                <th class="px-4 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expected Description</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
              <tr v-for="p in pages" :key="p.name">
                <td class="px-4 sm:px-6 py-3">
                  <a :href="p.route" target="_blank" class="text-blue-600 hover:text-blue-800 text-sm font-medium">{{ p.name }}</a>
                </td>
                <td class="px-4 sm:px-6 py-3 text-sm text-gray-700">
                  {{ p.name === 'Home' ? (form.meta_title || seo.meta_title || 'Site Title') : `${p.name} — ${form.meta_title || seo.meta_title || 'Site Title'}` }}
                </td>
                <td class="px-4 sm:px-6 py-3 text-sm text-gray-500 max-w-xs truncate">
                  {{ form.meta_description || seo.meta_description || 'Site description' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Submit -->
      <div class="flex items-center justify-end gap-3 pb-8">
        <button type="submit" class="btn-bangla btn-bangla-primary px-8"
          :disabled="form.processing"
          :class="{ 'opacity-50': form.processing }">
          <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
          </svg>
          {{ form.processing ? 'Saving...' : 'Save SEO Settings' }}
        </button>
      </div>
    </form>
  </div>
</template>
