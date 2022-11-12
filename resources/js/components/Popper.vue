<template>
  <Popper
    ref="popper"
    v-slot="{
      popperId,
      isShown,
      shouldMountContent,
      skipTransition,
      autoHide,
      show,
      hide,
      handleResize,
      onResize,
      classes,
      result,
      attrs,
    }"
    v-bind="popperAttrs"
    :theme="finalTheme"
    :target-nodes="getTargetNodes"
    :reference-node="() => $refs.reference"
    :popper-node="() => $refs.popperContent.$el"
    :container="container"
    @show="$emit('show', $event)"
    @hide="$emit('hide', $event)"
  >
    <div
      ref="reference"
      class="v-popper"
      v-bind="attrs"
      :class="[
        $attrs.class,
        themeClass,
        {
          'v-popper--shown': isShown,
        },
      ]"
      :style="$attrs.style"
    >
      <slot :shown="isShown" :show="show" :hide="hide" />

      <PopperContent
        class="focus:outline-none"
        ref="popperContent"
        :popper-id="popperId"
        :theme="finalTheme"
        :shown="isShown"
        :mounted="shouldMountContent"
        :skip-transition="skipTransition"
        :auto-hide="autoHide"
        :handle-resize="handleResize"
        :classes="classes"
        :result="result"
        @hide="hide"
        @resize="onResize"
      >
        <slot name="popper" :shown="isShown" :hide="hide" />
      </PopperContent>
    </div>
  </Popper>
</template>

<script>
import { Popper, PopperContent, PopperMethods, ThemeClass } from 'floating-vue'

export default {
  inheritAttrs: false,
  emits: ['show', 'hide'],
  mixins: [PopperMethods, ThemeClass()],
  name: 'IPopper',
  provide() {
    return {
      hide: this.hide,
      show: this.show,
    }
  },
  components: {
    Popper: Popper(),
    PopperContent,
  },
  props: {
    theme: String,
  },
  data: () => ({
    container: 'body',
  }),
  computed: {
    finalTheme() {
      return this.theme || this.$options.vPopperTheme
    },
    popperAttrs() {
      const result = { ...this.$attrs }
      delete result.class
      delete result.style
      return result
    },
  },
  methods: {
    getTargetNodes() {
      return Array.from(this.$refs.reference.children).filter(
        node => node !== this.$refs.popperContent.$el
      )
    },
    useDialogContainerIfNeeded() {
      // We will check if the popper reference is embedded in a dialog
      // if yes, we will use the dialog as container so the popper be appended in the dialog
      // because if it's in the body, the INPUT fields won't be focusable
      // As well modal closes if the popover is not in the dialog container, explicitly on the custom select field
      let parentDialog = this.$refs.reference.parents('.dialog')

      if (parentDialog) {
        this.container = parentDialog[0]
      }
    },
  },
  mounted() {
    this.$nextTick(this.useDialogContainerIfNeeded)
  },
}
</script>
