<template>
  <contact-company-card :contact="contact" @dissociated="$emit('refetch')" />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import ContactCompanyCard from '@/views/Contacts/ContactCompanyCard'
defineProps({
  contact: { required: true, type: Object },
})
defineEmits(['refetch'])
</script>
