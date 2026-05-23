<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  profile: {
    type: Object,
    default: null,
  },
})

const profileData = props.profile ?? {
  name: '', pen_name: '', date_of_birth: '', location: '', email_public: '',
  website: '', bio_md: '', achievements_summary_md: '',
  social_links: { facebook: '', twitter: '', youtube: '', instagram: '' },
  is_active: true, avatar_url: null, cover_url: null,
}

const form = useForm({
  name: profileData.name ?? '',
  pen_name: profileData.pen_name ?? '',
  date_of_birth: profileData.date_of_birth ?? '',
  location: profileData.location ?? '',
  email_public: profileData.email_public ?? '',
  website: profileData.website ?? '',
  bio_md: profileData.bio_md ?? '',
  achievements_summary_md: profileData.achievements_summary_md ?? '',
  social_links: profileData.social_links ?? { facebook:'', twitter:'', youtube:'', instagram:'' },
  is_active: profileData.is_active ?? true,
  avatar: null, cover: null,
})

const submit = () => {
  form
    .transform((data) => ({
      ...data, _method: 'put',
    }))
    .post(route('dashboard.about.update'), {
      forceFormData: true, preserveScroll: true,
    })
}
</script>

<template>
  <Head title="About — Author Profile" />

  <!-- Page Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Author Profile</h1>
      <p class="text-muted text-sm mt-1">Manage your personal information and public profile</p>
    </div>
    <div class="flex gap-2 flex-wrap">
      <Link :href="route('dashboard.about.achievements.index')" class="btn-bangla btn-bangla-secondary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        Achievements
      </Link>
      <Link :href="route('dashboard.about.events.index')" class="btn-bangla btn-bangla-secondary flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        Life Events
      </Link>
    </div>
  </div>

  <!-- Flash Message -->
  <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3 mb-6">
    <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    <span class="text-green-800 font-medium">{{ $page.props.flash.success }}</span>
  </div>

  <!-- Profile Form -->
  <div class="card-bangla">
    <form @submit.prevent="submit" class="p-6 space-y-8" enctype="multipart/form-data">
      <!-- Personal Information Section -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Personal Information</h3>
        <p class="text-muted text-sm">Your basic profile details</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="label-bangla">Full Name *</label>
            <input v-model="form.name" type="text" class="input-bangla" required />
            <p v-if="form.errors.name" class="text-sm text-red-600 mt-1">{{ form.errors.name }}</p>
            <p class="text-xs text-muted mt-1">Your real name as it should appear publicly</p>
          </div>
          <div>
            <label class="label-bangla">Pen Name</label>
            <input v-model="form.pen_name" type="text" class="input-bangla" />
            <p v-if="form.errors.pen_name" class="text-sm text-red-600 mt-1">{{ form.errors.pen_name }}</p>
            <p class="text-xs text-muted mt-1">Optional pen name or pseudonym</p>
          </div>
          <div>
            <label class="label-bangla">Date of Birth</label>
            <input v-model="form.date_of_birth" type="date" class="input-bangla" />
            <p v-if="form.errors.date_of_birth" class="text-sm text-red-600 mt-1">{{ form.errors.date_of_birth }}</p>
          </div>
          <div>
            <label class="label-bangla">Location</label>
            <input v-model="form.location" type="text" class="input-bangla" placeholder="City, Country" />
            <p v-if="form.errors.location" class="text-sm text-red-600 mt-1">{{ form.errors.location }}</p>
          </div>
        </div>
      </div>

      <!-- Contact Information Section -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Contact Information</h3>
        <p class="text-muted text-sm">How readers can reach you</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="label-bangla">Public Email</label>
            <input v-model="form.email_public" type="email" class="input-bangla" placeholder="email@example.com" />
            <p v-if="form.errors.email_public" class="text-sm text-red-600 mt-1">{{ form.errors.email_public }}</p>
            <p class="text-xs text-muted mt-1">This email will be visible to the public</p>
          </div>
          <div>
            <label class="label-bangla">Website</label>
            <input v-model="form.website" type="url" class="input-bangla" placeholder="https://example.com" />
            <p v-if="form.errors.website" class="text-sm text-red-600 mt-1">{{ form.errors.website }}</p>
          </div>
        </div>
      </div>

      <!-- Biography Section -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Biography</h3>
        <p class="text-muted text-sm">Tell your story</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Bio (Markdown Supported)</label>
          <textarea v-model="form.bio_md" rows="6" class="input-bangla" placeholder="Tell your story... Markdown formatting is supported."></textarea>
          <p v-if="form.errors.bio_md" class="text-sm text-red-600 mt-1">{{ form.errors.bio_md }}</p>
          <p class="text-xs text-muted mt-1">Supports Markdown formatting for rich text</p>
        </div>
      </div>

      <!-- Achievements Section -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Achievements Summary</h3>
        <p class="text-muted text-sm">Highlights of your accomplishments</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Achievements (Markdown Supported)</label>
          <textarea v-model="form.achievements_summary_md" rows="4" class="input-bangla" placeholder="List your achievements, awards, and milestones..."></textarea>
          <p v-if="form.errors.achievements_summary_md" class="text-sm text-red-600 mt-1">{{ form.errors.achievements_summary_md }}</p>
          <p class="text-xs text-muted mt-1">For detailed achievements, use the Achievements page</p>
        </div>
      </div>

      <!-- Profile Media Section -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Profile Media</h3>
        <p class="text-muted text-sm">Upload your avatar and cover images</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="label-bangla">Avatar Image</label>
            <input type="file" accept="image/*" @change="e => form.avatar = e.target.files[0]" class="input-bangla" />
            <p v-if="form.errors.avatar" class="text-sm text-red-600 mt-1">{{ form.errors.avatar }}</p>
            <p class="text-xs text-muted mt-1">Recommended: 400×400px, square aspect ratio</p>
          </div>
          <div>
            <label class="label-bangla">Cover Image</label>
            <input type="file" accept="image/*" @change="e => form.cover = e.target.files[0]" class="input-bangla" />
            <p v-if="form.errors.cover" class="text-sm text-red-600 mt-1">{{ form.errors.cover }}</p>
            <p class="text-xs text-muted mt-1">Recommended: 1200×400px, landscape aspect ratio</p>
          </div>
        </div>
      </div>

      <!-- Social Links Section -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Social Media Links</h3>
        <p class="text-muted text-sm">Connect with readers on social platforms</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="label-bangla">Facebook</label>
            <input v-model="form.social_links.facebook" type="url" class="input-bangla" placeholder="https://facebook.com/username" />
            <p v-if="form.errors['social_links.facebook']" class="text-sm text-red-600 mt-1">{{ form.errors['social_links.facebook'] }}</p>
          </div>
          <div>
            <label class="label-bangla">Twitter/X</label>
            <input v-model="form.social_links.twitter" type="url" class="input-bangla" placeholder="https://twitter.com/username" />
            <p v-if="form.errors['social_links.twitter']" class="text-sm text-red-600 mt-1">{{ form.errors['social_links.twitter'] }}</p>
          </div>
          <div>
            <label class="label-bangla">YouTube</label>
            <input v-model="form.social_links.youtube" type="url" class="input-bangla" placeholder="https://youtube.com/username" />
            <p v-if="form.errors['social_links.youtube']" class="text-sm text-red-600 mt-1">{{ form.errors['social_links.youtube'] }}</p>
          </div>
          <div>
            <label class="label-bangla">Instagram</label>
            <input v-model="form.social_links.instagram" type="url" class="input-bangla" placeholder="https://instagram.com/username" />
            <p v-if="form.errors['social_links.instagram']" class="text-sm text-red-600 mt-1">{{ form.errors['social_links.instagram'] }}</p>
          </div>
        </div>
      </div>

      <!-- Status & Submit -->
      <div class="flex items-center justify-between pt-6 border-t border-gray-200 px-6">
        <div class="flex items-center">
          <input id="is_active" type="checkbox" v-model="form.is_active" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2" />
          <label for="is_active" class="ml-2 text-sm font-medium text-gray-700">Profile is active and visible to public</label>
        </div>
        <button type="submit" :disabled="form.processing" class="btn-bangla btn-bangla-primary flex items-center gap-2 px-6">
          <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span v-if="form.processing">Saving Changes...</span>
          <span v-else>Save Profile</span>
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
