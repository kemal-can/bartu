<template>
  <div class="text-center">
    <svg
      class="mx-auto h-12 w-12 text-neutral-400"
      fill="none"
      viewBox="0 0 24 24"
      stroke="currentColor"
      aria-hidden="true"
    >
      <path
        vector-effect="non-scaling-stroke"
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
      />
    </svg>
    <h3 class="mt-2 text-sm font-medium text-neutral-800 dark:text-white">
      {{ title }}
    </h3>
    <p class="mt-1 text-sm text-neutral-500 dark:text-neutral-300">
      {{ description }}
    </p>
    <div class="mt-6">
      <i-button @click="handleClickEvent" icon="Plus">
        {{ buttonText }}
      </i-button>
    </div>
  </div>
</template>
<script>
export default {
  emits: ['click'],
  props: ['title', 'description', 'buttonText', 'to'],
  methods: {
    handleClickEvent(e) {
      if (this.to) {
        this.$router.push(this.to)
      } else {
        this.$emit('click', e)
      }
    },
  },
}
</script>
