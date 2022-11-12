<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <i-custom-select
      :input-id="fieldId"
      ref="select"
      v-model="value"
      :disabled="isReadonly"
      :filterable="filterable"
      @search="onSearch"
      :options="options"
      :create-option-provider="createOption"
      :name="field.attribute"
      :label="field.labelKey"
      v-bind="field.attributes"
    >
      <template #no-options>{{ noOptionsText }}</template>
    </i-custom-select>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import InteractsWithOptions from '@/mixins/InteractsWithOptions'
import { isValueEmpty } from '@/utils'
import debounce from 'lodash/debounce'
import find from 'lodash/find'
import cloneDeep from 'lodash/cloneDeep'
import isEqual from 'lodash/isEqual'
import isObject from 'lodash/isObject'

export default {
  mixins: [FormField, InteractsWithOptions],
  data: () => ({
    performedAsyncSearch: false,
    minimumAsyncCharacters: 2,
    totalAsyncSearchCharacters: 0,
    minimumAsyncCharactersRequirement: false,
  }),
  computed: {
    /**
     * Check whether the field is dirty
     *
     * @return {Boolean}
     */
    isDirty() {
      // Check for null and "" values
      if (isValueEmpty(this.value) && isValueEmpty(this.realInitialValue)) {
        return false
      }

      if (isValueEmpty(this.value)) {
        return !isValueEmpty(this.realInitialValue)
      } else if (isValueEmpty(this.realInitialValue)) {
        return !isValueEmpty(this.value)
      } else if (!isObject(this.realInitialValue)) {
        return !isEqual(this.value[this.field.valueKey], this.realInitialValue)
      }

      return !isEqual(
        this.value[this.field.valueKey],
        this.realInitialValue[this.field.valueKey]
      )
    },

    /**
     * Indicates whether the field options are filterable
     *
     * @return {Boolean}
     */
    filterable() {
      return this.isAsync ? false : true
    },

    /**
     * No options text for the select field
     *
     * NOTE: Uses i18n directly because in ActionDialog.vue is not recognized, not sure why
     *
     * @return {String}
     */
    noOptionsText() {
      // Only for async
      if (this.isAsync && this.minimumAsyncCharactersRequirement) {
        return this.$t('app.type_more_to_search', {
          characters:
            this.minimumAsyncCharacters - this.totalAsyncSearchCharacters,
        })
      }

      if (!this.isAsync || (this.isAsync && this.performedAsyncSearch)) {
        return this.$t('app.no_search_results')
      }

      // This is shown only the first time user clicked on the select (only for async)
      return this.$t('app.type_to_search')
    },

    /**
     * Check whether the field is async
     *
     * @return {Boolean}
     */
    isAsync() {
      return this.field.asyncUrl
    },
  },
  methods: {
    /**
     * Fill the form value
     *
     * @param  {Object} form
     *
     * @return {Void}
     */
    fill(form) {
      if (this.field.asObjectValue) {
        form.fill(this.field.attribute, this.value)
        return
      }

      form.fill(
        this.field.attribute,
        (this.value && this.value[this.field.valueKey]) || null
      )
    },

    /**
     * Create new options (not used currently - useful if tags field is added)
     *
     * @param  {String} value
     *
     * @return {Object}
     */
    createOption(value) {
      return {
        [this.field.valueKey]: value,
        [this.field.labelKey]: value,
      }
    },

    /**
     * On select search
     *
     * @param  {String} search
     * @param  {Function} loading
     *
     * @return {Void}
     */
    onSearch(search, loading) {
      this.asyncSearch(search, loading)
    },

    /**
     * Perform async search
     *
     * @param  {String} q
     * @param  {Function} loading
     *
     * @return {Void}
     */
    asyncSearch: debounce(function (q, loading) {
      const totalCharacters = q.length

      this.totalAsyncSearchCharacters = totalCharacters

      if (this.filterable || totalCharacters < this.minimumAsyncCharacters) {
        this.minimumAsyncCharactersRequirement = true

        return q
      }

      this.minimumAsyncCharactersRequirement = false

      if (q == '') {
        this.options = []
        return
      }

      this.httpRequest(q, loading)
    }, 400),

    /**
     * Perform async HTTP request
     *
     * @param  {String} q
     * @param  {Function} loading
     *
     * @return {Void}
     */
    async httpRequest(q, loading) {
      loading(true)

      let { data } = await Innoclapps.request().get(this.field.asyncUrl, {
        params: {
          q: q,
        },
      })

      this.options = data
      this.performedAsyncSearch = true
      loading(false)
    },

    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      this.value = this.prepareValue(this.field.value)
    },

    /**
     * Prepare the field internal value
     *
     * @param  {mixed} value
     *
     * @return {Object}
     */
    prepareValue(value) {
      // Has value and the value is not object
      // But the options are multi dimensional array
      // In this case, we must set the value as object so it can be populated within the select
      if (
        value &&
        typeof value != 'object' &&
        this.options.length > 0 &&
        typeof this.options[0] === 'object'
      ) {
        return find(this.options, [this.field.valueKey, value])
      } else {
        return value
      }
    },

    /**
     * Handle change
     *
     * @param  {mixed} value
     *
     * @return {Void}
     */
    handleChange(value) {
      let prepared = this.prepareValue(value)
      this.value = prepared
      this.realInitialValue = cloneDeep(prepared)
    },

    /**
     * Configure the value watcher
     *
     * Watch for changes on the actual field value
     *
     * @return {Void}
     */
    configureValueWatcher() {
      this.$watch('field.value', function (newVal) {
        this.handleChange(newVal)
      })
    },
  },
  created() {
    this.getOptions(this.field).then(options =>
      this.setOptions(options, options => {
        this.initialize()
        this.$nextTick(this.configureValueWatcher)
      })
    )
  },
}
</script>
