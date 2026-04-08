<script>
import AppLayout from '../Layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'

export default {
  layout: AppLayout,
  components: { Head, Link },
}
</script>

<template>
  <Head title="Dashboard" />

  <div class="my-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <h1 class="h4 fw-bold m-0">Dashboard</h1>

      <!-- নতুন পোস্ট তৈরি (Step–Create থেকে) -->
      <Link href="/dashboard/posts/create" class="btn btn-primary btn-sm">
        + New Post
      </Link>
    </div>

    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <p class="mb-0">You're logged in!</p>
      </div>
    </div>
  </div>
</template>
