<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3'
import AdminLayout from '@/Layouts/AdminLayout.vue'

// ✅ Attach Admin layout
defineOptions({ layout: AdminLayout })

// Safer props with defaults
const props = defineProps({
  stats: { type: Object, default: () => ({ posts:0, books:0, users:0, comments:0, messages:0, pending_posts:0 }) },
  recent:{ type: Object, default: () => ({ posts:[], books:[], comments:[], messages:[] }) },
  homepage: { type: Object, default: () => ({ banners_total:0, banners_active:0, author:{ name:'', has_photo:false } }) },
  roleFlags: { type: Object, default: () => ({ is_admin:false, can_manage_users:false }) },
})

const page = usePage()
const user  = page.props.value?.auth?.user ?? null

const fmt = (d) => {
  try {
    return new Date(d).toLocaleString()
  } catch { return '' }
}
</script>

<template>
  <Head title="Dashboard" />

  <!-- Main wrapper comes from AdminLayout, we just fill the slot -->
  <div class="space-y-6">

    <!-- Welcome + quick top CTA (Homepage editor) -->
    <div class="flex flex-wrap items-center justify-between">
      <div>
        <h2 class="text-xl font-semibold text-gray-900">Dashboard</h2>
        <p class="text-sm text-gray-500">Welcome back, {{ user?.name || 'Admin' }}</p>
      </div>
      <div class="flex gap-2">
        <Link :href="route('dashboard.homepage.edit')" class="btn btn-primary inline-flex items-center px-4 py-2 rounded-lg">
          <i class="bi bi-grid mr-2"></i> Manage Homepage
        </Link>
        <Link :href="route('home')" class="btn inline-flex items-center px-4 py-2 rounded-lg border border-gray-300 bg-white hover:bg-gray-50">
          <i class="bi bi-eye mr-2"></i> View Site
        </Link>
      </div>
    </div>

    <!-- KPI cards -->
    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3">
      <div
        v-for="card in [
          {label:'Posts',     value: props.stats?.posts ?? 0,     icon:'bi-journal-text',  link: route('dashboard.posts.index')},
          {label:'Books',     value: props.stats?.books ?? 0,     icon:'bi-book',           link: route('dashboard.books.index')},
          {label:'Users',     value: props.stats?.users ?? 0,     icon:'bi-people',         link: props.roleFlags?.can_manage_users ? route('dashboard.users.index') : null},
          {label:'Comments',  value: props.stats?.comments ?? 0,  icon:'bi-chat-left-dots', link: route('dashboard.comments.index')},
          {label:'Messages',  value: props.stats?.messages ?? 0,  icon:'bi-inbox',          link: route('dashboard.contacts.index')},
          {label:'Pending',   value: props.stats?.pending_posts ?? 0, icon:'bi-hourglass-split', link: route('dashboard.posts.index', { status:'pending' })}
        ]"
        :key="card.label"
        class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm"
      >
        <div class="flex items-center justify-between">
          <div>
            <div class="text-xs text-gray-500">{{ card.label }}</div>
            <div class="text-2xl font-semibold">{{ card.value }}</div>
          </div>
          <i class="bi text-2xl text-gray-400" :class="card.icon"></i>
        </div>
        <div class="mt-3">
          <template v-if="card.link">
            <Link :href="card.link" class="text-sm text-blue-600 hover:text-blue-700">Manage</Link>
          </template>
          <template v-else>
            <span class="text-sm text-gray-400">Restricted</span>
          </template>
        </div>
      </div>
    </div>

    <!-- Homepage summary block -->
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
      <div class="flex items-center justify-between">
        <h3 class="font-semibold">Homepage</h3>
        <div class="flex gap-2">
          <Link :href="route('dashboard.homepage.edit')" class="btn btn-primary btn-sm">
            <i class="bi bi-grid mr-1"></i> Manage
          </Link>
          <Link :href="route('dashboard.homepage.edit') + '#banners'" class="btn btn-outline btn-sm">
            <i class="bi bi-images mr-1"></i> Banners
          </Link>
          <Link :href="route('dashboard.homepage.edit') + '#author'" class="btn btn-outline btn-sm">
            <i class="bi bi-person-badge mr-1"></i> Author
          </Link>
        </div>
      </div>

      <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-3">
        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
          <div class="text-xs text-gray-500">Total Banners</div>
          <div class="text-xl font-semibold">{{ homepage?.banners_total ?? 0 }}</div>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
          <div class="text-xs text-gray-500">Active Banners</div>
          <div class="text-xl font-semibold">{{ homepage?.banners_active ?? 0 }}</div>
        </div>
        <div class="p-3 rounded-lg bg-gray-50 border border-gray-200">
          <div class="text-xs text-gray-500">Author</div>
          <div class="font-medium">{{ homepage?.author?.name || '—' }}</div>
          <div class="text-xs text-gray-500">Photo: {{ homepage?.author?.has_photo ? 'Yes' : 'No' }}</div>
        </div>
      </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
      <div class="flex flex-wrap gap-2">
        <Link :href="route('dashboard.posts.create')" class="btn btn-primary">
          <i class="bi bi-plus-circle mr-2"></i> New Post
        </Link>
        <Link :href="route('dashboard.books.create')" class="btn btn-primary">
          <i class="bi bi-plus-circle mr-2"></i> New Book
        </Link>
        <Link :href="route('dashboard.categories.index')" class="btn btn-outline">
          <i class="bi bi-tags mr-2"></i> Categories
        </Link>
        <Link :href="route('dashboard.genres.index')" class="btn btn-outline">
          <i class="bi bi-collection mr-2"></i> Genres
        </Link>
        <Link :href="route('dashboard.settings.edit')" class="btn btn-outline">
          <i class="bi bi-gear mr-2"></i> Settings
        </Link>
        <Link :href="route('dashboard.contacts.index')" class="btn btn-outline">
          <i class="bi bi-inbox mr-2"></i> Inbox
        </Link>
      </div>
    </div>

    <!-- Recents -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Recent Posts -->
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="px-4 py-3 border-b">
          <h3 class="font-semibold">Recent Posts</h3>
        </div>
        <div class="p-4 overflow-x-auto">
          <div v-if="(recent?.posts?.length || 0) === 0" class="text-sm text-gray-500">No posts yet.</div>
          <table v-else class="min-w-full text-sm">
            <thead class="text-gray-500">
              <tr>
                <th class="text-left py-2">Title</th>
                <th class="text-left py-2">Status</th>
                <th class="text-left py-2">Created</th>
                <th class="py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in recent.posts" :key="p.id" class="border-t">
                <td class="py-2 pr-3 max-w-[280px] truncate">{{ p.title }}</td>
                <td class="py-2">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded text-xs',
                      p.status === 'published' ? 'bg-green-100 text-green-700' :
                      p.status === 'pending'   ? 'bg-yellow-100 text-yellow-800' :
                                                 'bg-gray-100 text-gray-700'
                    ]"
                  >
                    {{ p.status }}
                  </span>
                </td>
                <td class="py-2">{{ fmt(p.created_at) }}</td>
                <td class="py-2 text-right">
                  <Link :href="route('dashboard.posts.edit', p.id)" class="text-blue-600 hover:text-blue-700">Edit</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-4 py-3 border-t text-right">
          <Link :href="route('dashboard.posts.index')" class="text-sm text-blue-600 hover:text-blue-700">All Posts</Link>
        </div>
      </div>

      <!-- Recent Books -->
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="px-4 py-3 border-b">
          <h3 class="font-semibold">Recent Books</h3>
        </div>
        <div class="p-4 overflow-x-auto">
          <div v-if="(recent?.books?.length || 0) === 0" class="text-sm text-gray-500">No books yet.</div>
          <table v-else class="min-w-full text-sm">
            <thead class="text-gray-500">
              <tr>
                <th class="text-left py-2">Title</th>
                <th class="text-left py-2">Status</th>
                <th class="text-left py-2">Created</th>
                <th class="py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="b in recent.books" :key="b.id" class="border-t">
                <td class="py-2 pr-3 max-w-[280px] truncate">{{ b.title }}</td>
                <td class="py-2">
                  <span
                    :class="[
                      'px-2 py-0.5 rounded text-xs',
                      b.status === 'published' ? 'bg-green-100 text-green-700' :
                      b.status === 'pending'   ? 'bg-yellow-100 text-yellow-800' :
                                                 'bg-gray-100 text-gray-700'
                    ]"
                  >
                    {{ b.status }}
                  </span>
                </td>
                <td class="py-2">{{ fmt(b.created_at) }}</td>
                <td class="py-2 text-right">
                  <Link :href="route('dashboard.books.edit', b.id)" class="text-blue-600 hover:text-blue-700">Edit</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-4 py-3 border-t text-right">
          <Link :href="route('dashboard.books.index')" class="text-sm text-blue-600 hover:text-blue-700">All Books</Link>
        </div>
      </div>

      <!-- Recent Comments -->
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="px-4 py-3 border-b">
          <h3 class="font-semibold">Recent Comments</h3>
        </div>
        <div class="p-4 overflow-x-auto">
          <div v-if="(recent?.comments?.length || 0) === 0" class="text-sm text-gray-500">No comments yet.</div>
          <table v-else class="min-w-full text-sm">
            <thead class="text-gray-500">
              <tr>
                <th class="text-left py-2">User</th>
                <th class="text-left py-2">On</th>
                <th class="text-left py-2">Time</th>
                <th class="py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="c in recent.comments" :key="c.id" class="border-t">
                <td class="py-2">{{ c.user?.name ?? 'Guest' }}</td>
                <td class="py-2 text-gray-500">{{ c.commentable_type?.split('\\').pop() }} #{{ c.commentable_id }}</td>
                <td class="py-2">{{ fmt(c.created_at) }}</td>
                <td class="py-2 text-right">
                  <Link :href="route('dashboard.comments.index', { q: c.id })" class="text-blue-600 hover:text-blue-700">View</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-4 py-3 border-t text-right">
          <Link :href="route('dashboard.comments.index')" class="text-sm text-blue-600 hover:text-blue-700">All Comments</Link>
        </div>
      </div>

      <!-- Latest Messages -->
      <div class="bg-white border border-gray-200 rounded-xl shadow-sm">
        <div class="px-4 py-3 border-b">
          <h3 class="font-semibold">Latest Messages</h3>
        </div>
        <div class="p-4 overflow-x-auto">
          <div v-if="(recent?.messages?.length || 0) === 0" class="text-sm text-gray-500">No messages yet.</div>
          <table v-else class="min-w-full text-sm">
            <thead class="text-gray-500">
              <tr>
                <th class="text-left py-2">Name</th>
                <th class="text-left py-2">Subject</th>
                <th class="text-left py-2">Time</th>
                <th class="py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="m in recent.messages" :key="m.id" class="border-t">
                <td class="py-2">{{ m.name }}</td>
                <td class="py-2 pr-3 max-w-[280px] truncate">{{ m.subject }}</td>
                <td class="py-2">{{ fmt(m.created_at) }}</td>
                <td class="py-2 text-right">
                  <Link :href="route('dashboard.contacts.show', m.id)" class="text-blue-600 hover:text-blue-700">Open</Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="px-4 py-3 border-t text-right">
          <Link :href="route('dashboard.contacts.index')" class="text-sm text-blue-600 hover:text-blue-700">Inbox</Link>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.btn { @apply inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium; }
.btn-sm { @apply px-2.5 py-1.5 text-xs; }
.btn-primary { @apply bg-blue-600 text-white hover:bg-blue-700; }
.btn-outline { @apply border border-gray-300 text-gray-700 bg-white hover:bg-gray-50; }
</style>
