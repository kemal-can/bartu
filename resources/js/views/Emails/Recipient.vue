<template>
  <span
    :class="[
      'text-neutral-700 dark:text-neutral-300',
      { 'border-b border-dashed border-neutral-400': recipient.address },
    ]"
    v-if="recipient.name"
    v-i-tooltip="recipient.address"
    >{{ recipient.name }}</span
  >
  <span
    class="text-neutral-700 dark:text-neutral-300"
    v-else
    v-text="recipient.address"
  ></span>
</template>
<script>
export default {
  props: {
    recipient: {
      type: Object,
      required: true,
    },
  },
}
</script>
