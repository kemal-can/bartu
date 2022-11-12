<template>
  <form-field-group :field="field" :form="form" :field-id="fieldId">
    <i-form-label :for="fieldId + '-end-date'" class="mb-1 block sm:hidden">
      {{ $t('activity.end_date') }}
    </i-form-label>
    <div class="flex items-center space-x-1">
      <i-form-input-dropdown
        :items="inputDropdownItems"
        :input-id="fieldId + '-end-time'"
        v-model="endTime"
        :placeholder="timeFormatForMoment"
        :disabled="!dueTime"
        max-height="300px"
        :class="{
          'border-danger-500 ring-danger-500 focus:border-danger-500 focus:ring-danger-500':
            showEndTimeWarning,
        }"
        @shown="handleTimeIsShown"
      />
      <date-picker
        v-model="endDate"
        :required="field.isRequired"
        :min-date="dueDate"
        :with-icon="false"
        :id="fieldId + '-end-date'"
        :name="field.attribute"
        :disabled="isReadonly"
        v-bind="field.attributes"
      />
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
    }
  },
  watch: {
    endTime: {
      handler: function (newVal, oldVal) {
        Innoclapps.$emit('end-time-changed', {
          newVal: newVal,
          oldVal: oldVal,
        })
      },
    },
    endDate: {
      handler: function (newVal, oldVal) {
        Innoclapps.$emit('end-date-changed', {
          newVal: newVal,
          oldVal: oldVal,
        })
      },
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
     * Indicates whether a warning should be shown for the end time field
     *
     * @return {Boolean}
     */
    showEndTimeWarning() {
      if (!this.endTime && this.dueTime && this.endDate > this.dueDate) {
        return true
      }
    },

    /**
     * Get the dropdown items for the input dropdown
     *
     * @return {Array}
     */
    inputDropdownItems() {
      if (!this.dueTime || this.endDate > this.dueDate) {
        return timelineLabels('00:00', 15, 'm', this.timeFormatForMoment, 23)
      }

      const startIn24HourFormat = moment(
        this.dueDate + ' ' + this.dueTime,
        'YYYY-MM-DD ' + this.timeFormatForMoment
      ).format('HH:mm')

      return timelineLabels(
        startIn24HourFormat,
        15,
        'm',
        this.timeFormatForMoment,
        23
      )
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
     * Provide a function that fills a passed form object with the
     *
     * field's internal value attribute
     */
    fill(form) {
      const values = this.getValues()
      form.fill('end_date', values.date)
      form.fill('end_time', values.time || null)
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
        this.endDate = this.dateFromAppTimezone(
          value.date + ' ' + value.time,
          'YYYY-MM-DD'
        )
        this.endTime = this.dateFromAppTimezone(
          value.date + ' ' + value.time,
          this.timeFormatForMoment
        )

        this.$nextTick(() => {
          if (this.endTime === this.dueTime && this.endDate === this.dueDate) {
            this.endTime = ''
          }
        })
      } else {
        this.endDate = value.date
        this.endTime = ''
      }
    },

    /**
     * Time shown event
     *
     * @return {Void}
     */
    handleTimeIsShown() {
      if (!this.endTime) {
        this.endTime = this.dueTime
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
      this.realInitialValue = this.value

      this.setDates(value)
    },

    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      if (!this.field.value) {
        this.value = {
          date: this.endDate,
          time: this.endTime,
        }
        return
      }

      this.value = this.field.value
      this.setDates(this.value)
    },

    /**
     * Get the actual field values for storage in UTC format
     *
     * @return {Object}
     */
    getValues() {
      if (this.endTime) {
        const UTCInstance = this.utcMomentInstanceFromDateAndDropdownTime(
          this.endDate,
          this.endTime
        )

        return {
          date: UTCInstance.format('YYYY-MM-DD'),
          time: UTCInstance.format('HH:mm'),
        }
      }

      return {
        date: this.endDate,
        time: '',
      }
    },
  },
  created() {
    this.field.label = ''
  },
  mounted() {
    Innoclapps.$on('update-end-time', value => (this.endTime = value))
    Innoclapps.$on('due-time-changed', event => (this.dueTime = event.newVal))
    Innoclapps.$on('due-date-changed', event => {
      this.dueDate = event.newVal

      if (event.newVal != this.endDate && !event.isInitialValue) {
        this.endDate = this.appMoment(event.newVal).format('YYYY-MM-DD')
      } else if (event.isInitialValue && event.dueTime) {
        // Below, we will check if the due date is the same date like
        // our current end date, it can happen e.q. on dates 23:30:00
        // to go to the next day when adding 1 hour, in this case
        // the due date in local time will be 01:00:00 and the endDate will be 12:00:00
        // To test, modify the ActivityDueDateField line:
        // const UTCInstance = this.appMoment().add(1, 'hour').startOf('hour')
        // to
        // const UTCInstance = this.appMoment('23:00:00').add(1, 'hour').startOf('hour')
        const utcDueDate = this.utcMomentInstanceFromDateAndDropdownTime(
          this.dueDate,
          this.dueTime
        )

        const utcEndDate = this.utcMomentInstanceFromDateAndDropdownTime(
          this.endDate,
          this.endTime || this.dueTime
        )

        if (!utcEndDate.isSame(utcDueDate, 'day')) {
          utcEndDate.add(utcDueDate.diff(utcEndDate, 'day'), 'day')
          this.endDate = this.dateFromAppTimezone(
            utcEndDate.format('YYYY-MM-DD'),
            'YYYY-MM-DD'
          )
        }
      }
    })
  },
  unmounted() {
    Innoclapps.$off('due-time-changed')
    Innoclapps.$off('due-date-changed')
    Innoclapps.$off('update-end-time')
  },
}
</script>
<style>
input[name='end_date-end-time'] {
  width: 116px !important;
}
</style>
