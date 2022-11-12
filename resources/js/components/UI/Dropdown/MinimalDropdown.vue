<template>
  <i-dropdown no-caret v-bind="$attrs" :full="false">
    <template #toggle>
      <i-button-icon :icon="icon" :class="toggleClass" />
    </template>
    <div class="py-1">
      <slot></slot>
    </div>
  </i-dropdown>
</template>
<script>
export default {
  inheritAttrs: false,
  props: {
    type: { default: 'vertical', type: String },
    toggleClass: [Array, Object, String],
  },
  computed: {
    icon() {
      return this.type === 'horizontal' ? 'DotsHorizontal' : 'DotsVertical'
    },
  },
}
</script>
