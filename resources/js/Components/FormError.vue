<script setup>
defineProps({
  message: {
    type: String,
    default: ''
  }
})
</script>

<template>
  <p v-if="message" class="mt-2 text-sm text-red-600">
    {{ message }}
  </p>
</template> 