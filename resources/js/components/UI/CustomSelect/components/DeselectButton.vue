<template>
  <button
    type="button"
    :disabled="disabled"
    @click.prevent.stop="deselect(option)"
    class="ml-1 text-neutral-400 hover:text-neutral-600 dark:text-neutral-200 dark:hover:text-neutral-400"
    :title="`Deselect ${label}`"
    :aria-label="`Deselect ${label}`"
    ref="deselectButton"
  >
    <icon icon="X" class="h-4 w-4" />
  </button>
</template>
<script>
export default {
  props: {
    option: { required: true },
    deselect: {
      type: Function,
      required: true,
    },
    disabled: {
      type: Boolean,
      required: true,
    },
    label: {
      type: [String, Number],
      required: true,
    },
  },
}
</script>
