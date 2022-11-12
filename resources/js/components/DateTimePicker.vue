<template>
  <vue-calendar
    v-model="localValue"
    :timezone="userTimezone"
    :mode="mode"
    :is-required="required"
    :is-range="isRange"
    :is-dark="isDarkMode"
    :popover="{
      positionFixed: fixed,
      visibility: 'focus',
      keepVisibleOnInput: true,
    }"
    :locale="locale"
    :is24hr="!usesTwelveHourTime"
    ref="dateTimePicker"
    :model-config="localModelConfig"
    :masks="
      isDate
        ? {
            input: dateFormatForMoment,
          }
        : {}
    "
    v-bind="$attrs"
  >
    <template v-slot="slotProps">
      <slot
        v-bind="{
          ...slotProps,
          inputValue: !isRange ? localizedValue : localizedRangeValue,
        }"
      >
        <div
          :class="{
            'flex flex-col items-center justify-start sm:flex-row': isRange,
          }"
        >
          <div :class="[roundedClass, 'relative shadow-sm', { grow: isRange }]">
            <div
              v-if="withIcon"
              class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
            >
              <icon
                icon="Calendar"
                class="h-5 w-5 text-neutral-500 dark:text-neutral-300"
              />
            </div>
            <input
              type="text"
              readonly
              :class="[
                'form-input border-neutral-300 dark:border-neutral-500 dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400',
                roundedClass,
                {
                  'pl-11': withIcon,
                  'form-input-sm': size === 'sm',
                  'form-input-lg': size === 'lg',
                },
              ]"
              autocomplete="off"
              :value="!isRange ? localizedValue : localizedValueRangeStart"
              :placeholder="placeholder"
              v-on="
                !isRange
                  ? slotProps.inputEvents
                  : slotProps.inputEvents[rangeKeys.start]
              "
              :disabled="disabled"
              :name="!isRange ? name : name + '-' + rangeKeys.start"
              :id="!isRange ? id : id + '-' + rangeKeys.start"
            />
            <icon
              icon="X"
              class="absolute right-3 top-2.5 h-5 w-5 cursor-pointer text-neutral-400 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
              v-show="Boolean(localValue)"
              v-if="clearable && !isRange"
              @click="clearValues"
            />
          </div>
          <span class="m-2 shrink-0" v-if="isRange">
            <icon icon="ArrowRight" class="h-4 w-4 text-neutral-600" />
          </span>
          <div
            :class="[roundedClass, 'relative grow shadow-sm']"
            v-if="isRange"
          >
            <div
              v-if="withIcon"
              class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3"
            >
              <icon
                icon="Calendar"
                class="h-5 w-5 text-neutral-400 dark:text-neutral-300"
              />
            </div>
            <input
              type="text"
              readonly
              :class="[
                'form-input border-neutral-300 dark:border-neutral-500 dark:bg-neutral-700 dark:text-white dark:placeholder-neutral-400',
                roundedClass,
                {
                  'pl-11': withIcon,
                  'form-input-sm': size === 'sm',
                  'form-input-lg': size === 'lg',
                },
              ]"
              autocomplete="off"
              :value="localizedValueRangeEnd"
              :placeholder="placeholder"
              v-on="slotProps.inputEvents[rangeKeys.end]"
              :disabled="disabled"
              :name="name + '-' + rangeKeys.end"
              :id="id + '-' + rangeKeys.end"
            />
            <icon
              icon="X"
              class="absolute right-3 top-2.5 h-5 w-5 cursor-pointer text-neutral-400 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
              v-show="Boolean(rangeStart) && Boolean(rangeEnd)"
              v-if="clearable"
              @click="clearValues"
            />
          </div>
        </div>
      </slot>
    </template>
  </vue-calendar>
</template>
<script>
import { DatePicker } from 'v-calendar'
import 'v-calendar/dist/style.css'
import { isValueEmpty } from '@/utils'

