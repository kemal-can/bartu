<template>
  <TransitionRoot as="template" :show="open">
    <Dialog
      as="div"
      static
      class="dialog fixed inset-0 overflow-y-auto"
      :open="open"
    >
      <div
        class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0"
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
            class="fixed inset-0 bg-neutral-500/75 transition-opacity dark:bg-neutral-700/90"
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
          <div
            class="inline-block w-full transform rounded-lg bg-white px-4 pt-5 pb-4 text-left align-bottom shadow-xl transition-all dark:bg-neutral-800 sm:my-8 sm:max-w-lg sm:p-6 sm:align-middle"
          >
            <template v-if="!dialog.component">
              <div class="sm:flex sm:items-start">
                <div
                  :class="[
                    'mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10',
                    dialog.iconWrapperColorClass
                      ? dialog.iconWrapperColorClass
                      : 'bg-danger-100',
                  ]"
                >
                  <icon
                    :icon="icon"
                    :class="[
                      'h-6 w-6',
                      dialog.iconColorClass
                        ? dialog.iconColorClass
                        : 'text-danger-600',
                    ]"
                  />
                </div>
                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                  <DialogTitle
                    as="h3"
                    :class="{ 'mt-2': !dialog.message }"
                    v-if="title"
                    class="text-lg font-medium leading-6 text-neutral-600 dark:text-white"
                  >
                    <span v-if="dialog.html" v-html="title"></span>
                    <span v-else v-text="title"></span>
                  </DialogTitle>

                  <div
                    :class="{ 'mt-2': Boolean(title) }"
                    v-if="dialog.message"
                  >
                    <p class="text-sm text-neutral-500 dark:text-neutral-300">
                      <span v-if="dialog.html" v-html="dialog.message"></span>
                      <span v-else v-text="dialog.message"></span>
                    </p>
                  </div>
                </div>
              </div>

              <div class="mt-5 sm:mt-4 sm:ml-10 sm:flex sm:pl-4">
                <i-button
                  :variant="confirmVariant"
                  class="w-full sm:w-auto"
                  @click="confirm"
                >
                  {{ confirmText }}
                </i-button>
                <i-button
                  :variant="cancelVariant"
                  class="mt-3 w-full sm:mt-0 sm:ml-3 sm:w-auto"
                  @click="cancel"
                >
                  {{ $t('app.cancel') }}
                </i-button>
              </div>
            </template>
            <component
              :is="dialog.component"
              :close="close"
              :cancel="cancel"
              :dialog="dialog"
              v-else
            />
          </div>
        </TransitionChild>
      </div>
    </Dialog>
  </TransitionRoot>
</template>
<script>
export default {
  name: 'IConfirmationDialog',
  props: {
    dialog: {
      required: true,
      type: Object,
    },
  },
  data: () => ({
    open: true,
  }),
  computed: {
    title() {
      if (this.dialog.title === false) {
        return null
      }

      return this.dialog.title || this.$t('actions.confirmation_message')
    },
    icon() {
      return this.dialog.icon || 'Exclamation'
    },
    confirmVariant() {
      return this.dialog.confirmVariant || 'danger'
    },
    confirmText() {
      return this.dialog.confirmText || this.$t('app.confirm')
    },
    cancelVariant() {
      return this.dialog.cancelVariant || 'white'
    },
  },
  methods: {
    close() {
      this.open = false
    },
    confirm() {
      this.dialog.resolve()
      this.close()
    },
    cancel() {
      this.dialog.reject()
      this.close()
    },
  },
}
</script>
