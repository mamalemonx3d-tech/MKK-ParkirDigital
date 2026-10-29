<template>
  <div class="flex-1 flex items-center justify-center p-4 sm:p-6">
    <div class="max-w-md w-full bg-white rounded-2xl border border-slate-200 shadow-sm p-6 sm:p-8">
      <div class="text-center mb-6">
        <div class="inline-flex justify-center mb-2">
          <BrandLogo :showSubtitle="false" />
        </div>
        <h2 class="text-xl font-bold text-[#001a4e]">
          Pemulihan Kata Sandi
        </h2>
        <p class="text-xs text-slate-500 mt-1">
          Langkah {{ step }} dari 3: {{ stepTitles[step - 1] }}
        </p>
      </div>

      <!-- Alert Error -->
      <div v-if="errorMessage" class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-xs text-red-700 flex items-center gap-2">
        <AlertCircle class="w-4 h-4 shrink-0 text-red-500" />
        <span>{{ errorMessage }}</span>
      </div>

      <!-- Alert Success -->
      <div v-if="successMessage" class="mb-4 p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center gap-2">
        <CheckCircle2 class="w-4 h-4 shrink-0 text-emerald-600" />
        <span>{{ successMessage }}</span>
      </div>

      <!-- Dev OTP helper if available -->
      <div v-if="devOtp" class="mb-4 p-3 rounded-lg bg-amber-50 border border-amber-300 text-xs text-amber-900">
        <span class="font-bold">Mode Pengujian:</span> Kode OTP Anda adalah:
        <span class="font-mono font-black text-sm ml-1">{{ devOtp }}</span>
      </div>

      <!-- Step 1: Input Email -->
      <form v-if="step === 1" @submit.prevent="submitEmail" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">
            Email Akun Terdaftar
          </label>
          <input
            v-model="email"
            type="email"
            required
            placeholder="petugas@parkir.com"
            class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] focus:ring-2 focus:ring-[#0099ff]/20 outline-none transition"
          />
          <p class="text-[11px] text-slate-400 mt-1">
            Kode verifikasi 6-digit akan dikirimkan ke nomor WhatsApp yang terdaftar pada akun Anda.
          </p>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-2.5 px-4 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-70"
        >
          <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
          <span>{{ isLoading ? 'Memeriksa Email...' : 'Kirim Kode OTP' }}</span>
        </button>
      </form>

      <!-- Step 2: Input OTP -->
      <form v-else-if="step === 2" @submit.prevent="submitOtp" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">
            Masukkan 6-Digit Kode OTP
          </label>
          <input
            v-model="otp"
            type="text"
            maxlength="6"
            required
            placeholder="123456"
            class="w-full px-3.5 py-2.5 text-center text-lg font-mono font-bold tracking-widest rounded-lg border border-slate-300 focus:border-[#0099ff] focus:ring-2 focus:ring-[#0099ff]/20 outline-none transition"
          />
          <p class="text-[11px] text-slate-400 mt-1 text-center">
            Masa berlaku kode adalah 5 menit.
          </p>
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-2.5 px-4 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-70"
        >
          <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
          <span>{{ isLoading ? 'Memverifikasi...' : 'Verifikasi OTP' }}</span>
        </button>
      </form>

      <!-- Step 3: New Password -->
      <form v-else-if="step === 3" @submit.prevent="submitNewPassword" class="space-y-4">
        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">
            Kata Sandi Baru (Minimal 8 Karakter)
          </label>
          <input
            v-model="newPassword"
            type="password"
            minlength="8"
            required
            placeholder="••••••••"
            class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] focus:ring-2 focus:ring-[#0099ff]/20 outline-none transition"
          />
        </div>

        <div>
          <label class="block text-xs font-semibold text-slate-700 mb-1.5">
            Ulangi Kata Sandi Baru
          </label>
          <input
            v-model="confirmPassword"
            type="password"
            minlength="8"
            required
            placeholder="••••••••"
            class="w-full px-3.5 py-2.5 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] focus:ring-2 focus:ring-[#0099ff]/20 outline-none transition"
          />
        </div>

        <button
          type="submit"
          :disabled="isLoading"
          class="w-full py-2.5 px-4 bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-70"
        >
          <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
          <span>{{ isLoading ? 'Menyimpan...' : 'Simpan Kata Sandi Baru' }}</span>
        </button>
      </form>

      <!-- Back Link -->
      <div class="mt-6 pt-4 border-t border-slate-100 text-center">
        <NuxtLink to="/login" class="text-xs text-slate-600 hover:text-[#001a4e] font-semibold">
          &larr; Kembali ke Halaman Login
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { AlertCircle, CheckCircle2, Loader2 } from 'lucide-vue-next'
import BrandLogo from '~/components/BrandLogo.vue'

const api = useApi()

const step = ref(1)
const stepTitles = ['Verifikasi Email', 'Masukkan Kode OTP', 'Kata Sandi Baru']

const email = ref('')
const otp = ref('')
const resetToken = ref('')
const newPassword = ref('')
const confirmPassword = ref('')

const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const devOtp = ref('')

const submitEmail = async () => {
  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await api.post('/forgot-password/check-email', { email: email.value })
    if (res.data?.success) {
      step.value = 2
      successMessage.value = res.data.message
      if (res.data.dev_otp) {
        devOtp.value = res.data.dev_otp
      }
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Email tidak ditemukan.'
  } finally {
    isLoading.value = false
  }
}

const submitOtp = async () => {
  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await api.post('/forgot-password/verify-otp', {
      email: email.value,
      otp: otp.value,
    })
    if (res.data?.success) {
      resetToken.value = res.data.reset_token
      step.value = 3
      successMessage.value = res.data.message
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Kode OTP salah atau kadaluarsa.'
  } finally {
    isLoading.value = false
  }
}

const submitNewPassword = async () => {
  if (newPassword.value !== confirmPassword.value) {
    errorMessage.value = 'Konfirmasi kata sandi tidak cocok.'
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const res = await api.post('/forgot-password/reset', {
      email: email.value,
      reset_token: resetToken.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    })

    if (res.data?.success) {
      successMessage.value = 'Kata sandi berhasil diperbarui. Mengalihkan ke login...'
      setTimeout(() => {
        navigateTo('/login')
      }, 1500)
    }
  } catch (err: any) {
    errorMessage.value = err.response?.data?.message || 'Gagal mereset kata sandi.'
  } finally {
    isLoading.value = false
  }
}
</script>
