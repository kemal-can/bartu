<template>
  <component
    :is="tag"
    class="block text-sm font-medium text-neutral-700 dark:text-neutral-100"
  >
    <span v-if="showRequiredMark" class="mr-1 text-sm text-danger-600">*</span>
    <slot>{{ label }}</slot>
  </component>
</template>
<script>
export default {
  props: {
    tag: { default: 'label', type: String },
    label: String,
    required: Boolean,
  },
  computed: {
    showRequiredMark() {
      return this.required && (this.label || this.$slots.default())
    },
  },
}
</script>
