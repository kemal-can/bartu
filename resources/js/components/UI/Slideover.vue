<template>
  <TransitionRoot appear as="template" :show="localVisible">
    <Dialog
      as="div"
      static
      :initial-focus="initialFocus"
      class="dialog fixed inset-0 overflow-hidden"
      @close="dialogClosedEvent"
      :open="localVisible"
    >
      <div class="absolute inset-0 overflow-hidden">
        <DialogOverlay
          class="absolute inset-0 bg-neutral-500/75 transition-opacity dark:bg-neutral-700/90"
        />

        <div class="fixed inset-y-0 right-0 flex max-w-full pl-10">
          <TransitionChild
            as="template"
            enter="transition ease-in-out duration-400 sm:duration-600"
            enter-from="translate-x-full"
            enter-to="translate-x-0"
            leave="transition ease-in-out duration-400 sm:duration-600"
            leave-from="translate-x-0"
            leave-to="translate-x-full"
          >
            <component
              :is="form ? 'form' : 'div'"
              @submit.prevent="$emit('submit')"
              :novalidate="form ? true : undefined"
              @keydown.passive="$emit('keydown', $event)"
              class="w-screen max-w-2xl"
            >
              <div
                class="flex h-full flex-col divide-y divide-neutral-200 bg-white shadow-xl dark:divide-neutral-700 dark:bg-neutral-900"
              >
                <div
                  class="flex min-h-0 flex-1 flex-col overflow-y-scroll py-6"
                >
                  <div class="px-4 sm:px-6" v-if="!hideHeader">
                    <slot name="modal-header" :cancel="hide">
                      <div class="flex items-start justify-between">
                        <div class="space-y-1">
                          <DialogTitle
                            class="text-lg font-medium text-neutral-700 dark:text-white"
                          >
                            {{ title }}
                          </DialogTitle>
                          <p
                            class="text-sm text-neutral-500 dark:text-neutral-300"
                            v-if="description"
                            v-text="description"
                          ></p>
                        </div>
                        <div class="ml-3 flex h-7 items-center">
                          <button
                            type="button"
                            class="rounded-md bg-white text-neutral-400 hover:text-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:bg-neutral-800"
                            v-if="!hideHeaderClose"
                            @click="hide"
                          >
                            <icon icon="X" class="h-6 w-6" />
                          </button>
                        </div>
                      </div>
                    </slot>
                  </div>
                  <div class="relative mt-8 flex-1 px-4 sm:px-6">
                    <slot></slot>
                  </div>
                </div>
                <div
                  class="shrink-0 px-4 py-4 dark:bg-neutral-800"
                  v-if="!hideFooter"
                >
                  <slot name="modal-footer" :cancel="hide">
                    <div
                      class="flex flex-wrap justify-end space-x-3 sm:flex-nowrap"
                    >
                      <slot
                        name="modal-cancel"
                        :cancel="hide"
                        :title="cancelTitle"
                      >
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
                          :size="okSize"
                          :type="form ? 'submit' : 'button'"
                          :loading="okLoading"
                          :disabled="computedOkDisabled"
                          >{{ okTitle }}</i-button
                        >
                      </slot>
                    </div>
                  </slot>
                </div>
              </div>
            </component>
          </TransitionChild>
        </div>
      </div>
      <i-confirmation-dialog v-if="$root.dialog" :dialog="$root.dialog" />
    </Dialog>
  </TransitionRoot>
</template>
<script>
import Dialog from './Dialog'
export default {
  mixins: [Dialog],
}
</script>
