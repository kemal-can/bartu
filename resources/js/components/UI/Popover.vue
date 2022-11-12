<template>
  <i-popper
    ref="popper"
    class="w-auto"
    theme="popover"
    :distance="10"
    :skidding="0"
    v-bind="$attrs"
  >
    <slot></slot>
    <template #popper>
      <div :class="popperClass">
        <div :class="{ 'pointer-events-none opacity-60': busy }">
          <div
            class="border-b border-neutral-100 py-3 px-4 dark:border-neutral-700"
            v-if="title || $slots.title"
          >
            <slot name="title" :hide="hide">
              <p
                v-text="title"
                class="font-medium text-neutral-800 dark:text-neutral-100"
              />
            </slot>
          </div>
          <div class="py-3 px-4">
            <slot name="popper"></slot>
          </div>
        </div>
      </div>
    </template>
  </i-popper>
</template>
<script>
import { options } from 'floating-vue'

options.themes.popover = {
  // Default dropdown placement relative to target element
  placement: 'bottom',
  // Update popper on content resize
  handleResize: true,
  // Hide on clock outside
  autoHide: true,
  // Default events that trigger the dropdown
  triggers: ['click'],
  // Triggers on the popper itself
  popperTriggers: [],
  delay: {
    show: 0,
    hide: 150,
  },
}

export default {
  inheritAttrs: false,
  props: {
    busy: Boolean,
    title: String,
    popperClass: {
      default:
        'bg-white rounded-md overflow-hidden dark:bg-neutral-800 max-w-xs sm:max-w-sm break-words',
      type: [String, Array, Object],
    },
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
