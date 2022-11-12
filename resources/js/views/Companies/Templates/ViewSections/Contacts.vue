<template>
  <company-contact-card :company="company" @dissociated="$emit('refetch')" />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import CompanyContactCard from '@/views/Companies/CompanyContactCard'
defineProps({
  company: { required: true, type: Object },
})
defineEmits(['refetch'])
</script>
