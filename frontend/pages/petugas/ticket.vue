<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Daftar Sesi Tiket Parkir
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Seluruh rekaman kendaraan masuk dan keluar di sistem parkir.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-if="tickets.length > 0"
          @click="clearAllTickets"
          :disabled="isClearing"
          class="px-3.5 py-2 rounded-lg bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold transition flex items-center gap-1.5 border border-red-200 cursor-pointer disabled:opacity-60"
        >
          <Trash2 class="w-3.5 h-3.5" />
          <span>{{ isClearing ? 'Menghapus...' : 'Hapus Semua Tiket' }}</span>
        </button>

        <button
          @click="fetchTickets"
          class="px-3.5 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition flex items-center gap-2 cursor-pointer"
        >
          <RefreshCw class="w-3.5 h-3.5" :class="isLoading ? 'animate-spin' : ''" />
          <span>Perbarui Data</span>
        </button>
      </div>
    </div>

    <!-- Filters Strip -->
    <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-center gap-3">
      <!-- Search -->
      <div class="relative flex-1 w-full">
        <Search class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari kode tiket atau nomor polisi..."
          class="w-full pl-9 pr-3.5 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none"
        />
      </div>

      <!-- Filter Status -->
      <select
        v-model="statusFilter"
        class="w-full sm:w-40 px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none bg-white font-medium"
      >
        <option value="all">Semua Status</option>
        <option value="masuk">Di Dalam (Masuk)</option>
        <option value="keluar">Selesai (Keluar)</option>
      </select>

      <!-- Filter Jenis -->
      <select
        v-model="jenisFilter"
        class="w-full sm:w-40 px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none bg-white font-medium"
      >
        <option value="all">Semua Jenis</option>
        <option value="non_member">Umum (Non-Member)</option>
        <option value="member">Member Langganan</option>
      </select>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-4">Kode Tiket</th>
              <th class="py-3 px-4">Jenis</th>
              <th class="py-3 px-4">Plat Nomor</th>
              <th class="py-3 px-4">Kendaraan</th>
              <th class="py-3 px-4">Waktu Masuk</th>
              <th class="py-3 px-4">Waktu Keluar</th>
              <th class="py-3 px-4">Total Biaya</th>
              <th class="py-3 px-4">Status</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="filteredTickets.length === 0">
              <td colspan="9" class="text-center py-8 text-slate-400">
                Tidak ada data tiket yang cocok.
              </td>
            </tr>
            <tr
              v-for="ticket in filteredTickets"
              :key="ticket.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="py-3 px-4 font-mono font-bold text-slate-900">
                {{ ticket.kode_tiket }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider"
                  :class="ticket.jenis === 'member' ? 'bg-indigo-50 text-indigo-700 border border-indigo-200' : 'bg-slate-100 text-slate-700'"
                >
                  {{ ticket.jenis === 'member' ? 'Member' : 'Umum' }}
                </span>
              </td>
              <td class="py-3 px-4 font-mono font-bold text-slate-700">
                {{ ticket.nomor_polisi || '-' }}
              </td>
              <td class="py-3 px-4 capitalize">
                {{ ticket.jenis_kendaraan || '-' }}
              </td>
              <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                {{ formatDateTime(ticket.waktu_masuk) }}
              </td>
              <td class="py-3 px-4 text-slate-600 font-mono text-[11px]">
                {{ formatDateTime(ticket.waktu_keluar) }}
              </td>
              <td class="py-3 px-4 font-mono font-bold" :class="ticket.total_bayar > 0 ? 'text-emerald-700' : 'text-slate-600'">
                {{ formatRupiah(ticket.total_bayar) }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider inline-flex items-center gap-1"
                  :class="ticket.status === 'masuk' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-600'"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="ticket.status === 'masuk' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                  <span>{{ ticket.status === 'masuk' ? 'Aktif' : 'Keluar' }}</span>
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <button
                  @click="deleteSingleTicket(ticket.id, ticket.kode_tiket)"
                  title="Hapus Tiket"
                  class="p-1.5 rounded hover:bg-red-50 text-slate-400 hover:text-red-600 transition cursor-pointer"
                >
                  <Trash2 class="w-3.5 h-3.5" />
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { Search, RefreshCw, Trash2 } from 'lucide-vue-next'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const api = useApi()
const tickets = ref<any[]>([])
const isLoading = ref(false)
const isClearing = ref(false)
const searchQuery = ref('')
const statusFilter = ref('all')
const jenisFilter = ref('all')

const fetchTickets = async () => {
  isLoading.value = true
  try {
    const res = await api.get('/tiket')
    tickets.value = res.data || []
  } catch (err) {
    console.error('Gagal mengambil daftar tiket:', err)
  } finally {
    isLoading.value = false
  }
}

const clearAllTickets = async () => {
  if (!confirm('Apakah Anda yakin ingin menghapus seluruh rekaman tiket parkir?')) {
    return
  }

  isClearing.value = true
  try {
    const res = await api.delete('/tiket/clear')
    if (res.data?.success) {
      tickets.value = []
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus semua tiket.')
  } finally {
    isClearing.value = false
  }
}

const deleteSingleTicket = async (id: number, kode: string) => {
  if (!confirm(`Hapus data tiket ${kode}?`)) {
    return
  }

  try {
    const res = await api.delete(`/tiket/${id}`)
    if (res.data?.success) {
      tickets.value = tickets.value.filter((t) => t.id !== id)
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus tiket.')
  }
}

const filteredTickets = computed(() => {
  return tickets.value.filter((t) => {
    const matchesSearch =
      t.kode_tiket?.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      t.nomor_polisi?.toLowerCase().includes(searchQuery.value.toLowerCase())

    const matchesStatus = statusFilter.value === 'all' || t.status === statusFilter.value
    const matchesJenis = jenisFilter.value === 'all' || t.jenis === jenisFilter.value

    return matchesSearch && matchesStatus && matchesJenis
  })
})

const formatDateTime = (str: string) => {
  if (!str) return '-'
  return new Date(str).toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    hour: '2-digit',
    minute: '2-digit',
  })
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

onMounted(() => {
  fetchTickets()
})
</script>
