<template>
  <div class="mb-3" v-if="showIntroductionSection">
    <h4
      class="text-lg font-medium text-neutral-800 dark:text-neutral-100"
      v-text="section.title"
    />
    <div
      class="text-sm text-neutral-600 dark:text-neutral-300"
      v-html="section.message"
    />
  </div>
</template>
<script>
import Section from './Section'
export default {
  mixins: [Section],
  computed: {
    showIntroductionSection() {
      return this.section.title || this.section.message
    },
  },
}
</script>
