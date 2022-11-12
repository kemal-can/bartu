<template>
  <deal-contact-card :deal="deal" @dissociated="$emit('refetch')" />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import DealContactCard from '@/views/Deals/DealContactCard'
defineProps({
  deal: { required: true, type: Object },
})
defineEmits(['refetch'])
</script>
