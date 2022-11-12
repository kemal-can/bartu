<template>
  <form-field-group
    :field="field"
    :form="form"
    :field-id="fieldId"
    class="relative"
  >
    <i-form-label :for="fieldId + '-due-date'" class="mb-1 block sm:hidden">
      {{ $t('activity.due_date') }}
    </i-form-label>
    <div class="flex items-center space-x-1">
      <date-picker
        v-model="dueDate"
        :id="fieldId + '-due-date'"
        :name="field.attribute"
        :disabled="isReadonly"
        :with-icon="false"
        :required="field.isRequired"
        v-bind="field.attributes"
      />
      <i-form-input-dropdown
        :items="inputDropdownItems"
        @blur="maybeSetEndTimeToEmpty"
        @cleared="maybeSetEndTimeToEmpty"
        :placeholder="timeFormatForMoment"
        :input-id="fieldId + '-due-time'"
        max-height="300px"
        :class="{
          'border-danger-500 ring-danger-500 focus:border-danger-500 focus:ring-danger-500':
            showDueTimeWarning,
        }"
        v-model="dueTime"
      />
      <div
        class="absolute -right-3 hidden text-neutral-900 dark:text-neutral-300 md:block"
      >
        -
      </div>
    </div>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import isEqual from 'lodash/isEqual'
import { timelineLabels } from '@/utils'
export default {
  mixins: [FormField],
  data() {
    return {
      endDate: this.appMoment().format('YYYY-MM-DD'),
      endTime: '',
      dueDate: '',
      dueTime: '',
      settingDueDateInitialValue: false,
    }
  },
  watch: {
    dueDate: function (newVal, oldVal) {
      Innoclapps.$emit('due-date-changed', {
        newVal: newVal,
        oldVal: oldVal,
        isInitialValue: this.settingDueDateInitialValue,
      })
    },
    dueTime: function (newVal, oldVal) {
      Innoclapps.$emit('due-time-changed', {
        newVal: newVal,
        oldVal: oldVal,
      })

      if (!this.endTime || this.endTime === this.dueDate) {
        return
      }

      let newDueDate = this.utcMomentInstanceFromDateAndDropdownTime(
        this.dueDate,
        newVal
      )
      let currentEndDate = this.utcMomentInstanceFromDateAndDropdownTime(
        this.endDate,
        this.endTime
      )
      let oldDueDate = this.utcMomentInstanceFromDateAndDropdownTime(
        this.dueDate,
        oldVal
      )

      this.invokeUpdateEndTimeValueEvent(
        this.dateFromAppTimezone(
          newDueDate
            .add(currentEndDate.diff(oldDueDate, 'minutes'), 'minutes')
            .format('YYYY-MM-DD HH:mm:ss'),
          this.timeFormatForMoment
        )
      )
    },
  },
  computed: {
    /**
     * Check whether the field is dirty
     *
     * @return {Boolean}
     */
    isDirty() {
      return !isEqual(this.realInitialValue, this.getValues())
    },

    /**
     * Indicates whether to show the due time field warning
     *
     * @return {Boolean}
     */
    showDueTimeWarning() {
      return this.endTime && !this.dueTime
    },

    /**
     * Get the dropdown items for the input dropdown
     *
     * @return {Array}
     */
    inputDropdownItems() {
      return timelineLabels('00:00', 15, 'm', this.timeFormatForMoment)
    },

    /**
     * Get the formatted value
     *
     * @return {String}
     */
    timeFormatForMoment() {
      return moment().PHPconvertFormat(this.currentTimeFormat)
    },
  },
  methods: {
    /**
     * Create UTC moment instance from the given date and dropdown time (already formatted)
     *
     * @return {Moment}
     */
    utcMomentInstanceFromDateAndDropdownTime(date, time) {
      return moment.utc(
        this.dateToAppTimezone(
          moment(
            date +
              ' ' +
              moment(
                date + ' ' + time,
                'YYYY-MM-DD ' + this.timeFormatForMoment
              ).format('HH:mm')
          ).format('YYYY-MM-DD HH:mm:ss')
        )
      )
    },

    /**
     * Invoke update end time event
     *
     * @param  {mixed} value
     *
     * @return {Void}
     */
    invokeUpdateEndTimeValueEvent(value) {
      Innoclapps.$emit('update-end-time', value)
    },

    /**
     * Provide a function that fills a passed form object with the
     *
     * field's internal value attribute
     */
    fill(form) {
      const values = this.getValues()
      form.fill('due_date', values.date)
      form.fill('due_time', values.time || null)
    },

    /**
     * Get the actual field values for storage in UTC format
     *
     * @return {Object}
     */
    getValues() {
      if (this.dueTime) {
        const UTCInstance = this.utcMomentInstanceFromDateAndDropdownTime(
          this.dueDate,
          this.dueTime
        )

        return {
          date: UTCInstance.format('YYYY-MM-DD'),
          time: UTCInstance.format('HH:mm'),
        }
      }

      return {
        date: this.dueDate,
        time: '',
      }
    },

    /**
     * Set the dates
     *
     * @param {Object|null} value
     */
    setDates(value) {
      if (!value) {
        return
      }

      if (value.time) {
        this.dueDate = this.dateFromAppTimezone(
          value.date + ' ' + value.time,
          'YYYY-MM-DD'
        )
        this.dueTime = this.dateFromAppTimezone(
          value.date + ' ' + value.time,
          this.timeFormatForMoment
        )
      } else {
        this.dueDate = value.date
        this.endDate = ''
      }
    },

    /**
     * Handle field value changed
     *
     * @param  {String} value
     *
     * @return {Void}
     */
    handleChange(value) {
      this.value = value
      this.realInitialValue = value
      this.setDates(value)
    },

    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      this.settingDueDateInitialValue = true

      if (!this.field.value) {
        // https://stackoverflow.com/questions/17691202/round-up-round-down-a-momentjs-moment-to-nearest-minute
        const UTCInstance = this.appMoment().add(1, 'hour').startOf('hour')

        this.dueTime = this.dateFromAppTimezone(
          UTCInstance.format('YYYY-MM-DD HH:mm:ss'),
          this.timeFormatForMoment
        )

        this.dueDate = this.dateFromAppTimezone(
          UTCInstance.format('YYYY-MM-DD HH:mm:ss'),
          'YYYY-MM-DD'
        )

        this.value = {
          date: this.dueDate,
          time: UTCInstance.format('HH:mm'), // utc
        }

        this.$nextTick(() => (this.settingDueDateInitialValue = false))

        return
      }

      this.value = this.field.value

      this.setDates(this.value)

      this.$nextTick(() => (this.settingDueDateInitialValue = false))
    },

    /**
     * If we don't have due time we will set the end time to empty
     *
     * @return {Void}
     */
    async maybeSetEndTimeToEmpty() {
      await this.$nextTick()

      if (!this.dueTime && this.endTime) {
        this.invokeUpdateEndTimeValueEvent('')
      }
    },
  },
  created() {
    this.field.label = ''
    Innoclapps.$on('end-time-changed', event => (this.endTime = event.newVal))
    Innoclapps.$on('end-date-changed', event => (this.endDate = event.newVal))
  },
  unmounted() {
    Innoclapps.$off('end-time-changed')
    Innoclapps.$off('end-date-changed')
  },
}
</script>
<style>
input[name='due_date-due-time'] {
  width: 116px !important;
}
</style>
