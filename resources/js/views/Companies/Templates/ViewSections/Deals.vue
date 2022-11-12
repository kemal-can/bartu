<template>
  <company-deal-card :company="company" @dissociated="$emit('refetch')" />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import CompanyDealCard from '@/views/Companies/CompanyDealCard'
defineProps({
  company: { required: true, type: Object },
})
defineEmits(['refetch'])
</script>
