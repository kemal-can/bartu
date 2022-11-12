<template>
  <span :class="computedClass">
    <slot></slot>
  </span>
</template>
<script>
const colorMaps = {
  neutral: 'bg-neutral-200 text-neutral-700',
  primary: 'bg-primary-100 text-primary-800',
  success: 'bg-success-100 text-success-800',
  info: 'bg-info-100 text-info-800',
  warning: 'bg-warning-100 text-warning-800',
  danger: 'bg-danger-100 text-danger-800',
}

const sizeMaps = {
  sm: 'px-2.5 py-0.5 text-xs',
  lg: 'px-3 py-0.5 text-sm',
  circle: 'h-5 w-5 justify-center text-xs',
}

export default {
  name: 'IBadge',
  props: {
    variant: {
      default: 'neutral',
      type: String,
    },
    rounded: {
      type: Boolean,
      default: true,
    },
    size: {
      default: 'sm',
      type: String,
      validator(value) {
        return ['sm', 'lg', 'circle'].includes(value)
      },
    },
  },
  computed: {
    computedClass() {
      return [
        'inline-flex items-center font-medium',
        colorMaps[this.variant],
        sizeMaps[this.size],
        this.rounded || this.size === 'circle' ? 'rounded-full' : null,
      ]
    },
  },
}
</script>
