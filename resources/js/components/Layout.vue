<template>
  <teleport to="#navbar-actions">
    <slot name="actions"></slot>
  </teleport>

  <main
    class="relative flex-1 focus:outline-none"
    :class="{ 'overflow-y-auto': scrollable }"
    id="main"
    v-bind="$attrs"
  >
    <div :class="{ 'py-6': !full }">
      <div :class="['mx-auto', { 'px-4 sm:px-6 lg:px-8': !full }]">
        <div :class="{ 'sm:py-4': !full }">
          <i-overlay :show="overlay">
            <div
              v-if="$slots.actions"
              :class="[
                'mb-4 flex justify-end lg:hidden',
                { 'px-4 pt-10 sm:px-6 lg:px-8': full },
              ]"
            >
              <slot name="actions"></slot>
            </div>
            <slot></slot>
          </i-overlay>
        </div>
      </div>
    </div>
  </main>
</template>
<script>
export default {
  inheritAttrs: false,
  props: {
    title: String,
    overlay: { type: Boolean, default: false },
    full: { type: Boolean, default: false },
    scrollable: { type: Boolean, default: true },
  },
}
</script>
