<template>
  <span
    :class="[
      'inline-block h-4 w-4 rounded-full',
      {
        'bg-success-400': passes,
        'bg-danger-400': fails,
      },
    ]"
  ></span>
</template>
<script>
import TableData from './TableData'
export default {
  mixins: [TableData],
  computed: {
    passes() {
      return this.formatted === this.column.trueValue
    },
    fails() {
      return this.formatted === this.column.falseValue
    },
  },
}
</script>
