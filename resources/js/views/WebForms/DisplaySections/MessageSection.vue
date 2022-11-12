<template>
  <div
    v-html="section.message"
    class="mb-3 text-sm text-neutral-600 dark:text-neutral-300"
  />
</template>
<script>
import Section from './Section'
export default {
  mixins: [Section],
}
</script>
