<template>
  <i-tabs v-model="activeTab" border="y" centered>
    <slot></slot>
  </i-tabs>
</template>
<script>
export default {
  data: () => ({
    activeTab: null,
  }),
  created() {
    if (this.$route.query.section) {
      this.activeTab = 'section-' + this.$route.query.section
    }
  },
}
</script>
