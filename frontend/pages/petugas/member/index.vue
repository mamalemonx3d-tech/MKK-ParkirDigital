<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Data Langganan Member Parkir
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Kelola pendaftaran anggota, penerbitan kartu ber-QR Code, dan masa berlaku.
        </p>
      </div>
      <div class="flex items-center gap-2">
        <button
          @click="openAddModal"
          class="px-3.5 py-2 rounded-lg bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer"
        >
          <UserPlus class="w-4 h-4" />
          <span>Daftar Member Baru</span>
        </button>
      </div>
    </div>

    <!-- Members Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-4">Kode</th>
              <th class="py-3 px-4">Nama Lengkap</th>
              <th class="py-3 px-4">Perusahaan / Instansi</th>
              <th class="py-3 px-4">Tarif Langganan</th>
              <th class="py-3 px-4">Masa Berlaku</th>
              <th class="py-3 px-4">Status Iuran</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="members.length === 0">
              <td colspan="7" class="text-center py-8 text-slate-400">
                Belum ada data member terdaftar.
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
                <div class="inline-flex items-center gap-1.5">
                  <NuxtLink
                    :to="`/petugas/member/kartu/${m.id}`"
                    title="Cetak Kartu Member"
                    class="p-1.5 rounded-md hover:bg-[#e6f4ff] text-[#0099ff] transition cursor-pointer"
                  >
                    <CreditCard class="w-4 h-4" />
                  </NuxtLink>
                  <button
                    v-if="m.status !== 'lunas'"
                    @click="markLunas(m.id)"
                    title="Tandai Lunas"
                    class="p-1.5 rounded-md hover:bg-emerald-50 text-emerald-600 transition cursor-pointer"
                  >
                    <Check class="w-4 h-4" />
                  </button>
                  <button
                    @click="deleteMember(m.id)"
                    title="Hapus Member"
                    class="p-1.5 rounded-md hover:bg-red-50 text-red-500 transition cursor-pointer"
                  >
                    <Trash2 class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modal Form: Tambah Member -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl border border-slate-200 max-w-md w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-sm font-bold text-[#001a4e]">Daftarkan Member Baru</h3>
          <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitMember" class="space-y-3.5">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
            <input
              v-model="form.nama"
              type="text"
              required
              placeholder="Contoh: Budi Santoso"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Perusahaan / Kantor</label>
            <input
              v-model="form.perusahaan"
              type="text"
              required
              placeholder="Contoh: PT Sumber Rejeki"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none"
            />
          </div>

          <div class="grid grid-cols-2 gap-3">
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Biaya Iuran (Rp)</label>
              <input
                v-model="form.harga"
                type="number"
                required
                placeholder="200000"
                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none font-mono"
              />
            </div>
            <div>
              <label class="block text-xs font-semibold text-slate-700 mb-1">Status Pembayaran</label>
              <select
                v-model="form.status"
                class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none bg-white font-medium"
              >
                <option value="belum">Belum Lunas</option>
                <option value="lunas">Lunas</option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Batas Masa Berlaku</label>
            <input
              v-model="form.expired_at"
              type="date"
              required
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none font-mono"
            />
          </div>

          <div class="pt-3 flex gap-2">
            <button
              type="submit"
              :disabled="isSubmitting"
              class="flex-1 py-2.5 px-4 bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold rounded-lg transition cursor-pointer disabled:opacity-60"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Data Member' }}
            </button>
            <button
              type="button"
              @click="isModalOpen = false"
              class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition cursor-pointer"
            >
              Batal
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, onMounted } from 'vue'
import { UserPlus, CreditCard, Check, Trash2, X } from 'lucide-vue-next'

definePageMeta({
  layout: 'petugas',
  middleware: 'auth',
})

const api = useApi()
const members = ref<any[]>([])
const isModalOpen = ref(false)
const isSubmitting = ref(false)

const form = reactive({
  nama: '',
  perusahaan: '',
  harga: 150000,
  status: 'belum',
  expired_at: '',
})

const fetchMembers = async () => {
  try {
    const res = await api.get('/members')
    members.value = res.data || []
  } catch (err) {
    console.error('Gagal mengambil member:', err)
  }
}

const openAddModal = () => {
  form.nama = ''
  form.perusahaan = ''
  form.harga = 150000
  form.status = 'belum'

  // Default 1 bulan ke depan
  const d = new Date()
  d.setMonth(d.getMonth() + 1)
  form.expired_at = d.toISOString().split('T')[0]

  isModalOpen.value = true
}

const submitMember = async () => {
  isSubmitting.value = true
  try {
    const res = await api.post('/members', form)
    if (res.data?.success) {
      isModalOpen.value = false
      await fetchMembers()
    }
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan member.')
  } finally {
    isSubmitting.value = false
  }
}

const markLunas = async (id: number) => {
  try {
    await api.put(`/members/${id}/lunas`)
    await fetchMembers()
  } catch (err) {
    console.error('Gagal melunasi member:', err)
  }
}

const deleteMember = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus data member ini?')) return
  try {
    await api.delete(`/members/${id}`)
    await fetchMembers()
  } catch (err) {
    console.error('Gagal menghapus member:', err)
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
