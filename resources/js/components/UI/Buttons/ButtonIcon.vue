<template>
  <button
    v-bind="$attrs"
    type="button"
    @click="$emit('click', $event)"
    class="flex items-center rounded-full text-neutral-500 hover:text-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 focus:ring-offset-neutral-100 dark:text-white dark:hover:text-neutral-300"
  >
    <slot>
      <icon :icon="icon" :class="['pointer-events-none', iconClass]" />
    </slot>
  </button>
</template>
<script>
export default {
  inheritAttrs: false,
  emits: ['click'],
  props: {
    icon: { required: true, type: String },
    iconClass: {
      type: [String, Array, Object],
      default: 'w-5 h-5',
    },
  },
}
</script>
