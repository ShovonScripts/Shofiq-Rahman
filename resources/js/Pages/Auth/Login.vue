<script setup>
import Checkbox from '@/Components/Checkbox.vue'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import InputError from '@/Components/InputError.vue'
import InputLabel from '@/Components/InputLabel.vue'
import PrimaryButton from '@/Components/PrimaryButton.vue'
import TextInput from '@/Components/TextInput.vue'
import { Head, Link, useForm } from '@inertiajs/vue3'

defineProps({
  canResetPassword: { type: Boolean, default: true },
  status: { type: String, default: '' },
})

const form = useForm({
  email: '',
  password: '',
  remember: false,
})

const submit = () => {
  form.post('/login', {
    onFinish: () => form.reset('password'),
  })
}
</script>

<template>
  <GuestLayout>
    <Head title="Log in" />

    <div class="text-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Welcome back</h2>
      <p class="text-sm text-gray-500 mt-1">Sign in to your account to continue</p>
    </div>

    <div v-if="status" class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm font-medium text-green-700 border border-green-200">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-5">
      <div>
        <InputLabel for="email" value="Email" />
        <TextInput
          id="email"
          type="email"
          class="mt-1.5"
          v-model="form.email"
          required
          inputmode="email"
          autocomplete="username"
          autocapitalize="none"
          spellcheck="false"
          :aria-invalid="!!form.errors.email"
          :aria-describedby="form.errors.email ? 'email-error' : undefined"
        />
        <InputError id="email-error" class="mt-1.5" :message="form.errors.email" />
      </div>

      <div>
        <div class="flex items-center justify-between">
          <InputLabel for="password" value="Password" />
          <Link
            v-if="canResetPassword"
            href="/forgot-password"
            class="text-xs text-blue-600 hover:text-blue-800 font-medium transition-colors"
          >
            Forgot password?
          </Link>
        </div>
        <TextInput
          id="password"
          type="password"
          class="mt-1.5"
          v-model="form.password"
          required
          autocomplete="current-password"
          :aria-invalid="!!form.errors.password"
          :aria-describedby="form.errors.password ? 'password-error' : undefined"
        />
        <InputError id="password-error" class="mt-1.5" :message="form.errors.password" />
      </div>

      <label class="flex items-center gap-2 cursor-pointer select-none">
        <Checkbox name="remember" v-model:checked="form.remember" />
        <span class="text-sm text-gray-600">Remember me</span>
      </label>

      <PrimaryButton
        class="w-full"
        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
        :disabled="form.processing"
        :aria-busy="form.processing"
      >
        <svg v-if="form.processing" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
        </svg>
        <span v-if="form.processing">Signing in...</span>
        <span v-else>Sign in</span>
      </PrimaryButton>

      <p class="text-center text-sm text-gray-500">
        Don't have an account?
        <Link href="/register" class="text-blue-600 hover:text-blue-800 font-medium transition-colors">Create one</Link>
      </p>
    </form>
  </GuestLayout>
</template>
