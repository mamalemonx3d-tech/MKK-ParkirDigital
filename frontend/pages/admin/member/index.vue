<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Monitoring Anggota Langganan (Member)
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Tinjauan seluruh member aktif, masa berlaku langganan, dan status pelunasan.
        </p>
      </div>
      <NuxtLink
        to="/petugas/member"
        class="px-3.5 py-2 rounded-lg bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs"
      >
        <UserPlus class="w-4 h-4" />
        <span>Buka Form Pendaftaran Member</span>
      </NuxtLink>
    </div>

    <!-- Members Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-4">Kode Member</th>
              <th class="py-3 px-4">Nama Lengkap</th>
              <th class="py-3 px-4">Perusahaan / Kantor</th>
              <th class="py-3 px-4">Tarif Langganan</th>
              <th class="py-3 px-4">Batas Masa Aktif</th>
              <th class="py-3 px-4">Status Pembayaran</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="members.length === 0">
              <td colspan="7" class="text-center py-8 text-slate-400">
                Belum ada member terdaftar.
              </td>
            </tr>
            <tr
              v-for="m in members"
              :key="m.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="py-3 px-4 font-mono font-bold text-[#001a4e]">
                {{ m.kode }}
              </td>
              <td class="py-3 px-4 font-bold text-slate-900">
                {{ m.nama }}
              </td>
              <td class="py-3 px-4 text-slate-600">
                {{ m.perusahaan }}
              </td>
              <td class="py-3 px-4 font-mono font-semibold text-slate-800">
                {{ formatRupiah(m.harga) }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-700">
                {{ m.expired_at }}
              </td>
              <td class="py-3 px-4">
                <span
                  class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider"
                  :class="m.status === 'lunas' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-amber-50 text-amber-700 border border-amber-200'"
                >
                  {{ m.status === 'lunas' ? 'Lunas' : 'Belum Lunas' }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <NuxtLink
                  :to="`/petugas/member/kartu/${m.id}`"
                  class="px-3 py-1 bg-slate-100 hover:bg-[#e6f4ff] text-[#0099ff] text-[11px] font-bold rounded transition inline-flex items-center gap-1.5"
                >
                  <CreditCard class="w-3.5 h-3.5" />
                  <span>Lihat Kartu</span>
                </NuxtLink>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { UserPlus, CreditCard } from 'lucide-vue-next'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const api = useApi()
const members = ref<any[]>([])

const fetchMembers = async () => {
  try {
    const res = await api.get('/members')
    members.value = res.data || []
  } catch (err) {
    console.error('Gagal mengambil daftar member di admin:', err)
  }
}

const formatRupiah = (val: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    maximumFractionDigits: 0,
  }).format(val || 0)
}

onMounted(() => {
  fetchMembers()
})
</script>
