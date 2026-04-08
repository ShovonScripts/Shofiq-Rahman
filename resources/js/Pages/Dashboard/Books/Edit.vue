<script setup>
import { useForm, Link, Head } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

defineOptions({ layout: AdminLayout })

const props = defineProps({
  book: { type: Object, required: true },
  genres: { type: Array, default: () => [] },
  categories: { type: Array, default: () => [] },
})

const initialGenreIds = Array.isArray(props.book.genre_ids)
  ? [...props.book.genre_ids]
  : (() => {
      const names = (props.book.genres || '').toString().split(/[,\uFF0C\u060C;|]+/u).map(s => s.trim()).filter(Boolean)
      return props.genres.filter(g => names.some(n => n.toLowerCase() === g.name.toLowerCase())).map(g => g.id)
    })()

const initialCategoryIds = Array.isArray(props.book.category_ids)
  ? [...props.book.category_ids]
  : (() => {
      const names = (props.book.categories || '').toString().split(/[,\uFF0C\u060C;|]+/u).map(s => s.trim()).filter(Boolean)
      return props.categories.filter(c => names.some(n => n.toLowerCase() === c.name.toLowerCase())).map(c => c.id)
    })()

const initialLinks = Array.isArray(props.book.links)
  ? props.book.links.map(l => ({
      label: (l.label ?? '').toString(), url: (l.url ?? '').toString(),
      is_primary: !!l.is_primary, sort_order: Number.isFinite(l.sort_order) ? l.sort_order : 0,
    }))
  : []

const form = useForm({
  title: props.book.title ?? '', subtitle: props.book.subtitle ?? '',
  description: props.book.description ?? '', status: props.book.status ?? 'draft',
  published_at: props.book.published_at ?? null, cover: null,
  genres: initialGenreIds, categories: initialCategoryIds, links: initialLinks,
})

const onCoverChange = (e) => {
  form.cover = e.target.files?.[0] ?? null
}

const addLink = () => {
  form.links.push({ label: '', url: '', is_primary: false, sort_order: form.links.length })
}

const removeLink = (i) => form.links.splice(i, 1)

const submit = () => {
  const trimmedTitle = (form.title || '').trim()
  if (!trimmedTitle) {
    form.setError('title', 'The title field is required.')
    return
  }
  form
    .transform((data) => {
      const toCsv = (ids, opts) =>
        (ids || []).map(id => opts.find(o => o.id === id)?.name).filter(Boolean).join(', ')
      const cleanLinks = (data.links || []).map((l, idx) => ({
        label: (l.label || '').trim() || 'Buy',
        url: (l.url || '').trim(),
        is_primary: !!l.is_primary,
        sort_order: Number.isFinite(l.sort_order) ? l.sort_order : idx,
      }))
      return { ...data, title: trimmedTitle, genres: toCsv(data.genres, props.genres), categories: toCsv(data.categories, props.categories), links: cleanLinks, _method: 'put' }
    })
    .post(`/dashboard/books/${props.book.id}`, {
      forceFormData: true, preserveScroll: true,
      onSuccess: () => router.visit(`/dashboard/books/${props.book.id}/edit`, { replace: true, preserveScroll: true }),
    })
}
</script>

