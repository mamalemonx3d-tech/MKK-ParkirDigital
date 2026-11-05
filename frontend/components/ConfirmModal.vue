<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="isOpen"
        class="fixed inset-0 z-50 bg-slate-950/50 backdrop-blur-xs flex items-center justify-center p-4 select-none font-sans"
        @click.self="handleCancel"
      >
        <div
          class="bg-white rounded-2xl border border-slate-200 max-w-sm w-full p-6 text-center shadow-xl space-y-4 animate-in fade-in zoom-in-95 duration-200"
        >
          <!-- Animated Lottie Header -->
          <div class="flex justify-center -mt-2">
            <LottieAnimation
              :animationPath="options.animationPath"
              width="w-20"
              height="h-20"
              :loop="options.type === 'warning' || options.type === 'info'"
              class="mx-auto"
            />
          </div>

          <!-- Dialog Title & Message -->
          <div class="space-y-1.5">
            <h3 class="text-lg font-normal text-slate-900 font-serif tracking-tight">
              {{ options.title }}
            </h3>
            <p class="text-xs text-slate-600 leading-relaxed font-sans">
              {{ options.message }}
            </p>
          </div>

          <!-- Dialog Actions -->
          <div class="pt-2">
            <!-- Alert Mode: Single Confirmation Button -->
            <div v-if="options.isAlert">
              <button
                @click="handleConfirm"
                class="w-full py-2.5 px-4 bg-[#00ffff] hover:bg-[#00e5e5] text-slate-950 border border-cyan-400 text-xs font-bold rounded-xl transition shadow-xs cursor-pointer active:scale-[0.98]"
              >
                {{ options.confirmText || 'Mengerti' }}
              </button>
            </div>

            <!-- Confirm Mode: Cancel + Action Buttons -->
            <div v-else class="flex items-center gap-3">
              <button
                type="button"
                @click="handleCancel"
                class="flex-1 py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition border border-slate-200 cursor-pointer active:scale-[0.98]"
              >
                {{ options.cancelText || 'Batal' }}
              </button>
              <button
                type="button"
                @click="handleConfirm"
                class="flex-1 py-2.5 px-4 text-xs font-bold rounded-xl transition shadow-xs cursor-pointer active:scale-[0.98]"
                :class="options.type === 'danger' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-[#00ffff] hover:bg-[#00e5e5] text-slate-950 border border-cyan-400'"
              >
                {{ options.confirmText || 'Ya, Lanjutkan' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup lang="ts">
import { onMounted, onUnmounted } from 'vue'
import LottieAnimation from '~/components/LottieAnimation.vue'

const { isOpen, options, handleConfirm, handleCancel } = useConfirm()

const handleKeydown = (e: KeyboardEvent) => {
  if (!isOpen.value) return
  if (e.key === 'Escape') {
    handleCancel()
    e.preventDefault()
  } else if (e.key === 'Enter') {
    handleConfirm()
    e.preventDefault()
  }
}

onMounted(() => {
  window.addEventListener('keydown', handleKeydown)
})

onUnmounted(() => {
  window.removeEventListener('keydown', handleKeydown)
})
</script>
