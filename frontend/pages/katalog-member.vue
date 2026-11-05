<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-between text-slate-800">
    <!-- Top Nav Header -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <NuxtLink to="/" class="flex items-center gap-3">
          <BrandLogo :showSubtitle="true" subtitle="Sistem Parkir Digital" size="sm" />
        </NuxtLink>

        <div class="flex items-center gap-3">
          <NuxtLink
            to="/user"
            class="px-3.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-1.5"
          >
            <Ticket class="w-3.5 h-3.5" />
            <span>Kiosk Tiket</span>
          </NuxtLink>
          <NuxtLink
            to="/login"
            class="px-3.5 py-1.5 rounded-lg bg-[#0066ff] hover:bg-[#0052cc] text-white text-xs font-bold transition"
          >
            Portal Petugas
          </NuxtLink>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto w-full px-4 sm:px-6 py-8 sm:py-10 space-y-10 flex-1">
      <!-- Title Section -->
      <div class="text-center max-w-2xl mx-auto space-y-2">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#0066ff] tracking-tight">
          Status & Katalog Keanggotaan Member
        </h1>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
          Pemeriksaan status masa aktif kartu member parkir dan rincian tarif paket berlangganan resmi.
        </p>
      </div>

      <!-- Member Card Status Checker Box -->
      <div class="bg-white rounded-xl border border-slate-200 shadow-xs p-6 sm:p-8 max-w-3xl mx-auto space-y-6">
        <div class="border-b border-slate-100 pb-3">
          <h1 class="text-sm font-normal  flex items-center gap-2 text-slate-900 font-serif tracking-tight">
            <Search class="w-4 h-4 text-[#0066ff]" />
            <span>Pemeriksaan Status Kartu Member</span>
          </h1>
          <p class="text-xs text-slate-500 mt-0.5">
            Masukkan kode kartu member (contoh: <span class="font-mono font-bold text-slate-700">MBR-001</span>) atau nama pemegang kartu.
          </p>
        </div>

        <!-- Search Input -->
        <form @submit.prevent="checkMemberStatus" class="flex flex-col sm:flex-row gap-2.5">
          <div class="relative flex-1">
            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Masukkan kode member (MBR-001) atau nama..."
              required
              class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-slate-300 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0066ff] focus:ring-1 focus:ring-[#0066ff] bg-white transition"
            />
          </div>
          <button
            type="submit"
            :disabled="isLoading"
            class="px-5 py-2.5 rounded-lg bg-[#0066ff] hover:bg-[#0052cc] text-white text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-50"
          >
            <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
            <Search v-else class="w-4 h-4" />
            <span>{{ isLoading ? 'Memeriksa...' : 'Cek Status' }}</span>
          </button>
        </form>

        <!-- Search Result Alert -->
        <div v-if="searchError" class="p-3.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs flex items-start gap-2.5">
          <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
          <div>
            <p class="font-bold">Kartu Member Tidak Ditemukan</p>
            <p class="mt-0.5 text-red-600">{{ searchError }}</p>
          </div>
        </div>

        <!-- Member Card Result Display -->
        <div
          v-if="memberData"
          class="rounded-xl border overflow-hidden transition-all duration-200"
          :class="memberData.is_expired ? 'border-red-300 bg-red-50/20' : 'border-emerald-300 bg-emerald-50/20'"
        >
          <!-- Top Card Status Header -->
          <div
            class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs border-b"
            :class="memberData.is_expired ? 'bg-red-50 border-red-200 text-red-800' : 'bg-emerald-50 border-emerald-200 text-emerald-900'"
          >
            <div class="flex items-center gap-2.5">
              <span
                class="w-2 h-2 rounded-full"
                :class="memberData.is_expired ? 'bg-red-500' : 'bg-emerald-500 animate-pulse'"
              ></span>
              <span class="font-mono font-bold text-sm uppercase">
                KODE KARTU: {{ memberData.kode }}
              </span>
            </div>

            <div class="flex items-center gap-2">
              <span
                class="px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px] tracking-wider"
                :class="memberData.is_expired ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white'"
              >
                {{ memberData.is_expired ? 'Masa Aktif Habis' : 'Member Aktif' }}
              </span>
              <span
                class="px-2.5 py-0.5 rounded-full font-bold uppercase text-[10px] tracking-wider bg-white text-slate-700 border border-slate-200"
              >
                {{ memberData.tipe_kendaraan }}
              </span>
            </div>
          </div>

          <!-- Card Content Body -->
          <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-5 bg-white text-xs">
            <div class="space-y-3">
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nama Pemegang</span>
                <p class="text-sm font-bold text-slate-900">{{ memberData.nama }}</p>
              </div>

              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Instansi / Kantor</span>
                <p class="font-medium text-slate-700">{{ memberData.perusahaan || '-' }}</p>
              </div>

              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Batas Masa Berlaku</span>
                <p class="font-mono font-bold" :class="memberData.is_expired ? 'text-red-600' : 'text-emerald-700'">
                  {{ memberData.expired_at }}
                  <span v-if="memberData.is_expired" class="text-[10px] font-normal text-red-500 block">Silakan lakukan perpanjangan di loket parkir.</span>
                </p>
              </div>
            </div>

            <div class="space-y-3 border-t md:border-t-0 md:border-l border-slate-100 md:pl-5 pt-3 md:pt-0">
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Status Iuran</span>
                <span
                  class="inline-block mt-1 px-2.5 py-0.5 rounded text-[10px] font-bold uppercase"
                  :class="memberData.status_iuran === 'lunas' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                >
                  {{ memberData.status_iuran === 'lunas' ? 'LUNAS' : 'MENUNGGU PEMBAYARAN' }}
                </span>
              </div>

              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Posisi Parkir Saat Ini</span>
                <div class="mt-1 flex items-center gap-1.5">
                  <span
                    class="w-2 h-2 rounded-full"
                    :class="memberData.sedang_parkir ? 'bg-[#0066ff]' : 'bg-slate-400'"
                  ></span>
                  <p class="font-bold" :class="memberData.sedang_parkir ? 'text-[#0066ff]' : 'text-slate-600'">
                    {{ memberData.sedang_parkir ? 'Di Dalam Area Parkir' : 'Di Luar Area Parkir' }}
                  </p>
                </div>
                <p v-if="memberData.sedang_parkir && memberData.waktu_masuk" class="text-[10px] text-slate-400 font-mono mt-0.5">
                  Masuk sejak: {{ memberData.waktu_masuk }}
                </p>
              </div>

              <div class="pt-1">
                <p class="text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                  <strong class="text-slate-700">Petunjuk:</strong> Tunjukkan kode QR pada kartu Anda ke kamera scanner di gerbang masuk atau keluar.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Membership Pricing Catalog Section -->
      <div class="space-y-6 pt-4">
        <div class="text-center max-w-xl mx-auto space-y-1">
          <h1 class="text-xl font-normal text-slate-900 font-serif tracking-tight">
            Katalog Paket Berlangganan Parkir
          </h1>
          <p class="text-xs text-slate-500">
            Daftar paket berlangganan parkir resmi untuk pengendara motor dan mobil.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
          <!-- Paket 1: Motor -->
          <div class="bg-white rounded-xl border border-slate-200 p-6 flex flex-col justify-between shadow-xs">
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#0066ff] bg-slate-100 px-3 py-1 rounded-full">
                  RODA DUA (MOTOR)
                </span>
                <Bike class="w-5 h-5 text-slate-600" />
              </div>

              <div>
                <h3 class="text-lg font-bold text-slate-900">Member Bulanan Sepeda Motor</h3>
                <p class="text-xs text-slate-500 mt-0.5">Untuk komuter dan staf harian.</p>
              </div>

              <div class="flex items-baseline gap-1 py-2 border-y border-slate-100">
                <span class="text-2xl font-black font-mono text-[#0066ff]">Rp 50.000</span>
                <span class="text-xs font-medium text-slate-400">/ bulan</span>
              </div>

              <ul class="space-y-2 text-xs text-slate-600">
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Akses keluar-masuk menggunakan kartu QR resmi</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Bebas tarif per jam (Rp 0 di kasir keluar)</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Fasilitas pencetakan kartu fisik member</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Proteksi keamanan sesi parkir aktif</span>
                </li>
              </ul>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
              <NuxtLink
                to="/user"
                class="w-full py-2 px-4 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center justify-center gap-1.5"
              >
                <span>Pendaftaran di Loket Parkir</span>
                <ArrowRight class="w-3.5 h-3.5" />
              </NuxtLink>
            </div>
          </div>

          <!-- Paket 2: Mobil -->
          <div class="bg-white rounded-xl border border-slate-200 p-6 flex flex-col justify-between shadow-xs">
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#0066ff] bg-slate-100 px-3 py-1 rounded-full">
                  RODA EMPAT (MOBIL)
                </span>
                <Car class="w-5 h-5 text-slate-600" />
              </div>

              <div>
                <h3 class="text-lg font-bold text-slate-900">Member Bulanan Mobil</h3>
                <p class="text-xs text-slate-500 mt-0.5">Untuk kendaraan dinas, tenant gedung, dan operasional.</p>
              </div>

              <div class="flex items-baseline gap-1 py-2 border-y border-slate-100">
                <span class="text-2xl font-black font-mono text-[#0066ff]">Rp 200.000</span>
                <span class="text-xs font-medium text-slate-400">/ bulan</span>
              </div>

              <ul class="space-y-2 text-xs text-slate-600">
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Akses keluar-masuk menggunakan kartu QR resmi</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Bebas tarif per jam (Rp 0 di kasir keluar)</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Fasilitas pencetakan kartu fisik & invoice resmi</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Proteksi keamanan sesi parkir aktif</span>
                </li>
              </ul>
            </div>

            <div class="mt-6 pt-4 border-t border-slate-100">
              <NuxtLink
                to="/user"
                class="w-full py-2 px-4 rounded-lg bg-[#0066ff] hover:bg-[#0052cc] text-white text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs"
              >
                <span>Pendaftaran di Loket Parkir</span>
                <ArrowRight class="w-3.5 h-3.5" />
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Simple Footer -->
    <footer class="bg-white border-t border-slate-200 py-5 text-center text-xs text-slate-500">
      <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p>&copy; 2026 Secure Parking - Sistem Manajemen Parkir Digital.</p>
        <div class="flex items-center gap-4 text-slate-400 font-medium">
          <NuxtLink to="/user" class="hover:text-slate-600">Kiosk Tiket</NuxtLink>
          <span>&bull;</span>
          <NuxtLink to="/petugas/keluar" class="hover:text-slate-600">Pos Kasir</NuxtLink>
          <span>&bull;</span>
          <NuxtLink to="/login" class="hover:text-slate-600">Login Staf</NuxtLink>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import {
  Ticket,
  Search,
  Loader2,
  AlertCircle,
  CheckCircle2,
  Bike,
  Car,
  ArrowRight,
} from 'lucide-vue-next'
import BrandLogo from '~/components/BrandLogo.vue'

const api = useApi()
const searchQuery = ref('')
const isLoading = ref(false)
const searchError = ref('')
const memberData = ref<any>(null)

const checkMemberStatus = async () => {
  if (!searchQuery.value.trim()) return

  isLoading.value = true
  searchError.value = ''
  memberData.value = null

  try {
    const res = await api.post('/member/check-status', {
      query: searchQuery.value.trim(),
    })
    memberData.value = res.data
  } catch (err: any) {
    searchError.value =
      err.response?.data?.message ||
      'Member tidak ditemukan. Periksa kembali kode member Anda.'
  } finally {
    isLoading.value = false
  }
}
</script>
