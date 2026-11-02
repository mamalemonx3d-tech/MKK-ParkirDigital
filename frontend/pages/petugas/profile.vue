<template>
  <div class="max-w-3xl mx-auto space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Kelola Profil Akun Petugas
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Perbarui informasi identitas pribadi, kontak nomor telepon, dan kata sandi login Anda.
        </p>
      </div>

      <span
        class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider bg-[#001a4e] text-white font-mono"
      >
        {{ auth.user.value?.role === 'super_admin' ? 'Super Admin' : 'Petugas Gerbang' }}
      </span>
    </div>

    <!-- Alert Notifications -->
    <div
      v-if="successMessage"
      class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between animate-in fade-in"
    >
      <div class="flex items-center gap-2.5">
        <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
        <span class="font-bold">{{ successMessage }}</span>
      </div>
      <button @click="successMessage = ''" class="text-emerald-600 hover:text-emerald-800">
        &times;
      </button>
    </div>

    <div
      v-if="errorMessage"
      class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-800 text-xs flex items-center justify-between animate-in fade-in"
    >
      <div class="flex items-center gap-2.5">
        <AlertTriangle class="w-4 h-4 text-red-600 shrink-0" />
        <span class="font-bold">{{ errorMessage }}</span>
      </div>
      <button @click="errorMessage = ''" class="text-red-600 hover:text-red-800">
        &times;
      </button>
    </div>

    <!-- Main Profile Card -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 space-y-6">
      <form @submit.prevent="saveProfile" class="space-y-6">
        <!-- Section 1: Data Diri -->
        <div class="space-y-4">
          <div class="border-b border-slate-100 pb-2">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
              <User class="w-4 h-4 text-[#0099ff]" />
              <span>Informasi Pengguna</span>
            </h3>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                Nama Lengkap Petugas <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                placeholder="Nama Anda"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-800 focus:outline-none focus:border-[#0099ff] focus:ring-1 focus:ring-[#0099ff]"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                Alamat Email <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.email"
                type="email"
                required
                placeholder="nama@email.com"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-800 focus:outline-none focus:border-[#0099ff] focus:ring-1 focus:ring-[#0099ff]"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                Nomor WhatsApp / HP
              </label>
              <input
                v-model="form.no_hp"
                type="text"
                placeholder="08123456789"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-800 focus:outline-none focus:border-[#0099ff] focus:ring-1 focus:ring-[#0099ff]"
              />
              <p class="text-[10px] text-slate-400 mt-0.5">Digunakan untuk pemulihan akun via OTP WhatsApp.</p>
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                Peran Akun
              </label>
              <input
                :value="auth.user.value?.role?.toUpperCase()"
                disabled
                class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-slate-100 text-xs font-mono font-bold text-slate-500 cursor-not-allowed"
              />
            </div>
          </div>
        </div>

        <!-- Section 2: Ganti Password -->
        <div class="space-y-4 pt-4 border-t border-slate-100">
          <div class="border-b border-slate-100 pb-2">
            <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
              <KeyRound class="w-4 h-4 text-[#ff9900]" />
              <span>Ganti Kata Sandi (Opsional)</span>
            </h3>
            <p class="text-[11px] text-slate-400">Kosongkan jika Anda tidak ingin mengubah password saat ini.</p>
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                Password Saat Ini
              </label>
              <input
                v-model="form.current_password"
                type="password"
                placeholder="Password lama"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-800 focus:outline-none focus:border-[#0099ff] focus:ring-1 focus:ring-[#0099ff]"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                Password Baru
              </label>
              <input
                v-model="form.password"
                type="password"
                placeholder="Minimal 6 karakter"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-800 focus:outline-none focus:border-[#0099ff] focus:ring-1 focus:ring-[#0099ff]"
              />
            </div>

            <div>
              <label class="block text-xs font-bold text-slate-700 mb-1">
                Konfirmasi Password Baru
              </label>
              <input
                v-model="form.password_confirmation"
                type="password"
                placeholder="Ulangi password baru"
                class="w-full px-3 py-2 rounded-lg border border-slate-300 text-xs text-slate-800 focus:outline-none focus:border-[#0099ff] focus:ring-1 focus:ring-[#0099ff]"
              />
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
          <button
            type="submit"
            :disabled="isSubmitting"
            class="px-5 py-2.5 rounded-lg bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs disabled:opacity-50"
          >
            <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
            <Save v-else class="w-4 h-4" />
            <span>{{ isSubmitting ? 'Menyimpan Perubahan...' : 'Simpan Pembaruan Profil' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import {
  User,
  KeyRound,
  Save,
  Loader2,
  CheckCircle2,
  AlertTriangle,
} from 'lucide-vue-next'

const auth = useAuth()
const api = useApi()

// Decide layout dynamically based on role
definePageMeta({
  middleware: 'auth',
})

const isSubmitting = ref(false)
const successMessage = ref('')
const errorMessage = ref('')

const form = ref({
  name: '',
  email: '',
  no_hp: '',
  current_password: '',
  password: '',
  password_confirmation: '',
})

onMounted(async () => {
  if (auth.user.value) {
    form.value.name = auth.user.value.name || ''
    form.value.email = auth.user.value.email || ''
    form.value.no_hp = auth.user.value.no_hp || ''
  } else {
    try {
      const res = await api.get('/me')
      if (res.data?.data) {
        auth.user.value = res.data.data
        form.value.name = res.data.data.name || ''
        form.value.email = res.data.data.email || ''
        form.value.no_hp = res.data.data.no_hp || ''
      }
    } catch (e) {
      console.error('Failed to load user', e)
    }
  }
})

const saveProfile = async () => {
  isSubmitting.value = true
  successMessage.value = ''
  errorMessage.value = ''

  if (form.value.password && form.value.password !== form.value.password_confirmation) {
    errorMessage.value = 'Konfirmasi password baru tidak cocok.'
    isSubmitting.value = false
    return
  }

  try {
    const payload: any = {
      name: form.value.name,
      email: form.value.email,
      no_hp: form.value.no_hp,
    }

    if (form.value.password) {
      payload.current_password = form.value.current_password
      payload.password = form.value.password
      payload.password_confirmation = form.value.password_confirmation
    }

    const res = await api.put('/profile', payload)

    if (res.data?.success) {
      successMessage.value = 'Profil Anda berhasil diperbarui.'
      if (res.data?.data) {
        auth.user.value = res.data.data
        if (process.client) {
          localStorage.setItem('user', JSON.stringify(res.data.data))
        }
      }
      form.value.current_password = ''
      form.value.password = ''
      form.value.password_confirmation = ''
    }
  } catch (err: any) {
    const msg =
      err.response?.data?.errors?.email?.[0] ||
      err.response?.data?.errors?.current_password?.[0] ||
      err.response?.data?.errors?.password?.[0] ||
      err.response?.data?.message ||
      'Gagal memperbarui profil.'
    errorMessage.value = msg
  } finally {
    isSubmitting.value = false
  }
}
</script>
