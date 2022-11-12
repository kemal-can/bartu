<template>
  <div class="flex items-center">
    <i-form-checkbox
      v-model:checked="form.with_task"
      @change="checkboxOnChange"
      :label="$t('activity.create_follow_up_task')"
    />

    <div class="ml-2 flex" v-if="form.with_task">
      <div v-show="!isCustomDateSelected">
        <i-dropdown>
          <template #toggle>
            <a href="#" class="link inline-flex items-center text-sm">
              {{ dropdownLabel }}
              <icon icon="ChevronDown" class="ml-1 h-4 w-4" />
            </a>
          </template>
          <i-dropdown-item
            v-for="date in dates"
            @click="onDropdownSelected(date.value)"
            :key="date.value"
            :text="date.label"
          />
        </i-dropdown>
      </div>
      <date-picker
        v-if="isCustomDateSelected"
        v-model="form.task_date"
        :required="true"
      >
        <template v-slot="{ inputValue, inputEvents }">
          <input
            :value="inputValue"
            v-on="inputEvents"
            class="cursor-pointer bg-transparent text-sm font-medium text-neutral-700 focus:outline-none dark:text-neutral-100"
          />
        </template>
      </date-picker>
    </div>
  </div>
</template>
<script>
import find from 'lodash/find'
export default {
  props: {
    form: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    selectedDropdownDate: '',
  }),
  computed: {
    /**
     * Today's date object
     *
     * @return {Object}
     */
    dateToday() {
      return {
        label: this.$t('dates.today'),
        value: this.appMoment().format('YYYY-MM-DD'),
      }
    },

    /**
     * Tomorrow date object
     *
     * @return {Object}
     */
    dateTomorrow() {
      return {
        label: this.$t('dates.tomorrow'),
        value: this.dateDropdownValue(1),
        default: true,
      }
    },

    /**
     * Date in 2 days object
     *
     * @return {Object}
     */
    dateIn2Days() {
      return {
        label:
          this.$t('dates.in_2_days') + ' (' + this.dateDropdownLabel(2) + ')',
        value: this.dateDropdownValue(2),
      }
    },

    /**
     * Date in  days object
     *
     * @return {Object}
     */
    dateIn3Days() {
      return {
        label:
          this.$t('dates.in_3_days') + ' (' + this.dateDropdownLabel(3) + ')',
        value: this.dateDropdownValue(3),
      }
    },

    /**
     * Date in 4 days object
     *
     * @return {Object}
     */
    dateIn4Days() {
      return {
        label:
          this.$t('dates.in_4_days') + ' (' + this.dateDropdownLabel(4) + ')',
        value: this.dateDropdownValue(4),
      }
    },

    /**
     * Date in 5 days object
     *
     * @return {Object}
     */
    dateIn5Days() {
      return {
        label:
          this.$t('dates.in_5_days') + ' (' + this.dateDropdownLabel(5) + ')',
        value: this.dateDropdownValue(5),
      }
    },

    /**
     * Date in 2 weeks object
     *
     * @return {Object}
     */
    dateIn2Weeks() {
      return {
        label:
          this.$t('dates.in_2_weeks') +
          ' (' +
          this.dateDropdownLabel(2, 'weeks', 'MMMM Do') +
          ')',
        value: this.dateDropdownValue(2, 'weeks'),
      }
    },

    /**
     * Date in 1 month object
     *
     * @return {Object}
     */
    dateIn1Month() {
      return {
        label:
          this.$t('dates.in_1_month') +
          ' (' +
          this.dateDropdownLabel(1, 'months', 'MMMM Do') +
          ')',
        value: this.dateDropdownValue(1, 'months'),
      }
    },

    /**
     * Whether the "custom" dropdown option is selected
     *
     * @return {Boolean}
     */
    isCustomDateSelected() {
      return this.selectedDropdownDate === 'custom'
    },

    /**
     * Dates for dropdown
     *
     * @return {Array}
     */
    dates() {
      return [
        this.dateToday,
        this.dateTomorrow,
        this.dateIn2Days,
        this.dateIn3Days,
        this.dateIn4Days,
        this.dateIn5Days,
        this.dateIn2Weeks,
        this.dateIn1Month,
        {
          label: this.$t('dates.custom'),
          value: 'custom',
        },
      ]
    },

    /**
     * Label for the dropdown text based on selected date
     * @return {Sting|null}
     */
    dropdownLabel() {
      let selected = find(this.dates, ['value', this.selectedDropdownDate])

      if (selected) {
        return selected.label
      }
    },

    /**
     * The default value
     *
     * @return {String}
     */
    defaultValue() {
      return find(this.dates, ['default', true]).value
    },
  },
  methods: {
    /**
     * On date option selected from the dropdown
     * @param  {String} value
     * @return {Void}
     */
    onDropdownSelected(value) {
      this.selectedDropdownDate = value
      if (value !== 'custom') {
        this.form.task_date = value
      }
    },

    /**
     * Dropdown label date to show the actual day/date
     * @param  {Number} number
     * @param  {String} period
     * @param  {String} format
     * @return {String}
     */
    dateDropdownValue(number, period = 'days', format = 'YYYY-MM-DD') {
      return this.appMoment().add(number, period).format(format)
    },

    /**
     * Dropdown label date to show the actual day/date
     * @param  {Number} number
     * @param  {String} period
     * @param  {String} format
     * @return {String}
     */
    dateDropdownLabel(number, period = 'days', format = 'dddd') {
      return this.dateFromAppTimezone(
        this.appMoment().add(number, period),
        format
      )
    },

    /**
     * Handle the checkbox "Create follow up task" change event
     *
     * @param  {Boolean} value
     *
     * @return {Void}
     */
    checkboxOnChange(value) {
      if (value && !this.form.task_date) {
        this.$nextTick(() => {
          this.form.task_date = this.defaultValue
          this.selectedDropdownDate = this.defaultValue
        })
      } else if (!value) {
        this.form.task_date = null
      }
    },
  },
}
</script>
