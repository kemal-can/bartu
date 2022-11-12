<template>
  <div class="sm:flex sm:items-start">
    <div
      v-if="!hasFields"
      class="mx-auto flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-danger-100 sm:mx-0 sm:h-10 sm:w-10"
    >
      <icon icon="Exclamation" class="h-6 w-6 text-danger-600" />
    </div>
    <div
      :class="[
        { 'text-center sm:ml-4': !hasFields },
        'mt-3 w-full sm:mt-0 sm:text-left',
      ]"
    >
      <DialogTitle
        as="h3"
        :class="{ 'mt-2': !showMessage }"
        class="text-lg font-medium leading-6 text-neutral-600 dark:text-white"
      >
        {{ dialog.title }}
      </DialogTitle>

      <div class="mt-2" v-if="showMessage">
        <p class="text-sm text-neutral-500 dark:text-neutral-300">
          {{ dialog.message }}
        </p>
      </div>
      <fields-generator
        :form="form"
        v-if="hasFields"
        class="mt-4"
        :is-floating="true"
        view="internal"
        :fields="dialog.fields"
      />
    </div>
  </div>
  <div class="mt-5 sm:mt-4 sm:flex" :class="{ 'sm:ml-10 sm:pl-4': !hasFields }">
    <i-button
      :variant="dialog.action.destroyable ? 'danger' : 'secondary'"
      :disabled="executing"
      :loading="executing"
      class="w-full sm:w-auto"
      @click="runAction"
    >
      {{ $t('app.confirm') }}
    </i-button>
    <i-button
      variant="white"
      class="mt-3 w-full sm:mt-0 sm:ml-3 sm:w-auto"
      @click="cancel"
    >
      {{ $t('app.cancel') }}
    </i-button>
  </div>
</template>
<script>
const qs = require('qs')
import Form from '@/components/Form/Form'

export default {
  props: {
    close: Function,
    cancel: Function,
    dialog: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    executing: false,
    form: new Form({
      ids: [],
    }),
  }),
  computed: {
    /**
     * Indicates whether to show the action message
     *
     * @return {Boolean}
     */
    showMessage() {
      return !this.hasFields && this.dialog.message
    },

    /**
     * Indicates whether the action has fields
     *
     * @return {Boolean}
     */
    hasFields() {
      return this.dialog.fields && this.dialog.fields.all().length > 0
    },
  },
  methods: {
    /**
     * Handle the confirmation
     *
     * @return {Void}
     */
    runAction() {
      this.hasFields && this.dialog.fields.fill(this.form)
      this.form.fill('ids', this.dialog.ids)
      this.executing = true

      this.form
        .post(
          `${this.dialog.endpoint}?${qs.stringify(this.dialog.queryString)}`
        )
        .then(data => {
          this.dialog.resolve({
            form: this.form,
            response: data,
          })
          this.close()
        })
        .finally(() => (this.executing = false))
    },
  },
}
</script>
