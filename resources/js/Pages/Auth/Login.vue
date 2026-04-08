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

    <div v-if="status" class="mb-4 text-sm font-medium text-green-600">
      {{ status }}
    </div>

    <form @submit.prevent="submit" class="space-y-6">
      <div>
        <InputLabel for="email" value="Email" />
        <TextInput
          id="email"
          type="email"
          class="mt-1 block w-full"
          v-model="form.email"
          required
          inputmode="email"
          autocomplete="username"
          autocapitalize="none"
          spellcheck="false"
          :aria-invalid="!!form.errors.email"
          :aria-describedby="form.errors.email ? 'email-error' : undefined"
        />
        <InputError id="email-error" class="mt-2" :message="form.errors.email" />
      </div>

      <div>
        <InputLabel for="password" value="Password" />
        <TextInput
          id="password"
          type="password"
          class="mt-1 block w-full"
          v-model="form.password"
          required
          autocomplete="current-password"
          :aria-invalid="!!form.errors.password"
          :aria-describedby="form.errors.password ? 'password-error' : undefined"
        />
        <InputError id="password-error" class="mt-2" :message="form.errors.password" />
      </div>

      <div class="flex items-center justify-between">
        <label class="flex items-center gap-2">
          <Checkbox name="remember" v-model:checked="form.remember" />
          <span class="text-sm text-gray-700">Remember me</span>
        </label>

        <Link
          v-if="canResetPassword"
          href="/forgot-password"
          class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          Forgot your password?
        </Link>
      </div>

      <div class="flex items-center justify-end">
        <PrimaryButton
          class="ms-4"
          :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
          :disabled="form.processing"
          :aria-busy="form.processing"
        >
          Log in
        </PrimaryButton>
      </div>
    </form>
  </GuestLayout>
</template>
