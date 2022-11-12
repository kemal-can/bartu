<template>
  <form-field-group :field="field" :field-id="fieldId" :form="form">
    <div class="flex items-center">
      <div class="mr-2">
        <div class="flex rounded-md shadow-sm">
          <i-button-close
            :rounded="false"
            @click="cancelReminder()"
            :icon="!cancelled ? 'X' : 'Bell'"
            v-if="field.cancelable"
            variant="white"
            class="relative -mr-px rounded-l-md focus:z-20"
          />
          <div class="relative flex grow items-stretch">
            <i-form-numeric-input
              type="number"
              :max="maxAttribute"
              :min="1"
              :rounded="false"
              :class="[
                {
                  'rounded-r-md': field.cancelable,
                  'rounded-md': !field.cancelable,
                },
              ]"
              :disabled="cancelled"
              :precision="0"
              :placeholder="$t('dates.' + selectedType)"
              v-model="reminderValue"
            />
          </div>
        </div>
      </div>
      <div class="flex items-center space-x-2">
        <i-form-select
          v-model="selectedType"
          :disabled="cancelled"
          class="sm:flex-1"
        >
          <option :value="type" v-for="type in types" :key="type">
            {{ type }}
          </option>
        </i-form-select>
        <div
          class="ml-2 truncate text-neutral-800 dark:text-neutral-300"
          v-t="'app.reminder_before_due'"
        ></div>
      </div>
    </div>
  </form-field-group>
</template>
<script>
import FormField from '@/components/Form/FormField'
import {
  determineReminderTypeBasedOnMinutes,
  determineReminderValueBasedOnMinutes,
} from '@/utils'
export default {
  mixins: [FormField],

  data: () => ({
    types: ['minutes', 'hours', 'days', 'weeks'],
    reminderValue: Innoclapps.config.defaults.reminder_minutes,
    selectedType: 'minutes',
    cancelled: false,
  }),

  watch: {
    valueInMinutes: function (newVal, oldVal) {
      this.value = newVal
    },
  },

  computed: {
    /**
     * Max attribute for the field
     *
     * @return {Number}
     */
    maxAttribute() {
      if (this.selectedType === 'minutes') {
        return 59
      } else if (this.selectedType === 'hours') {
        return 23
      } else if (this.selectedType === 'days') {
        return 6
      }
      // For weeks, as Google allow max 4 weeks reminder
      return 4
    },

    /**
     * Get the actual value in minutes
     *
     * @return {Number|null}
     */
    valueInMinutes() {
      if (this.cancelled) {
        return null
      }

      if (this.selectedType === 'minutes') {
        return parseInt(this.reminderValue)
      } else if (this.selectedType === 'hours') {
        return parseInt(this.reminderValue) * 60
      } else if (this.selectedType === 'days') {
        return parseInt(this.reminderValue) * 1440
      } else if (this.selectedType === 'weeks') {
        return parseInt(this.reminderValue) * 10080
      }
    },
  },
  methods: {
    /**
     * Set/toggle the no reminder option
     */
    cancelReminder(force) {
      this.cancelled = force === undefined ? !this.cancelled : force
      this.reminderValue = Innoclapps.config.defaults.reminder_minutes
      this.selectedType = 'minutes'
    },

    /**
     * Handle field change, update the actual value to proper format
     *
     * @param  {String} value
     *
     * @return {Void}
     */
    handleChange(value) {
      if (value) {
        this.value = value
        this.reminderValue = determineReminderValueBasedOnMinutes(value)
        this.selectedType = determineReminderTypeBasedOnMinutes(value)
      } else if (value === null && this.field.cancelable) {
        this.cancelReminder(true)
      } else {
        this.value = this.reminderValue
      }

      this.realInitialValue = this.value
    },

    /*
     * Set the initial value for the field
     */
    setInitialValue() {
      if (this.field.value) {
        this.value = this.field.value
        this.reminderValue = determineReminderValueBasedOnMinutes(
          this.field.value
        )
        this.selectedType = determineReminderTypeBasedOnMinutes(
          this.field.value
        )
      } else if (this.field.value === null && this.field.cancelable) {
        this.cancelReminder()
      } else {
        this.value = this.reminderValue
      }
    },
  },
}
</script>
