<template>
  <TransitionRoot as="template" :show="localVisible">
    <Dialog
      as="div"
      ref="dialog"
      static
      :initial-focus="initialFocus"
      class="dialog fixed inset-0 overflow-y-auto"
      @close="dialogClosedEvent"
      :open="localVisible"
    >
      <div
        class="dialog-inner flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-1"
      >
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0"
          enter-to="opacity-100"
          leave="ease-in duration-200"
          leave-from="opacity-100"
          leave-to="opacity-0"
        >
          <DialogOverlay
            class="dialog-overlay absolute inset-0 bg-neutral-500/75 transition-opacity dark:bg-neutral-700/90"
          />
        </TransitionChild>

        <!-- This element is to trick the browser into centering the modal contents. -->
        <span
          class="hidden sm:inline-block sm:h-screen sm:align-middle"
          aria-hidden="true"
          >&#8203;</span
        >
        <TransitionChild
          as="template"
          enter="ease-out duration-300"
          enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          enter-to="opacity-100 translate-y-0 sm:scale-100"
          leave="ease-in duration-200"
          leave-from="opacity-100 translate-y-0 sm:scale-100"
          leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        >
          <component
            :is="form ? 'form' : 'div'"
            @submit.prevent="$emit('submit')"
            :novalidate="form ? true : undefined"
            @keydown.passive="$emit('keydown', $event)"
            :class="[
              { 'sm:max-w-lg': size === 'sm' },
              { 'sm:max-w-2xl': size === 'md' },
              { 'sm:max-w-3xl': size === 'lg' },
              { 'sm:max-w-4xl': size === 'xl' },
              { 'sm:max-w-5xl': size === 'xxl' },
              'relative z-50 inline-block w-full  overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl dark:bg-neutral-900 sm:my-8 sm:align-middle',
            ]"
          >
            <!-- Above, added "relative z-50", removed "transform transition-all" -->
            <!-- For some reason when using fixed popover with the VCalendar package positioning it's not shown correctly in the modal
        probably some issue with the TailwindCSS transform class which is causing the issue as if the transform class
        is removed from the modal works fine, but without fixed positioning, works good
        check in future, perhaps related to this issue: https://github.com/nathanreyes/v-calendar/issues/1058 -->
            <div class="absolute top-1 right-2 pt-4 pr-4">
              <button
                type="button"
                class="rounded-md bg-white text-neutral-400 hover:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:bg-neutral-800"
                @click="hide"
              >
                <icon icon="X" class="h-6 w-6" />
              </button>
            </div>
            <div
              class="bg-white px-4 pt-5 pb-4 dark:bg-neutral-900 sm:p-6 sm:pb-4"
            >
              <div class="space-y-1">
                <DialogTitle
                  class="text-lg font-medium leading-6 text-neutral-700 dark:text-white"
                >
                  {{ title }}
                </DialogTitle>
                <p
                  class="text-sm text-neutral-500 dark:text-neutral-300"
                  v-if="description"
                  v-text="description"
                ></p>
              </div>

              <div class="mt-5">
                <slot></slot>
              </div>
            </div>
            <div
              v-if="!hideFooter"
              class="bg-neutral-50 px-4 py-3 dark:bg-neutral-800 sm:px-6"
            >
              <slot name="modal-footer" :cancel="hide">
                <div class="flex justify-end space-x-3">
                  <slot name="modal-cancel" :cancel="hide" :title="cancelTitle">
                    <i-button
                      :variant="cancelVariant"
                      :disabled="computedCancelDisabled"
                      :size="cancelSize"
                      @click="hide"
                    >
                      {{ cancelTitle }}
                    </i-button>
                  </slot>
                  <slot name="modal-ok" :title="okTitle">
                    <i-button
                      :variant="okVariant"
                      @click="handleOkClick"
                      :type="form ? 'submit' : 'button'"
                      :size="okSize"
                      class="ml-4"
                      :loading="okLoading"
                      :disabled="computedOkDisabled"
                      >{{ okTitle }}</i-button
                    >
                  </slot>
                </div>
              </slot>
            </div>
          </component>
        </TransitionChild>
      </div>
      <i-confirmation-dialog v-if="$root.dialog" :dialog="$root.dialog" />
    </Dialog>
  </TransitionRoot>
</template>
<script>
import Dialog from './Dialog'
import { passiveEventArg } from '@/utils'
export default {
  mixins: [Dialog],
  props: {
    size: {
      type: String,
      default: 'md',
      validator(value) {
        return ['sm', 'md', 'lg', 'xl', 'xxl'].includes(value)
      },
    },
  },
  data: () => ({
    trackingOverlayHeight: false,
  }),
  watch: {
    localVisible: function (newVal) {
      // Tailwind UI dialog does not handle edge cases like scrolling the
      // dialog content and from the examples the overlay is with fixed position
      // and hides the scroll of the inner dialog content
      // For this reason, we changed the overlay to absolute positioning and
      // will automatically fix/adjust the height when scrolling the inner content
      if (newVal) {
        this.$nextTick(this.addOverlayEventListeners)
        this.trackingOverlayHeight = true
      } else {
        this.removeOverlayEventListeners()
      }
    },
  },
  methods: {
    /**
     * Fix the overlay height based on the dialog-inner height
     *
     * @return {Vod}
     */
    adjustOverlayHeight() {
      const dialogInner = document.querySelector('.dialog-inner')

      document.querySelector('.dialog-overlay').style.height =
        dialogInner.offsetHeight + 'px'
    },

    /**
     * Add the overlay event listener
     *
     * @return {Void}
     */
    addOverlayEventListeners() {
      const dialog = document.querySelector('.dialog')
      dialog.addEventListener(
        'scroll',
        this.adjustOverlayHeight,
        passiveEventArg()
      )
      window.addEventListener('resize', this.adjustOverlayHeight)
    },

    /**
     * Remove the overlay event listener
     *
     * @return {Void}
     */
    removeOverlayEventListeners() {
      const dialog = document.querySelector('.dialog')
      // Perhaps on unmounted, in this case
      // the event will be already removed
      if (dialog) {
        window.removeEventListener('resize', this.adjustOverlayHeight)

        dialog.removeEventListener(
          'scroll',
          this.adjustOverlayHeight,
          passiveEventArg()
        )
      }
    },
  },
  beforeUnmount() {
    if (this.trackingOverlayHeight) {
      this.removeOverlayEventListeners()
    }
  },
}
</script>
