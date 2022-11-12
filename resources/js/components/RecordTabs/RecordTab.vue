<template>
  <i-tab :id="prefixedSectionId" :tab-id="prefixedSectionId" v-bind="$attrs">
    <slot></slot>
  </i-tab>
</template>
<script>
export default {
  inheritAttrs: false,
  props: {
    sectionId: { require: true, Type: String },
  },
  computed: {
    /**
     * Get the section id
     *
     * @return {STring}
     */
    prefixedSectionId() {
      return `section-${this.sectionId}`
    },
  },
}
</script>
