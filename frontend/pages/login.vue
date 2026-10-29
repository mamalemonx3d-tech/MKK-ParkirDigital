<template>
  <div class="flex-1 flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
      <!-- Brand & Header -->
      <div class="text-center mb-6">
        <div class="inline-flex justify-center mb-2">
          <BrandLogo :showSubtitle="false" />
        </div>
        <h2 class="text-xl font-bold text-[#001a4e]">
          Masuk Portal Petugas & Admin
        </h2>
        <p class="text-xs text-slate-500 mt-1">
          Gunakan akun yang telah terdaftar untuk mengakses sistem.
        </p>
      </div>

      <!-- Error Alert -->
      <div v-if="errorMessage" class="mb-5 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
        <AlertCircle class="w-4 h-4 shrink-0 text-red-500" />
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Login Form -->
      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">
            Alamat Email
          </label>
          <div class="relative">
            <Mail class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="nama@parkir.com"
              class="w-full pl-9 pr-3.5 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] focus:ring-2 focus:ring-[#0099ff]/20 outline-none transition"
            />
          </div>
        </div>

        <div>
          <div class="flex items-center justify-between mb-1.5">
            <label class="block text-xs font-semibold text-slate-700">
              Kata Sandi
            </label>
            <NuxtLink to="/forgot-password" class="text-[11px] text-[#0099ff] hover:underline font-medium">
              Lupa sandi?
            </NuxtLink>
          </div>
          <div class="relative">
            <Lock class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <input
              v-model="form.password"
              :type="showPassword ? 'text' : 'password'"
              required
              placeholder="••••••••"
              class="w-full pl-9 pr-10 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] focus:ring-2 focus:ring-[#0099ff]/20 outline-none transition"
            />
            <button
              type="button"
              @click="showPassword = !showPassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600"
            >
              <Eye v-if="!showPassword" class="w-4 h-4" />
              <EyeOff v-else class="w-4 h-4" />
            </button>
          </div>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full mt-2 py-2.5 px-4 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-70"
        >
          <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
          <span>{{ isLoading ? 'Memverifikasi...' : 'Masuk ke Sistem' }}</span>
        </button>
      </form>

      <!-- Demo Accounts Helper -->
      <div class="mt-8 pt-6 border-t border-slate-100">
        <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-2 text-center">
          Akun Bawaan Sistem (Demo)
        </span>
        <div class="grid grid-cols-2 gap-2 text-[11px]">
          <button
            type="button"
            @click="fillCredentials('admin@parkir.com', 'password')"
            class="p-2 rounded bg-slate-50 border border-slate-200 text-left hover:border-[#0099ff] transition cursor-pointer"
          >
            <span class="font-bold text-slate-700 block">Super Admin</span>
            <span class="text-slate-500 font-mono text-[10px]">admin@parkir.com</span>
          </button>
          <button
            type="button"
            @click="fillCredentials('petugas@parkir.com', 'password')"
            class="p-2 rounded bg-slate-50 border border-slate-200 text-left hover:border-[#0099ff] transition cursor-pointer"
          >
            <span class="font-bold text-slate-700 block">Petugas Gerbang</span>
            <span class="text-slate-500 font-mono text-[10px]">petugas@parkir.com</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Mail, Lock, Eye, EyeOff, AlertCircle, Loader2 } from 'lucide-vue-next'
import BrandLogo from '~/components/BrandLogo.vue'

const auth = useAuth()
const api = useApi()

const form = reactive({
  email: '',
  password: '',
})

const showPassword = ref(false)
const isLoading = ref(false)
const errorMessage = ref('')

const fillCredentials = (email: string, pass: string) => {
  form.email = email
  form.password = pass
}

const handleLogin = async () => {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const res = await api.post('/login', {
      email: form.email,
      password: form.password,
    })

    if (res.data?.success) {
      const { token, user } = res.data.data
      auth.setAuth(token, user)

      if (user.role === 'super_admin') {
        navigateTo('/admin/dashboard')
      } else {
        navigateTo('/petugas')
      }
    } else {
      errorMessage.value = res.data?.message || 'Login gagal.'
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || err.response?.data?.errors?.email?.[0] || 'Kombinasi email dan kata sandi tidak cocok.'
  } finally {
    isLoading.value = false
  }
}
</script>
