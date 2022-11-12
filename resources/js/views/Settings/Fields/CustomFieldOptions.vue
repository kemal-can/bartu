<template>
  <i-form-group>
    <div class="flex">
      <i-form-label
        class="grow text-neutral-900 dark:text-neutral-100"
        :label="$t('fields.options')"
        required
      />
      <div>
        <i-button-icon icon="Plus" @click="newOption" />
      </div>
    </div>
    <i-alert :show="form.options.length === 0" variant="info" class="mt-2">
      <i18n-t
        scope="global"
        :keypath="'fields.custom.create_option_icon'"
        tag="div"
        class="flex"
      >
        <template #icon>
          <icon icon="Plus" class="h-5 w-5 cursor-pointer" @click="newOption" />
        </template>
      </i18n-t>
    </i-alert>

    <div
      class="mt-3 flex rounded-md shadow-sm"
      v-for="(option, index) in form.options"
      :key="index"
    >
      <div class="relative flex grow items-stretch focus-within:z-10">
        <div
          class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm dark:text-white"
          v-if="option.id"
        >
          {{ $t('app.id') + ': ' + option.id }}
        </div>
        <i-form-input
          v-model="form.options[index].name"
          @keydown.enter.prevent.stop="newOption"
          :ref="'option-' + (index + 1)"
          :rounded="false"
          :class="['rounded-l-md', { 'pl-14': option.id }]"
          @keydown="form.errors.clear('options')"
        />
      </div>
      <i-button-close
        :rounded="false"
        @click="removeOption(index)"
        variant="danger"
        class="relative -ml-px rounded-r-md"
      />
    </div>

    <form-error :form="form" field="options" />
  </i-form-group>
</template>
<script>
export default {
  props: {
    form: {
      required: true,
      type: Object,
    },
  },
  methods: {
    /**
     * Create new field option
     *
     * @return {Void}
     */
    newOption() {
      this.form.options.push({
        name: null,
      })

      // Focus the last option
      this.$nextTick(() => {
        this.$refs['option-' + this.form.options.length][0].focus()
      })
    },

    /**
     * Remove field option
     *
     * @param  {Int} index
     *
     * @return {Void}
     */
    removeOption(index) {
      let option = this.form.options[index]
      if (option.id) {
        this.$dialog.confirm().then(() => this.form.options.splice(index, 1))
      } else {
        this.form.options.splice(index, 1)
      }
    },
  },
}
</script>