<template>
  <Head :title="`Edit: ${props.book.title || 'Book'}`" />

  <!-- Header -->
  <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
      <h1 class="heading-bangla heading-bangla-2 text-gray-900">Edit Book</h1>
      <p class="text-muted text-sm mt-1">Update book details</p>
    </div>
    <Link href="/dashboard/books" class="btn-bangla btn-bangla-secondary flex items-center gap-2">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
      Back to Books
    </Link>
  </div>

  <!-- Flash -->
  <div v-if="$page.props.flash?.success" class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3 mb-6">
    <div class="w-5 h-5 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
      <svg class="w-4 h-4 text-green-600" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
    </div>
    <span class="text-green-800 font-medium">{{ $page.props.flash.success }}</span>
  </div>

  <!-- Form -->
  <div class="card-bangla">
    <form @submit.prevent="submit" class="p-6 space-y-8">
      <!-- Title & Subtitle -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Book Information</h3>
        <p class="text-muted text-sm">Title and optional subtitle</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="label-bangla">Title *</label>
            <input v-model="form.title" class="input-bangla" placeholder="Book title" required />
            <p v-if="form.errors.title" class="text-sm text-red-600 mt-1">{{ form.errors.title }}</p>
          </div>
          <div>
            <label class="label-bangla">Subtitle</label>
            <input v-model="form.subtitle" class="input-bangla" placeholder="Book subtitle (optional)" />
            <p v-if="form.errors.subtitle" class="text-sm text-red-600 mt-1">{{ form.errors.subtitle }}</p>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Description</h3>
        <p class="text-muted text-sm">Write a detailed description</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Description (Markdown Supported)</label>
          <textarea v-model="form.description" rows="8" class="input-bangla" placeholder="Write a detailed description..."></textarea>
          <p v-if="form.errors.description" class="text-sm text-red-600 mt-1">{{ form.errors.description }}</p>
        </div>
      </div>

      <!-- Genres -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Genres</h3>
        <p class="text-muted text-sm">Select one or more genres</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Genres (Select multiple)</label>
          <select v-model="form.genres" multiple class="input-bangla min-h-32">
            <option v-for="g in props.genres" :key="g.id" :value="g.id">{{ g.name }}</option>
          </select>
          <p class="text-xs text-muted mt-1">Hold Ctrl (or Cmd) to select multiple</p>
          <p v-if="form.errors.genres" class="text-sm text-red-600 mt-1">{{ form.errors.genres }}</p>
        </div>
      </div>

      <!-- Categories -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Categories</h3>
        <p class="text-muted text-sm">Group your book into categories</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div>
          <label class="label-bangla">Categories (Select multiple)</label>
          <select v-model="form.categories" multiple class="input-bangla min-h-32">
            <option v-for="c in props.categories" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
          <p class="text-xs text-muted mt-1">Hold Ctrl (or Cmd) to select multiple</p>
          <p v-if="form.errors.categories" class="text-sm text-red-600 mt-1">{{ form.errors.categories }}</p>
        </div>
      </div>

      <!-- Status & Cover -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Status & Cover</h3>
        <p class="text-muted text-sm">Publishing status and cover image</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="grid md:grid-cols-2 gap-6">
          <div>
            <label class="label-bangla">Status</label>
            <select v-model="form.status" class="input-bangla">
              <option value="draft">Draft</option>
              <option value="published">Published</option>
            </select>
          </div>
          <div v-if="form.status === 'published'">
            <label class="label-bangla">Published Date</label>
            <input type="date" v-model="form.published_at" class="input-bangla" />
          </div>
        </div>
        <div>
          <label class="label-bangla">Cover Image</label>
          <input type="file" accept="image/*" @change="onCoverChange" class="input-bangla" />
          <p v-if="form.errors.cover" class="text-sm text-red-600 mt-1">{{ form.errors.cover }}</p>
        </div>
      </div>

      <!-- Purchase Links -->
      <div class="card-bangla-header">
        <h3 class="heading-bangla heading-bangla-3">Purchase Links</h3>
        <p class="text-muted text-sm">Where readers can buy this book</p>
      </div>
      <div class="space-y-4 px-6 pb-6">
        <div class="flex items-center justify-between">
          <span class="label-bangla">Links</span>
          <button type="button" @click="addLink" class="btn-bangla btn-bangla-secondary text-sm flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Link
          </button>
        </div>
        <div v-if="form.links.length" class="space-y-4">
          <div v-for="(link, index) in form.links" :key="index" class="card-bangla p-4">
            <div class="grid md:grid-cols-12 gap-4 items-end">
              <div class="md:col-span-3">
                <label class="label-bangla text-sm">Label</label>
                <input v-model="link.label" class="input-bangla" placeholder="e.g. Amazon" />
              </div>
              <div class="md:col-span-5">
                <label class="label-bangla text-sm">URL</label>
                <input v-model="link.url" class="input-bangla" placeholder="https://..." />
              </div>
              <div class="md:col-span-2">
                <label class="label-bangla text-sm">Order</label>
                <input v-model.number="link.sort_order" type="number" class="input-bangla" />
              </div>
              <div class="md:col-span-2 flex items-center gap-4">
                <label class="flex items-center">
                  <input type="checkbox" v-model="link.is_primary" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500 focus:ring-2" />
                  <span class="ml-2 text-sm text-gray-700">Primary</span>
                </label>
                <button type="button" @click="removeLink(index)" class="btn-bangla btn-bangla-ghost text-sm text-red-600 flex items-center gap-1">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-else class="card-bangla p-8 text-center">
          <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
          <p class="text-sm font-medium text-gray-900">No purchase links</p>
          <p class="text-sm text-muted">Get started by adding your first purchase link.</p>
        </div>
        <p v-if="form.errors.links" class="text-sm text-red-600">{{ form.errors.links }}</p>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200 px-6">
        <Link href="/dashboard/books" class="btn-bangla btn-bangla-ghost flex items-center gap-2">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          Cancel
        </Link>
        <button type="submit" :disabled="form.processing" class="btn-bangla btn-bangla-primary flex items-center gap-2">
          <svg v-if="form.processing" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
          <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span v-if="form.processing">Saving...</span>
          <span v-else>Save Changes</span>
        </button>
      </div>
    </form>
  </div>
</template>

<style scoped>
select[multiple] { min-height: 8rem; }
</style>
