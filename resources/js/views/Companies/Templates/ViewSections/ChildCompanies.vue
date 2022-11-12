<template>
  <company-card
    v-if="hasParents"
    :title="$t('company.child', { count: totalParents })"
    :companies="company.parents"
  />
</template>
<script>
export default {
  inheritAttrs: false,
}
</script>
<script setup>
import CompanyCard from '@/views/Companies/CompanyCard'
import { computed } from 'vue'
const props = defineProps({
  company: { required: true, type: Object },
})
const totalParents = computed(() => props.company.parents.length)
const hasParents = computed(() => totalParents.value > 0)
</script>
