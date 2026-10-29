<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="bg-white p-5 rounded-xl border border-slate-200 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h2 class="text-lg font-bold text-[#001a4e]">
          Manajemen Akun Petugas Gerbang
        </h2>
        <p class="text-xs text-slate-500 mt-0.5">
          Kelola data login staf pos keluar dan gerbang masuk.
        </p>
      </div>
      <button
        @click="openModal()"
        class="px-3.5 py-2 rounded-lg bg-[#0099ff] hover:bg-[#0084de] text-white text-xs font-bold transition flex items-center gap-2 cursor-pointer shadow-xs"
      >
        <UserPlus class="w-4 h-4" />
        <span>Tambah Petugas Baru</span>
      </button>
    </div>

    <!-- Table Users -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-xs overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200 text-slate-600 font-bold uppercase tracking-wider text-[11px]">
              <th class="py-3 px-4">Nama Lengkap</th>
              <th class="py-3 px-4">Alamat Email</th>
              <th class="py-3 px-4">Nomor WhatsApp</th>
              <th class="py-3 px-4">Peran (Role)</th>
              <th class="py-3 px-4 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-if="users.length === 0">
              <td colspan="5" class="text-center py-8 text-slate-400">
                Belum ada data petugas.
              </td>
            </tr>
            <tr
              v-for="u in users"
              :key="u.id"
              class="hover:bg-slate-50/80 transition"
            >
              <td class="py-3 px-4 font-bold text-slate-900">
                {{ u.name }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-700">
                {{ u.email }}
              </td>
              <td class="py-3 px-4 font-mono text-slate-600">
                {{ u.no_hp || '-' }}
              </td>
              <td class="py-3 px-4">
                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-slate-100 text-slate-700">
                  {{ u.role }}
                </span>
              </td>
              <td class="py-3 px-4 text-center">
                <div class="inline-flex items-center gap-2">
                  <button
                    @click="openModal(u)"
                    title="Edit Petugas"
                    class="p-1.5 rounded hover:bg-slate-100 text-slate-600 hover:text-[#0099ff] transition cursor-pointer"
                  >
                    <Edit class="w-4 h-4" />
                  </button>
                  <button
                    @click="deleteUser(u.id)"
                    title="Hapus Petugas"
                    class="p-1.5 rounded hover:bg-red-50 text-slate-400 hover:text-red-600 transition cursor-pointer"
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

    <!-- Modal Form: Tambah / Edit Petugas -->
    <div
      v-if="isModalOpen"
      class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
      <div class="bg-white rounded-2xl border border-slate-200 max-w-md w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-slate-100">
          <h3 class="text-sm font-bold text-[#001a4e]">
            {{ editingId ? 'Edit Data Petugas' : 'Tambah Petugas Baru' }}
          </h3>
          <button @click="isModalOpen = false" class="text-slate-400 hover:text-slate-600">
            <X class="w-4 h-4" />
          </button>
        </div>

        <form @submit.prevent="submitUser" class="space-y-3.5">
          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap</label>
            <input
              v-model="form.name"
              type="text"
              required
              placeholder="Contoh: Ahmad Fauzi"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Alamat Email</label>
            <input
              v-model="form.email"
              type="email"
              required
              placeholder="ahmad@parkir.com"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">Nomor WhatsApp (untuk OTP)</label>
            <input
              v-model="form.no_hp"
              type="text"
              placeholder="08123456789"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none font-mono"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-slate-700 mb-1">
              Kata Sandi {{ editingId ? '(Kosongkan jika tidak diubah)' : '' }}
            </label>
            <input
              v-model="form.password"
              type="password"
              :required="!editingId"
              minlength="6"
              placeholder="••••••••"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none"
            />
          </div>

          <div v-if="!editingId">
            <label class="block text-xs font-semibold text-slate-700 mb-1">Ulangi Kata Sandi</label>
            <input
              v-model="form.password_confirmation"
              type="password"
              required
              minlength="6"
              placeholder="••••••••"
              class="w-full px-3 py-2 text-xs rounded-lg border border-slate-300 focus:border-[#0099ff] outline-none"
            />
          </div>

          <div class="pt-3 flex gap-2">
            <button
              type="submit"
              :disabled="isSubmitting"
              class="flex-1 py-2.5 px-4 bg-[#001a4e] hover:bg-[#001033] text-white text-xs font-bold rounded-lg transition cursor-pointer disabled:opacity-60"
            >
              {{ isSubmitting ? 'Menyimpan...' : 'Simpan Data Petugas' }}
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
import { UserPlus, Edit, Trash2, X } from 'lucide-vue-next'

definePageMeta({
  layout: 'admin',
  middleware: 'auth',
})

const api = useApi()
const users = ref<any[]>([])
const isModalOpen = ref(false)
const isSubmitting = ref(false)
const editingId = ref<number | null>(null)

const form = reactive({
  name: '',
  email: '',
  no_hp: '',
  password: '',
  password_confirmation: '',
})

const fetchUsers = async () => {
  try {
    const res = await api.get('/users')
    if (res.data?.success) {
      users.value = res.data.data || []
    }
  } catch (err) {
    console.error('Gagal mengambil daftar petugas:', err)
  }
}

const openModal = (user?: any) => {
  if (user) {
    editingId.value = user.id
    form.name = user.name
    form.email = user.email
    form.no_hp = user.no_hp || ''
    form.password = ''
    form.password_confirmation = ''
  } else {
    editingId.value = null
    form.name = ''
    form.email = ''
    form.no_hp = ''
    form.password = ''
    form.password_confirmation = ''
  }
  isModalOpen.value = true
}

const submitUser = async () => {
  isSubmitting.value = true
  try {
    if (editingId.value) {
      await api.put(`/users/${editingId.value}`, {
        name: form.name,
        email: form.email,
        no_hp: form.no_hp,
        password: form.password || undefined,
      })
    } else {
      await api.post('/users', form)
    }
    isModalOpen.value = false
    await fetchUsers()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menyimpan data petugas.')
  } finally {
    isSubmitting.value = false
  }
}

const deleteUser = async (id: number) => {
  if (!confirm('Apakah Anda yakin ingin menghapus akun petugas ini?')) return
  try {
    await api.delete(`/users/${id}`)
    await fetchUsers()
  } catch (err: any) {
    alert(err.response?.data?.message || 'Gagal menghapus petugas.')
  }
}

onMounted(() => {
  fetchUsers()
})
</script>
