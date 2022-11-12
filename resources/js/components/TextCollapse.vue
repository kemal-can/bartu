<template>
  <div class="relative">
    <div v-html="visibleText" v-bind="$attrs" />

    <div v-show="hasTextToCollapse">
      <slot name="action" :collapsed="localCollapsed" :toggle="toggle">
        <div
          v-show="localCollapsed"
          @click="toggle"
          class="absolute bottom-0 h-1/2 w-full cursor-pointer bg-gradient-to-t from-white to-transparent dark:from-neutral-900"
        />

        <a
          href="#"
          v-show="!localCollapsed"
          class="link mt-2 block text-sm !no-underline"
          @click.prevent="toggle"
          v-t="'app.show_less'"
        />
      </slot>
    </div>
  </div>
</template>
<script>
import truncate from 'truncate-html'
export default {
  inheritAttrs: false,
  emits: ['update:collapsed', 'hasTextToCollapse'],
  props: {
    text: { type: String, required: true },
    length: { default: 150, type: Number },
    collapsed: { type: Boolean, default: true },
  },
  watch: {
    collapsed: function (newVal) {
      this.localCollapsed = newVal
    },
    text: {
      handler: function (newVal) {
        this.truncated = truncate(newVal, this.length)
        this.$nextTick(() =>
          this.$emit('hasTextToCollapse', this.hasTextToCollapse)
        )
      },
      immediate: true,
    },
  },
  data() {
    return {
      localCollapsed: true,
      truncated: '',
    }
  },
  computed: {
    /**
     * Get the visible text for the user
     *
     * @return {String}
     */
    visibleText() {
      return this.localCollapsed ? this.truncated : this.text
    },

    /**
     * Indicates whether there is text to collapse
     *
     * @return {Boolean}
     */
    hasTextToCollapse() {
      return this.text.length >= this.length
    },
  },
  methods: {
    toggle() {
      this.localCollapsed = !this.localCollapsed
      this.$emit('update:collapsed', this.localCollapsed)
    },
  },
  created() {
    this.localCollapsed = this.collapsed
  },
}
</script>