export default {
  emits: ['update:modelValue', 'input'],
  components: { VueCalendar: DatePicker },
  inheritAttrs: false,
  props: {
    modelValue: {},
    withIcon: { default: true, type: Boolean },
    modelConfig: {
      type: Object,
      default() {
        return {}
      },
    },
    id: String,
    name: String,
    placeholder: String,
    disabled: Boolean,
    required: Boolean,
    isRange: Boolean,
    clearable: {
      default: false,
      type: Boolean,
    },
    fixed: {
      type: Boolean,
      default: true,
    },
    rangeKeys: {
      type: Object,
      default: function () {
        return { start: 'start', end: 'end' }
      },
    },
    mode: {
      type: String,
      default: 'date',
      validator: value => ['date', 'dateTime', 'time'].includes(value),
    },
    rounded: {
      type: Boolean,
      default: true,
    },
    size: {
      type: [String, Boolean],
      default: '',
      validator(value) {
        return ['sm', 'lg', 'md', '', false].includes(value)
      },
    },
  },
  data: () => ({
    localValue: null,
    clearTimeout: null,
    isDarkMode: false,
  }),
  watch: {
    localValue: function (newVal) {
      if (isValueEmpty(newVal)) {
        return this.emitEmptyValChangeEvent()
      }

      if (this.isDate) {
        this.emitValChangeEvent(newVal)
      } else if (this.isDateTime) {
        if (!this.isRange) {
          this.emitValChangeEvent(this.dateToAppTimezone(newVal))
        } else {
          this.emitValChangeEvent({
            [this.rangeKeys.start]: this.dateToAppTimezone(
              newVal[this.rangeKeys.start]
            ),
            [this.rangeKeys.end]: this.dateToAppTimezone(
              newVal[this.rangeKeys.end]
            ),
          })
        }
      } else {
        // TODO time, not yet used
        this.emitValChangeEvent(newVal)
      }
    },
    modelValue: {
      handler: function (newVal) {
        if (this.isEqualToLocalValue(newVal)) {
          return
        }

        this.setLocalValueFromModelValue(newVal)
      },
      immediate: true,
    },
  },
  computed: {
    roundedClass() {
      if (this.rounded && this.size === 'sm') {
        return 'rounded'
      }
      if (this.rounded && this.size !== 'sm' && this.size !== false) {
        return 'rounded-md'
      }
    },
    isDateTime() {
      return this.mode.toLowerCase() === 'datetime'
    },
    isDate() {
      return this.mode.toLowerCase() === 'date'
    },
    localModelConfig() {
      let config = {
        type: 'string',
      }

      if (this.isDate) {
        config.mask = 'YYYY-MM-DD'
      } else if (this.isDateTime) {
        config.mask = 'YYYY-MM-DD HH:mm:ss'
      } else {
        // TODO time, not yet used
      }

      return Object.assign({}, config, this.modelConfig)
    },
    localizedValue() {
      return this.localValue ? this.localizeValue(this.localValue) : ''
    },
    localizedRangeValue() {
      return {
        [this.rangeKeys.start]: this.localizedValueRangeStart,
        [this.rangeKeys.end]: this.localizedValueRangeEnd,
      }
    },
    rangeStart() {
      return this.localValue[this.rangeKeys.start]
    },
    rangeEnd() {
      return this.localValue[this.rangeKeys.end]
    },
    localizedValueRangeStart() {
      return this.rangeStart ? this.localizeValue(this.rangeStart) : ''
    },
    localizedValueRangeEnd() {
      return this.rangeEnd ? this.localizeValue(this.rangeEnd) : ''
    },
    locale() {
      let currentUser = this.$store.getters['users/current']

      let firstDayOfWeek = Number(
        currentUser
          ? currentUser.first_day_of_week
          : this.$store.state.settings.first_day_of_week
      )

      return {
        id: navigator.language,
        firstDayOfWeek: firstDayOfWeek + 1, // uses 1-7 not 0-6 weekdays
      }
    },
  },
  methods: {
    localizeValue(value) {
      if (this.isDate) {
        return this.appMoment(value).format(this.dateFormatForMoment)
      } else if (this.isDateTime) {
        return this.localizedDateTime(this.dateToAppTimezone(value))
      } else {
        // TODO time, not yet used
        return value
      }
    },
    setLocalValueFromModelValue(value) {
      if (!this.isRange) {
        if (this.isDateTime) {
          this.localValue = this.dateFromAppTimezone(value)
        } else if (this.isDate) {
          this.localValue = value
        } else {
          // TODO time, not yet used
          this.localValue = value
        }
        return
      }

      if (this.isDate) {
        this.localValue = {
          [this.rangeKeys.start]: value[this.rangeKeys.start],
          [this.rangeKeys.end]: value[this.rangeKeys.end],
        }
      } else if (this.isDateTime) {
        this.localValue = {
          [this.rangeKeys.start]: this.dateFromAppTimezone(
            value[this.rangeKeys.start]
          ),
          [this.rangeKeys.end]: this.dateFromAppTimezone(
            value[this.rangeKeys.end]
          ),
        }
      } else {
        // TODO time, not yet used
        this.localValue = value
      }
    },
    isEqualToLocalValue(value) {
      if (value === this.localValue) {
        return true
      }

      if (!this.isRange) {
        if (this.isDateTime) {
          return this.dateFromAppTimezone(value) === this.localValue
        } else if (this.isDate) {
          return value === this.localValue
        } else {
          // TODO time, not yet used
        }
      }

      if ((!this.localValue && value) || (!value && localValue)) {
        return false
      }

      if (this.isDateTime) {
        return (
          this.dateFromAppTimezone(
            value[this.rangeKeys.start] === this.rangeStart
          ) &&
          this.dateFromAppTimezone(value[this.rangeKeys.end] === this.rangeEnd)
        )
      } else if (this.isDate) {
        return (
          value[this.rangeKeys.start] === this.rangeStart &&
          value[this.rangeKeys.end] === this.rangeEnd
        )
      } else {
        // TODO time, not yet used
      }
    },
    clearValues() {
      if (this.isRange) {
        this.localValue[this.rangeKeys.start] = null
        this.localValue[this.rangeKeys.end] = null
      } else {
        this.localValue = null
      }
    },
    emitValChangeEvent(value) {
      this.$emit('update:modelValue', value)
      this.$emit('input', value)
    },
    emitEmptyValChangeEvent() {
      this.emitValChangeEvent(
        !this.isRange
          ? null
          : {
              [this.rangeKeys.start]: null,
              [this.rangeKeys.end]: null,
            }
      )
    },
  },
  created() {
    this.isDarkMode = document.documentElement.classList.contains('dark')
  },
  mounted() {
    if (this.isDateTime) {
      this.clearTimeout = setTimeout(
        () => this.$refs.dateTimePicker.refreshDateParts(),
        100
      )
    }
  },
  beforeUnmount() {
    this.clearTimeout && clearTimeout(this.clearTimeout)
  },
}
</script>
