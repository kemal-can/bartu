<template>
  <i-overlay :show="overlay">
    <div
      :class="[
        'card overflow-hidden bg-white ring-1 ring-neutral-600 ring-opacity-5 dark:bg-neutral-900',
        { 'rounded-lg': rounded, shadow: shadow },
      ]"
      v-bind="$attrs"
    >
      <!-- Header -->
      <div
        class="flex flex-wrap items-center justify-between border-b border-neutral-200 px-4 py-5 dark:border-neutral-700 sm:flex-nowrap sm:px-6"
        :class="headerClass"
        v-if="header || $slots.header || $slots.actions"
      >
        <div class="grow">
          <slot name="header">
            <i-card-heading>{{ header }}</i-card-heading>
          </slot>
          <p
            class="mt-1 max-w-2xl text-sm text-neutral-500 dark:text-neutral-200"
            v-if="description"
            v-text="description"
          ></p>
        </div>
        <div
          class="shrink-0 sm:ml-4"
          :class="actionsClass"
          v-if="$slots.actions"
        >
          <slot name="actions"></slot>
        </div>
      </div>

      <!-- Body -->
      <i-card-body v-if="!noBody">
        <slot></slot>
      </i-card-body>

      <slot v-else></slot>

      <!-- Footer -->
      <i-card-footer v-if="$slots.footer" :class="footerClass">
        <slot name="footer"></slot>
      </i-card-footer>
    </div>
  </i-overlay>
</template>
<script>
export default {
  name: 'ICard',
  inheritAttrs: false,
  props: {
    header: String,
    headerClass: [String, Array, Object],
    actionsClass: [String, Array, Object],
    footerClass: [String, Array, Object],
    description: String,
    overlay: { default: false, type: Boolean },
    rounded: { default: true, type: Boolean },
    shadow: { default: true, type: Boolean },
    noBody: {
      default: false,
      type: Boolean,
    },
  },
}
</script>
