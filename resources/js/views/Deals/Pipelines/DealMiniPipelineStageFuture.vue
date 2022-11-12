<template>
  <a href="#" @click.prevent="$emit('click')" class="group flex items-center">
    <span class="flex items-center py-2 px-4 text-sm font-medium">
      <span
        class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full border-2 border-neutral-300 group-hover:border-neutral-400 dark:border-neutral-500 dark:group-hover:border-neutral-100"
      >
        <i-spinner
          class="h-6 w-6 text-neutral-500 dark:text-neutral-200"
          v-if="requestInProgress"
        />
        <icon
          icon="Plus"
          v-else
          class="h-5 w-5 text-neutral-500 dark:text-neutral-100"
        />
      </span>
      <span
        class="ml-4 text-sm font-medium text-neutral-500 group-hover:text-neutral-900 dark:text-neutral-400 dark:group-hover:text-neutral-100"
        ><slot></slot
      ></span>
    </span>
  </a>
</template>
<script>
export default {
  emits: ['click'],
  props: { requestInProgress: Boolean },
}
</script>
