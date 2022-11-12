<template>
  <contact-deal-card :contact="contact" @dissociated="$emit('refetch')" />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import ContactDealCard from '@/views/Contacts/ContactDealCard'
defineProps({
  contact: { required: true, type: Object },
})
defineEmits(['refetch'])
</script>
