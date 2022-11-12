<template>
  <component
    :is="tag"
    :type="type"
    :class="computedClasses"
    :disabled="disabled"
    @click="handleClickEvent"
    :tabindex="disabled ? '-1' : undefined"
  >
    <icon
      :icon="icon"
      v-show="!loading"
      v-if="icon"
      :class="[
        'pointer-events-none shrink-0', // avoid click event.target propagating to the icon, see FloatingFilters vco middleware
        !iconClass
          ? { 'h-4 w-4': size === 'sm', 'h-5 w-5': size !== 'sm' }
          : iconClass,
        { '-ml-1 mr-2': $slots.default },
      ]"
    />
    <i-spinner
      :class="[
        { 'h-4 w-4': size === 'sm', 'h-5 w-5': size !== 'sm' },
        { 'mr-2': $slots.default },
        'text-current',
      ]"
      v-if="loading"
    />
    <slot></slot>
  </component>
</template>
<script>
export default {
  name: 'IButton',
  emits: ['click'],
  props: {
    icon: String,
    iconClass: [String, Array, Object],
    to: [Object, String],
    tag: { default: 'button', type: String },
    type: { type: String, default: 'button' },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    rounded: { default: true, type: Boolean },
    block: { default: false, type: Boolean },
    variant: {
      type: String,
      default: 'primary',
      validator(value) {
        return ['primary', 'secondary', 'danger', 'white', 'success'].includes(
          value
        )
      },
    },
    size: {
      type: [String, Boolean],
      default: 'md',
      validator(value) {
        if (value === false) {
          return true
        }
        // buttons have md by default because can be used to other
        // elements link </a>
        return ['sm', 'md', 'lg'].includes(value)
      },
    },
  },
  computed: {
    computedClasses() {
      return [
        'btn',
        {
          'btn-primary': this.variant === 'primary',
          'btn-secondary': this.variant === 'secondary',
          'btn-danger': this.variant === 'danger',
          'btn-white': this.variant === 'white',
          'btn-success': this.variant === 'success',
          'btn-sm': this.size === 'sm',
          'btn-md': this.size === 'md',
          'btn-lg': this.size === 'lg',
          rounded: this.rounded && this.size === 'sm',
          'rounded-md':
            this.rounded && (this.size === 'md' || this.size === 'lg'),
          'w-full justify-center': this.block,
          'only-icon': this.icon && !this.$slots.default,
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
