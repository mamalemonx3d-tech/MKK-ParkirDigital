/**
 * Utility formater standar untuk tampilan enterprise
 */

export const formatRupiah = (nominal: number | string | null | undefined): string => {
  const num = typeof nominal === 'string' ? parseFloat(nominal) : Number(nominal || 0)
  if (isNaN(num)) return 'Rp 0'
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(num)
}

export const formatDateTime = (dateStr: string | null | undefined): string => {
  if (!dateStr) return '-'
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(d)
}

export const formatDate = (dateStr: string | null | undefined): string => {
  if (!dateStr) return '-'
  const cleanStr = dateStr.includes('T') ? dateStr.split('T')[0] : dateStr.split(' ')[0]
  const parts = cleanStr.split('-')
  if (parts.length === 3) {
    const y = parseInt(parts[0], 10)
    const m = parseInt(parts[1], 10) - 1
    const d = parseInt(parts[2], 10)
    const date = new Date(y, m, d)
    if (!isNaN(date.getTime())) {
      return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
      }).format(date)
    }
  }
  const d = new Date(dateStr)
  if (isNaN(d.getTime())) return '-'
  return new Intl.DateTimeFormat('id-ID', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  }).format(d)
}

/**
 * Pembulatan durasi parkir ke jam penuh (Ceil Rounding):
 * - Di bawah 1 jam (misal 15m, 45m, 60m) -> dibulatkan jadi 1 Jam
 * - Di atas 1 jam (misal 1 jam 30 menit / 90m) -> dibulatkan jadi 2 Jam
 * - 2 jam 15 menit -> dibulatkan jadi 3 Jam
 */
export const formatDurasiJam = (
  durasiMenit?: number | string | null,
  waktuMasuk?: string | null,
  waktuKeluar?: string | null
): string => {
  let minutes: number | null = null

  if (durasiMenit !== null && durasiMenit !== undefined && durasiMenit !== '') {
    minutes = typeof durasiMenit === 'string' ? parseFloat(durasiMenit) : Number(durasiMenit)
  } else if (waktuMasuk) {
    const start = new Date(waktuMasuk).getTime()
    const end = waktuKeluar ? new Date(waktuKeluar).getTime() : Date.now()
    if (!isNaN(start) && !isNaN(end)) {
      minutes = Math.max(1, Math.round((end - start) / 60000))
    }
  }

  if (minutes === null || isNaN(minutes)) return '-'

  const jam = Math.max(1, Math.ceil(minutes / 60))
  return `${jam} Jam`
}

export const formatDurasiMenit = (menit: number | string | null | undefined): string => {
  if (menit === null || menit === undefined || menit === '') return '-'
  const num = typeof menit === 'string' ? parseFloat(menit) : Number(menit)
  if (isNaN(num)) return '-'
  const rounded = Math.round(num)
  return `${Math.max(1, rounded)} Menit`
}
