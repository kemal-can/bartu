<template>
  <a
    href="#"
    @click.prevent="$emit('click')"
    class="group flex w-full items-center"
  >
    <span class="flex items-center py-2 px-4 text-sm font-medium">
      <span
        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
        :class="{
          'bg-success-500 group-hover:bg-success-700 dark:bg-success-400 dark:group-hover:bg-success-600':
            deal.status === 'open' || deal.status === 'won',
          'bg-danger-500 group-hover:bg-danger-700 dark:bg-danger-400 dark:group-hover:bg-danger-600':
            deal.status === 'lost',
        }"
      >
        <i-spinner class="h-6 w-6 text-white" v-if="requestInProgress" />
        <icon
          icon="Check"
          v-else-if="deal.status === 'open' || deal.status === 'won'"
          class="h-6 w-6 text-white"
        />
        <icon icon="X" v-else class="h-6 w-6 text-white" />
      </span>
      <span
        class="ml-4 text-sm font-medium text-neutral-900 dark:text-neutral-200"
      >
        <slot></slot>
      </span>
    </span>
  </a>
</template>
<script>
export default {
  emits: ['click'],
  props: {
    stage: { required: true, type: Object },
    deal: { required: true, type: Object },
    requestInProgress: Boolean,
  },
}
</script>
