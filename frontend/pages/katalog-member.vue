<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-between text-slate-800">
    <!-- Top Bar -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-2xs">
      <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
        <NuxtLink to="/" class="flex items-center gap-2">
          <BrandLogo :showSubtitle="false" size="sm" />
        </NuxtLink>

        <div class="flex items-center gap-3">
          <NuxtLink
            to="/user"
            class="text-xs font-bold text-slate-600 hover:text-[#001a4e] flex items-center gap-1.5 transition"
          >
            <Ticket class="w-4 h-4 text-[#ff9900]" />
            <span>Kiosk Tiket Masuk</span>
          </NuxtLink>
          <NuxtLink
            to="/login"
            class="px-3.5 py-1.5 rounded-lg bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold transition"
          >
            Portal Petugas
          </NuxtLink>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto w-full px-4 sm:px-6 py-8 sm:py-12 space-y-12 flex-1">
      <!-- Hero Section -->
      <div class="text-center max-w-2xl mx-auto space-y-3">
        <span class="inline-block text-[11px] font-bold uppercase tracking-widest text-[#0099ff] px-3 py-1 rounded-full bg-[#e6f4ff] border border-[#0099ff]/20">
          Layanan Pengendara Prioritas
        </span>
        <h1 class="text-2xl sm:text-4xl font-black text-[#001a4e] tracking-tight">
          Katalog & Status Keanggotaan Member
        </h1>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
          Nikmati kemudahan akses gerbang tanpa tiket karcis kertas. Cek status kartu langganan Anda secara instan atau pelajari keuntungan menjadi member resmi.
        </p>
      </div>

      <!-- Live Member Card Status Checker Box -->
      <div class="bg-white rounded-2xl border border-slate-200 shadow-md p-6 sm:p-8 max-w-3xl mx-auto space-y-6">
        <div class="border-b border-slate-100 pb-4">
          <h2 class="text-base font-black text-[#001a4e] flex items-center gap-2">
            <Search class="w-5 h-5 text-[#0099ff]" />
            <span>Pemeriksaan Status Kartu Member</span>
          </h2>
          <p class="text-xs text-slate-500 mt-1">
            Ketik nomor kartu member Anda (contoh: <span class="font-mono font-bold text-slate-700">MBR-001</span>) atau nama pemilik kartu.
          </p>
        </div>

        <!-- Search Input -->
        <form @submit.prevent="checkMemberStatus" class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <Search class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2" />
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Masukkan kode member (MBR-001) atau nama..."
              required
              class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-300 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:outline-none focus:border-[#0099ff] focus:ring-2 focus:ring-[#0099ff]/20 bg-slate-50 focus:bg-white transition"
            />
          </div>
          <button
            type="submit"
            :disabled="isLoading"
            class="px-6 py-2.5 rounded-xl bg-[#0099ff] hover:bg-[#0084de] text-white text-xs sm:text-sm font-bold transition flex items-center justify-center gap-2 cursor-pointer shadow-xs disabled:opacity-50"
          >
            <Loader2 v-if="isLoading" class="w-4 h-4 animate-spin" />
            <Search v-else class="w-4 h-4" />
            <span>{{ isLoading ? 'Memeriksa...' : 'Cek Status' }}</span>
          </button>
        </form>

        <!-- Search Result Alert / Card -->
        <div v-if="searchError" class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs flex items-start gap-3">
          <AlertCircle class="w-4 h-4 shrink-0 mt-0.5" />
          <div>
            <p class="font-bold">Kartu Member Tidak Ditemukan</p>
            <p class="mt-0.5 text-red-600">{{ searchError }}</p>
          </div>
        </div>

        <!-- Member Card Result Display -->
        <div
          v-if="memberData"
          class="rounded-2xl border-2 overflow-hidden transition-all duration-300"
          :class="memberData.is_expired ? 'border-red-300 bg-red-50/30' : 'border-emerald-300 bg-emerald-50/20'"
        >
          <!-- Top Card Status Header -->
          <div
            class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs"
            :class="memberData.is_expired ? 'bg-red-100/60 text-red-800' : 'bg-emerald-100/60 text-emerald-900'"
          >
            <div class="flex items-center gap-2.5">
              <span
                class="w-2.5 h-2.5 rounded-full"
                :class="memberData.is_expired ? 'bg-red-500' : 'bg-emerald-500 animate-pulse'"
              ></span>
              <span class="font-mono font-black text-sm uppercase">
                KODE KARTU: {{ memberData.kode }}
              </span>
            </div>

            <div class="flex items-center gap-2">
              <span
                class="px-3 py-1 rounded-full font-black uppercase text-[10px] tracking-wider"
                :class="memberData.is_expired ? 'bg-red-600 text-white' : 'bg-emerald-600 text-white'"
              >
                {{ memberData.is_expired ? 'MASA AKTIF HABIS' : 'MEMBER AKTIF' }}
              </span>
              <span
                class="px-2.5 py-1 rounded-full font-bold uppercase text-[10px] tracking-wider bg-white text-slate-700 border border-slate-200"
              >
                {{ memberData.tipe_kendaraan }}
              </span>
            </div>
          </div>

          <!-- Card Content Body -->
          <div class="p-5 sm:p-6 grid grid-cols-1 md:grid-cols-2 gap-6 bg-white">
            <div class="space-y-3.5">
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Nama Pemegang</span>
                <p class="text-base font-black text-slate-900">{{ memberData.nama }}</p>
              </div>

              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Instansi / Kantor</span>
                <p class="text-xs font-semibold text-slate-700">{{ memberData.perusahaan || '-' }}</p>
              </div>

              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Batas Masa Berlaku</span>
                <p class="text-xs font-mono font-bold" :class="memberData.is_expired ? 'text-red-600' : 'text-emerald-700'">
                  {{ memberData.expired_at }}
                  <span v-if="memberData.is_expired" class="text-[10px] font-normal text-red-500 block">Segera lakukan perpanjangan di loket kasir.</span>
                </p>
              </div>
            </div>

            <div class="space-y-3.5 border-t md:border-t-0 md:border-l border-slate-100 md:pl-6 pt-4 md:pt-0">
              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Status Iuran Bulanan</span>
                <span
                  class="inline-block mt-1 px-2.5 py-0.5 rounded text-[11px] font-bold uppercase"
                  :class="memberData.status_iuran === 'lunas' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                >
                  {{ memberData.status_iuran === 'lunas' ? 'LUNAS (TERVERIFIKASI)' : 'MENUNGGU PEMBAYARAN' }}
                </span>
              </div>

              <div>
                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Posisi Parkir Saat Ini</span>
                <div class="mt-1 flex items-center gap-2">
                  <span
                    class="w-2 h-2 rounded-full"
                    :class="memberData.sedang_parkir ? 'bg-[#0099ff]' : 'bg-slate-400'"
                  ></span>
                  <p class="text-xs font-bold" :class="memberData.sedang_parkir ? 'text-[#0099ff]' : 'text-slate-600'">
                    {{ memberData.sedang_parkir ? 'Sedang Berada di Area Parkir' : 'Di Luar Area Parkir' }}
                  </p>
                </div>
                <p v-if="memberData.sedang_parkir && memberData.waktu_masuk" class="text-[11px] text-slate-500 font-mono mt-0.5">
                  Masuk sejak: {{ memberData.waktu_masuk }} (Tiket: {{ memberData.kode_tiket_aktif }})
                </p>
              </div>

              <div class="pt-2">
                <p class="text-[11px] text-slate-500 leading-relaxed bg-slate-50 p-2.5 rounded-lg border border-slate-100">
                  <span class="font-bold text-slate-700">Petunjuk Gerbang:</span> Tunjukkan QR Code kartu fisik Anda ke arah kamera scanner saat berada di depan palang masuk atau keluar.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Membership Pricing Catalog Section -->
      <div class="space-y-6 pt-6">
        <div class="text-center max-w-xl mx-auto space-y-1">
          <h2 class="text-xl sm:text-2xl font-black text-[#001a4e]">
            Katalog Paket Berlangganan Parkir
          </h2>
          <p class="text-xs text-slate-500">
            Pilihan paket hemat dan fleksibel untuk mobilitas harian Anda tanpa ribet karcis.
          </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
          <!-- Paket 1: Motor -->
          <div class="bg-white rounded-2xl border-2 border-slate-200 hover:border-[#0099ff] p-6 sm:p-8 flex flex-col justify-between shadow-xs hover:shadow-md transition">
            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#0099ff] bg-[#e6f4ff] px-3 py-1 rounded-full">
                  RODA DUA
                </span>
                <Bike class="w-6 h-6 text-[#0099ff]" />
              </div>

              <div>
                <h3 class="text-xl font-black text-slate-900">Member Bulanan Motor</h3>
                <p class="text-xs text-slate-500 mt-1">Cocok untuk karyawan, mahasiswa, dan komuter harian.</p>
              </div>

              <div class="flex items-baseline gap-1 py-2 border-y border-slate-100">
                <span class="text-3xl font-black font-mono text-[#001a4e]">Rp 50.000</span>
                <span class="text-xs font-bold text-slate-400">/ bulan</span>
              </div>

              <ul class="space-y-2.5 text-xs text-slate-600">
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Akses keluar-masuk 24 jam tanpa tiket karcis</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Jalur khusus tapping QR / barcode instan</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Bebas biaya parkir tarif jam-jaman (Rp 0 saat keluar)</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Kartu fisik akses resmi dengan proteksi Anti-Passback</span>
                </li>
              </ul>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-100">
              <NuxtLink
                to="/user"
                class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-[#0099ff] hover:text-white text-[#001a4e] text-xs font-bold transition flex items-center justify-center gap-2"
              >
                <span>Aktivasi di Pos Loket Gerbang</span>
                <ArrowRight class="w-4 h-4" />
              </NuxtLink>
            </div>
          </div>

          <!-- Paket 2: Mobil -->
          <div class="bg-white rounded-2xl border-2 border-[#001a4e] p-6 sm:p-8 flex flex-col justify-between shadow-md relative overflow-hidden">
            <div class="absolute top-0 right-0 bg-[#ff9900] text-slate-950 font-black text-[9px] uppercase tracking-widest px-3 py-1 rounded-bl-lg">
              TERPOPULER
            </div>

            <div class="space-y-4">
              <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-[#001a4e] bg-slate-100 px-3 py-1 rounded-full">
                  RODA EMPAT
                </span>
                <Car class="w-6 h-6 text-[#ff9900]" />
              </div>

              <div>
                <h3 class="text-xl font-black text-slate-900">Member Bulanan Mobil</h3>
                <p class="text-xs text-slate-500 mt-1">Dirancang untuk kendaraan operasional, eksekutif & perkantoran.</p>
              </div>

              <div class="flex items-baseline gap-1 py-2 border-y border-slate-100">
                <span class="text-3xl font-black font-mono text-[#001a4e]">Rp 200.000</span>
                <span class="text-xs font-bold text-slate-400">/ bulan</span>
              </div>

              <ul class="space-y-2.5 text-xs text-slate-600">
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Unlimited in & out bebas antri kasir pembayaran</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Prioritas alokasi slot parkir mobil roda empat</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Gratis parkir berjam-jam tanpa perlu bayar kasir</span>
                </li>
                <li class="flex items-center gap-2">
                  <CheckCircle2 class="w-4 h-4 text-emerald-600 shrink-0" />
                  <span>Dukungan cetak kartu ID fisik & stempel resmi kuitansi</span>
                </li>
              </ul>
            </div>

            <div class="mt-8 pt-4 border-t border-slate-100">
              <NuxtLink
                to="/user"
                class="w-full py-2.5 px-4 rounded-xl bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold transition flex items-center justify-center gap-2 shadow-xs"
              >
                <span>Aktivasi di Pos Loket Gerbang</span>
                <ArrowRight class="w-4 h-4" />
              </NuxtLink>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Simple Footer -->
    <footer class="bg-white border-t border-slate-200 py-6 text-center text-xs text-slate-500">
      <div class="max-w-6xl mx-auto px-4 flex flex-col sm:flex-row items-center justify-between gap-3">
        <p>&copy; 2026 Secure Parking - Sistem Manajemen Parkir Digital.</p>
        <div class="flex items-center gap-4 text-slate-400 font-medium">
          <NuxtLink to="/user" class="hover:text-slate-600">Dispenser Kiosk</NuxtLink>
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
