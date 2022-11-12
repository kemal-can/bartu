<template>
  <component :is="tag" v-if="shouldRenderContents" v-show="isActive"
    ><slot></slot
  ></component>
</template>
<script>
import { randomString } from '@/utils'
import { computed } from 'vue'
export default {
  emits: ['activated', 'deactivated', 'activated-first-time'],
  inject: ['registerTab', 'selectTab', 'activeTab'],
  props: {
    title: String,
    disabled: Boolean,
    tag: { default: 'div', type: String },
    classes: [Array, Object, String],
    lazy: Boolean,
    badge: [String, Number],
    badgeVariant: {
      default: 'info',
    },
    icon: String,
    tabId: {
      type: String,
      default() {
        return randomString(10)
      },
    },
  },
  data: () => ({
    isActive: false,
    activatedFirstTime: false,
  }),
  computed: {
    shouldRenderContents() {
      if (this.lazy !== true) {
        return true
      }

      return this.isActive === true
    },
  },
  watch: {
    activeTab: {
      handler: function (newVal) {
        const currentTabIsActive = newVal === this.tabId
        this.isActive = currentTabIsActive
        this.$emit(currentTabIsActive ? 'activated' : 'deactivated', this.tabId)

        if (currentTabIsActive && !this.activatedFirstTime) {
          this.activatedFirstTime = true
          this.$emit('activated-first-time', this.tabId)
        }
      },
      immediate: true,
    },
  },
  beforeMount() {
    this.registerTab(
      computed(() => {
        return {
          title: this.title,
          disabled: this.disabled,
          class: this.classes,
          badge: this.badge,
          badgeVariant: this.badgeVariant,
          icon: this.icon,
          tabId: this.tabId,
          isActive: this.isActive,
        }
      })
    )
  },
}
</script>
