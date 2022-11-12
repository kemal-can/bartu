<template>
  <i-popper
    ref="popper"
    :class="{ 'w-full': full }"
    theme="dropdown"
    :distance="10"
    :skidding="0"
    v-bind="$attrs"
  >
    <slot
      name="toggle"
      v-bind="{
        disabled: disabled,
        loading: loading,
        icon: icon,
        iconClass: iconClass,
        noCaret: noCaret,
      }"
    >
      <!-- Must be <button> element not <a> because the events are not handled properly when in modal
				closes the modal, needs further debugging -->
      <i-button
        :variant="variant"
        :disabled="disabled"
        :loading="loading"
        :rounded="rounded"
        :size="size"
        :icon="icon"
        :icon-class="iconClass"
        :class="['w-full', toggleClass, { 'justify-between': !noCaret }]"
      >
        <slot name="toggle-content">
          {{ text }}
        </slot>
        <icon
          icon="ChevronDown"
          v-if="!noCaret"
          :class="size !== 'sm' ? 'h-5 w-5' : 'h-4 w-4'"
          class="-mr-1 ml-2 shrink-0"
        ></icon>
      </i-button>
    </slot>
    <template #popper>
      <div :class="popperClass">
        <slot></slot>
      </div>
    </template>
  </i-popper>
</template>
<script>
import { options } from 'floating-vue'

options.themes.dropdown = {
  // Default dropdown placement relative to target element
  placement: 'bottom',
  // Default events that trigger the dropdown
  triggers: ['click'],
  // Delay (ms)
  delay: 0,
  // Update popper on content resize
  handleResize: true,
  // Hide on click outside
  autoHide: true,
}

export default {
  inheritAttrs: false,
  props: {
    text: String,
    popperClass: {
      default: 'bg-white rounded-md dark:bg-neutral-800 max-w-xs sm:max-w-sm',
      type: [String, Array, Object],
    },
    variant: { type: String, default: 'white' },
    full: { type: Boolean, default: true },
    disabled: { type: Boolean, default: false },
    loading: { type: Boolean, default: false },
    rounded: { type: Boolean, default: true },
    icon: { type: String },
    iconClass: [String, Array, Object],
    toggleClass: [String, Array, Object],
    size: { type: String, default: 'md' },
    noCaret: { default: false, type: Boolean },
  },
  methods: {
    show(...args) {
      return this.$refs.popper.show(...args)
    },
    hide(...args) {
      return this.$refs.popper.hide(...args)
    },
  },
}
</script>
