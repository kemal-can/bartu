<template>
  <i-custom-select
    :input-id="fieldId || 'timezone'"
    :clearable="clearable"
    :placeholder="placeholder"
    :model-value="modelValue"
    @option:selected="$emit('update:modelValue', $event)"
    @cleared="$emit('update:modelValue', null)"
    :options="timezones"
  >
    <template #option="option">
      {{ label(option) }}
    </template>
  </i-custom-select>
</template>
<script>
import { mapState } from 'vuex'
export default {
  emits: ['update:modelValue'],
  name: 'app-timezone-field',
  props: {
    modelValue: null,
    fieldId: String,
    placeholder: {
      type: String,
      default: '',
    },
    clearable: {
      default: false,
    },
  },
  methods: {
    /**
     * Get the timezone formatted label
     *
     * @param  {Object} option
     *
     * @return {String}
     */
    label(option) {
      return (
        'UTC/GMT ' + moment.tz(option.label).format('Z') + ' ' + option.label
      )
    },
  },
  computed: {
    ...mapState({
      timezones: state => state.timezones,
    }),
  },
  created() {
    // Check if the timezones are set in the store
    // If not, make a request to fetch the timezones and set them in for future usage
    if (this.timezones.length === 0) {
      this.$store.dispatch('fetchTimezones')
    }
  },
}
</script>
