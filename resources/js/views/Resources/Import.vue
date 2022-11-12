<template>
  <i-layout>
    <import :resource-name="$route.params.resourceName" :header="header" />
  </i-layout>
</template>
<script>
import Import from '@/components/Import'
import { singularize } from '@/utils'
export default {
  components: { Import },
  computed: {
    header() {
      return this.$t(singularize(this.$route.params.resourceName) + '.import')
    },
  },
  created() {
    this.setPageTitle(this.header)
  },
}
</script>
