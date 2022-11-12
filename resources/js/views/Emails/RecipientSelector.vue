<template>
  <div class="flex items-center" :class="type">
    <div class="w-14">
      <i-form-label :label="label" :for="type" />
    </div>
    <div class="recipient grow">
      <recipient-select
        :input-id="type"
        :options="options"
        :placeholder="$t('inbox.search_recipients')"
        @option:selected="handleRecipientSelectedEvent"
        @search="asyncSearch"
        v-model="form[type]"
      >
        <!-- Searched emails -->
        <template #option="option">
          {{ option.name }} {{ option.address }}
        </template>
        <!-- Selected -->
        <template
          #selected-option-container="{ option, disabled, deselect, multiple }"
        >
          <span
            :class="[
              'mr-2 inline-flex rounded-md bg-neutral-100 px-2 dark:bg-neutral-500 dark:text-white',
              {
                'border border-danger-500': !validateAddress(option.address),
              },
            ]"
            v-bind:key="option.index"
          >
            <span v-if="!option.name">
              {{ option.address }}
            </span>
            <span v-else>
              <span v-i-tooltip="option.address">{{ option.name }}</span>
            </span>
            <button
              v-if="multiple"
              :disabled="disabled"
              @click.prevent.stop="removeRecipient(deselect, option)"
              type="button"
              class="ml-1 text-neutral-400 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
              title="Remove recipient"
              aria-label="Remove recipient"
            >
              <icon icon="X" class="h-4 w-4" />
            </button>
          </span>
        </template>
      </recipient-select>
      <form-error :form="form" :field="type" />
    </div>
    <slot name="after"></slot>
  </div>
</template>
<script>
import RecipientSelect from './RecipientSelectField'
import debounce from 'lodash/debounce'
const validator = require('email-validator')
import { CancelToken } from '@/services/HTTP'
export default {
  emits: ['recipient-removed', 'recipient-selected'],
  components: { RecipientSelect },
  props: {
    label: String,
    type: {
      type: String,
      required: true,
    },
    form: {
      required: true,
    },
  },
  data: () => ({
    options: [],
    cancelToken: null,
  }),
  methods: {
    /**
     * Focus the recipient field
     *
     * @return {Void}
     */
    focus() {
      document.getElementById(this.type).focus()
    },

    /**
     * Handle the receipient selected event
     *
     * @param  {Array} records
     *
     * @return {Void}
     */
    handleRecipientSelectedEvent(records) {
      this.$emit('recipient-selected', records)
    },

    /**
     * Handle the recipient removed event
     *
     * @param  {Function} deselect
     * @param  {Object} option
     *
     * @return {Void}
     */
    removeRecipient(deselect, option) {
      deselect(option)
      this.$emit('recipient-removed', option)
    },

    /**
     * Perform search
     *
     * @param  {[type]}   q
     * @param  {Function} loading
     * @return {Null|String}
     */
    asyncSearch: debounce(function (q, loading) {
      if (!q) {
        return
      }

      if (this.cancelToken) {
        this.cancelToken()
      }

      loading(true)

      Innoclapps.request()
        .get('/search/email-address', this.getRequestQueryString(q))
        .then(({ data }) => {
          if (data) {
            let opts = []
            data.forEach(result => opts.push(...result.data))
            this.$nextTick(() => (this.options = opts))
          }
        })
        .finally(() => loading(false))
    }, 400),

    /**
     * Get the search request params
     *
     * @param  {string} q
     *
     * @return {Object}
     */
    getRequestQueryString(q) {
      return {
        params: {
          q: q,
        },
        cancelToken: new CancelToken(token => (this.cancelToken = token)),
      }
    },

    /**
     * Custom email address validator
     *
     * @param  {String} address
     *
     * @return {Boolean}
     */
    validateAddress(address) {
      return validator.validate(address)
    },
  },
}
</script>
