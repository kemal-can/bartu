<template>
  <i-custom-select
    input-id="first_day_of_week"
    v-model="internalValue"
    :options="options"
    @update:modelValue="$emit('update:modelValue', $event)"
    :clearable="false"
    :reduce="option => option.id"
  />
</template>
<script>
import find from 'lodash/find'
import filter from 'lodash/filter'
export default {
  emits: ['update:modelValue'],
  props: {
    modelValue: null,
    only: Array,
  },
  watch: {
    modelValue: {
      handler: function (newVal, oldVal) {
        this.internalValue = find(this.options, ['id', Number(newVal)])
      },
      immediate: true,
    },
  },
  data() {
    return {
      internalValue: null,
      weekDays: [
        {
          id: 1,
          label: this.$t('app.weekdays.monday'),
        },
        {
          id: 2,
          label: this.$t('app.weekdays.tuesday'),
        },
        {
          id: 3,
          label: this.$t('app.weekdays.wednesday'),
        },
        {
          id: 4,
          label: this.$t('app.weekdays.thursday'),
        },
        {
          id: 5,
          label: this.$t('app.weekdays.friday'),
        },
        {
          id: 6,
          label: this.$t('app.weekdays.saturday'),
        },
        {
          id: 0,
          label: this.$t('app.weekdays.sunday'),
        },
      ],
    }
  },
  computed: {
    /**
     * Get the options filterd for select
     *
     * @return {Array}
     */
    options() {
      if (!this.only) {
        return this.weekDays
      }

      return filter(this.weekDays, day => this.only.indexOf(day.id) > -1)
    },
  },
}
</script>
