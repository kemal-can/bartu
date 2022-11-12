<template>
  <component
    :is="tag"
    v-i-tooltip="withTooltip ? $t('app.copy') : ''"
    :icon="icon"
    v-bind="$attrs"
  >
    <slot></slot>
  </component>
</template>
<script>
import ClipboardJS from 'clipboard'
export default {
  inheritAttrs: false,
  props: {
    text: {
      type: String,
    },
    clipboardOptions: {
      type: Object,
      default() {
        return {}
      },
    },
    withTooltip: {
      type: Boolean,
      default: true,
    },
    tag: {
      type: String,
      default: 'i-button-icon',
    },
    icon: {
      type: String,
      default: 'Duplicate',
    },
    successMessage: String,
  },
  mounted() {
    // https://clipboardjs.com/#advanced-usage
    // For modals
    this.clipboard = new ClipboardJS(
      this.$el,
      Object.assign(
        {},
        {
          container: this.$el,
          text: trigger => this.text,
        },
        this.clipboardOptions
      )
    )

    this.clipboard.on('success', e => {
      Innoclapps.info(this.successMessage || this.$t('app.copied'))
      e.clearSelection()
    })

    this.clipboard.on('error', e => {
      console.log(e)
      Innoclapps.error('Failed to perform this action.')
    })
  },
  unmounted() {
    this.clipboard.destroy()
  },
}
</script>
