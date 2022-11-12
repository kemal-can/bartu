<template>
  <i-modal
    :id="modalId"
    :title="computedTitle"
    :ok-loading="exportInProgress"
    :ok-disabled="exportInProgress"
    :ok-title="$t('app.export.export')"
    @ok="performExport"
  >
    <i-form-group :label="$t('app.export.type')">
      <i-form-select v-model="form.type">
        <option value="csv">CSV</option>
        <option value="xls">XLS</option>
        <option value="xlsx">XLSX</option>
      </i-form-select>
    </i-form-group>
    <i-form-group
      class="space-y-1"
      :label="$t('dates.range') + ' (' + $t('app.creation_date') + ')'"
    >
      <i-form-radio
        v-for="period in periods"
        :key="period.text"
        v-model="form.period"
        :value="period.value"
        name="period"
        :id="period.id"
        :label="period.text"
      />
    </i-form-group>
    <i-form-group v-if="isCustomOptionSelected">
      <div class="sm:ml-6">
        <i-form-label
          label="Select Range"
          for="custom-period-start"
          class="mb-1"
        />

        <date-picker
          v-model="form.customPeriod"
          name="custom-period"
          id="custom-period"
          is-range
        />
      </div>
    </i-form-group>
    <div
      v-show="canUseFilterForExport"
      class="mt-5 rounded-md border border-neutral-200 bg-neutral-50 p-3 dark:border-neutral-500 dark:bg-neutral-700"
    >
      <i-form-checkbox
        v-model:checked="shouldApplyFilters"
        :label="$t('app.export.apply_filters')"
      />
    </div>
  </i-modal>
</template>
<script>
import FileDownload from 'js-file-download'
import Form from '@/components/Form/Form'

export default {
  props: {
    resourceName: String,
    filtersView: String,
    title: String,
    urlPath: {
      required: true,
      type: String,
    },
    modalId: {
      default: 'export-modal',
      type: String,
    },
  },
  data() {
    return {
      shouldApplyFilters: true,
      exportInProgress: false,
      form: new Form({
        period: 'last_7_days',
        type: 'csv',
        customPeriod: {
          start: this.appMoment().startOf('month').format('YYYY-MM-DD'),
          end: this.appMoment().format('YYYY-MM-DD'),
        },
      }),
      periods: [
        {
          text: this.$t('dates.today'),
          value: 'today',
        },
        {
          text: this.$t('dates.periods.7_days'),
          value: 'last_7_days',
        },
        {
          text: this.$t('dates.this_month'),
          value: 'this_month',
        },
        {
          text: this.$t('dates.last_month'),
          value: 'last_month',
        },
        {
          text: this.$t('app.all'),
          value: 'all',
          id: 'all',
        },
        {
          text: this.$t('dates.custom'),
          value: 'custom',
          id: 'custom',
        },
      ],
    }
  },
  computed: {
    computedTitle() {
      return this.title || this.$t('app.export.export')
    },

    /**
     * Indicates whether a custom period option is selected
     *
     * @return {Boolean}
     */
    isCustomOptionSelected() {
      return this.form.period == 'custom'
    },

    /**
     * Indicates whether the filters can be used for export
     *
     * @return {Void}
     */
    canUseFilterForExport() {
      return this.hasRulesApplied && this.hasValidFilterRules
    },

    /**
     * Indicates whether the resource has applied valid rules
     *
     * @return {Boolean}
     */
    hasRulesApplied() {
      if (!this.resourceName) {
        return false
      }

      return this.$store.getters['filters/hasRulesApplied'](
        this.resourceName,
        this.filtersView
      )
    },

    /**
     * Indicates whether the resource has aplied valid rules
     *
     * @return {Boolean}
     */
    hasValidFilterRules() {
      if (!this.resourceName) {
        return false
      }

      return this.$store.getters['filters/rulesAreValid'](
        this.resourceName,
        this.filtersView
      )
    },

    /**
     * Hold the all applied resource filter rules
     *
     * @return {Object}
     */
    appliedFilterRules() {
      if (!this.resourceName) {
        return {}
      }

      return this.$store.getters['filters/getBuilderRules'](
        this.resourceName,
        this.filtersView
      )
    },
  },
  methods: {
    /**
     * Get the export file name from the headers
     * @param  {Object} response
     * @return {String}
     */
    getFileNameFromResponseHeaders(response) {
      return response.headers['content-disposition'].split('filename=')[1]
    },

    /**
     * Perform export
     *
     * @return {Void}
     */
    performExport() {
      this.exportInProgress = true

      Innoclapps.request()
        .post(
          this.urlPath,
          {
            period: !this.isCustomOptionSelected
              ? this.form.period === 'all'
                ? null
                : this.form.period
              : this.form.customPeriod,
            type: this.form.type,
            filters:
              this.shouldApplyFilters && this.canUseFilterForExport
                ? this.appliedFilterRules
                : null,
          },
          {
            responseType: 'blob',
          }
        )
        .then(response => {
          FileDownload(
            response.data,
            this.getFileNameFromResponseHeaders(response)
          )
        })
        .finally(() => (this.exportInProgress = false))
    },
  },
}
</script>
