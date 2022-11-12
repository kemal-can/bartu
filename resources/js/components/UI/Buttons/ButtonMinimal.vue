<template>
  <component
    :is="tag"
    :type="type"
    :class="computedClasses"
    :disabled="disabled"
    @click="handleClickEvent"
    :tabindex="disabled ? '-1' : undefined"
    class="inline-flex items-center px-2 py-1.5 text-sm font-medium focus:outline-none focus:ring-2 focus:ring-offset-2"
  >
    <i-spinner class="mr-2 h-3 w-3 text-current" v-if="loading" />
    <slot></slot>
  </component>
</template>
<script>
const colorMaps = {
  // fix neutral on dark mode, it's not appealing
  neutral:
    'bg-neutral-50 text-neutral-800 hover:bg-neutral-100 focus:ring-offset-neutral-50 focus:ring-neutral-300 dark:bg-neutral-300',
  primary:
    'bg-primary-50 text-primary-800 hover:bg-primary-100 focus:ring-offset-primary-50 focus:ring-primary-600',
  success:
    'bg-success-50 text-success-800 hover:bg-success-100 focus:ring-offset-success-50 focus:ring-success-600',
  info: 'bg-info-50 text-info-800 hover:bg-info-100 focus:ring-offset-info-50 focus:ring-info-600',
  warning:
    'bg-warning-50 text-warning-800 hover:bg-warning-100 focus:ring-offset-warning-50 focus:ring-warning-600',
  danger:
    'bg-danger-50 text-danger-800 hover:bg-danger-100 focus:ring-offset-danger-50 focus:ring-danger-600',
}

export default {
  emits: ['click'],
  name: 'IButtonMinimal',
  props: {
    to: [Object, String],
    rounded: {
      default: true,
      type: Boolean,
    },
    tag: {
      default: 'button',
      type: String,
    },
    type: {
      type: String,
      default: 'button',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    loading: {
      type: Boolean,
      default: false,
    },
    variant: {
      default: 'primary',
      type: String,
      validator(value) {
        return Object.keys(colorMaps).includes(value)
      },
    },
  },
  computed: {
    computedClasses() {
      return [
        colorMaps[this.variant],
        {
          'pointer-events-none opacity-60': this.disabled,
          'rounded-md': this.rounded,
        },
      ]
    },
  },
  methods: {
    handleClickEvent(e) {
      if (this.to) {
        this.$router.push(this.to)
      } else {
        this.$emit('click', e)
      }
    },
  },
}
</script>
