<script setup lang="ts">
// Alias route agar localhost:3000/kiosk langsung mengarah ke halaman Kiosk Mandiri
definePageMeta({
  layout: false,
})

await navigateTo('/user', { replace: true })
</script>

<template>
  <div></div>
</template>
