<template>
  <div class="flex animate-pulse">
    <div class="flex-1 space-y-4">
      <div
        :key="count"
        v-for="count in total"
        class="h-9 rounded-md bg-neutral-200 dark:bg-neutral-700"
        :class="randomPlaceholderWidth()"
      ></div>
    </div>
  </div>
</template>
<script>
export default {
  props: {
    total: {
      default: 8,
      type: Number,
    },
  },
  data: () => ({
    placeholderWidths: ['w-full', 'w-1/2', 'w-5/6', 'w-3/4'],
  }),
  methods: {
    randomPlaceholderWidth() {
      return this.placeholderWidths[
        Math.floor(Math.random() * this.placeholderWidths.length)
      ]
    },
  },
}
</script>
