<template>
  <deal-company-card :deal="deal" @dissociated="$emit('refetch')" />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import DealCompanyCard from '@/views/Deals/DealCompanyCard'
defineProps({
  deal: { required: true, type: Object },
})
defineEmits(['refetch'])
</script>
