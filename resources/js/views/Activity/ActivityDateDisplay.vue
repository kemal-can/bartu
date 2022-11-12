<template>
  <p
    :class="[
      'flex items-center text-sm',
      {
        'text-danger-500 dark:text-danger-400': isDue,
        'text-neutral-800 dark:text-neutral-200': !isDue,
      },
    ]"
  >
    <icon
      v-if="withIcon"
      icon="Calendar"
      :class="[
        'mr-2 h-5 w-5',
        {
          'text-neutral-800 dark:text-white': !isDue,
          'text-danger-400': isDue,
        },
      ]"
    />
    {{
      dueDate.time
        ? localizedDateTime(dueDate.date + ' ' + dueDate.time)
        : localizedDate(dueDate.date)
    }}
    <span v-if="isEndDateBiggerThenDueDate" class="ml-1">
      -
      {{ localizedDateTime(endDate.date + ' ' + endDate.time) }}
    </span>
    <span v-else-if="isEndDateEqualToDueDate" class="ml-1">
      -
      {{
        localizedDateTime(
          endDate.date + ' ' + endDate.time,
          timeFormatForMoment
        )
      }}
    </span>
  </p>
</template>
<script>
export default {
  props: {
    dueDate: { required: true },
    endDate: { required: true },
    isDue: { required: true, type: Boolean },
    withIcon: { type: Boolean, default: true },
  },
  computed: {
    isEndDateBiggerThenDueDate() {
      return (
        this.endDate.date &&
        this.endDate.time &&
        this.localizedDateTime(
          this.endDate.date + ' ' + this.endDate.time,
          'YYYY-MM-DD'
        ) >
          this.localizedDateTime(
            this.dueDate.date + ' ' + this.dueDate.time,
            'YYYY-MM-DD'
          )
      )
    },

    isEndDateEqualToDueDate() {
      return (
        this.endDate.date &&
        this.endDate.time &&
        this.localizedDateTime(
          this.endDate.date + ' ' + this.endDate.time,
          'YYYY-MM-DD'
        ) ==
          this.localizedDateTime(
            this.dueDate.date + ' ' + this.dueDate.time,
            'YYYY-MM-DD'
          )
      )
    },

    timeFormatForMoment() {
      return moment().PHPconvertFormat(this.currentTimeFormat)
    },
  },
}
</script>
