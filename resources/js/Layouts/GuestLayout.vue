<script setup>
import { Link } from '@inertiajs/vue3'
</script>

<template>
  <div class="min-h-screen bg-gray-100 d-flex align-items-center">
    <div class="container" style="max-width: 480px;">
      <div class="text-center mb-4">
        <!-- আগে যদি route('home') থাকতো, এখন শুধু / -->
        <Link href="/" class="text-decoration-none h4 m-0 d-block">কাইজার চৌধুরী সাহিত্য</Link>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <slot />
        </div>
      </div>

      <div class="text-center mt-3">
        <Link href="/login" class="text-muted small me-2">Login</Link>
        <span class="text-muted small">·</span>
        <Link href="/register" class="text-muted small ms-2">Register</Link>
      </div>
    </div>
  </div>
</template>
