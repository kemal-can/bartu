<template>
  <div class="hidden sm:relative sm:block sm:px-1">
    <div
      :class="[
        log.is_pinned ? 'bg-warning-300' : 'bg-neutral-200 dark:bg-neutral-700',
        'flex h-8 w-8 items-center justify-center rounded-full ring-8 ring-neutral-100 dark:ring-neutral-800',
      ]"
    >
      <icon
        :icon="icon"
        :class="[
          'h-5 w-5',
          log.is_pinned
            ? 'text-neutral-900'
            : 'text-neutral-600 dark:text-neutral-100',
        ]"
      />
    </div>
  </div>
  <div class="min-w-0 flex-1 sm:py-1.5">
    <div class="flex justify-between space-x-4 text-sm">
      <div class="flex-1 md:flex md:items-center">
        <p class="mr-2 text-neutral-800 dark:text-white" :class="headingClass">
          <slot name="heading">
            {{ heading }}
          </slot>
        </p>

        <span
          class="self-start whitespace-nowrap text-neutral-500 dark:text-neutral-300"
          v-once
        >
          <slot name="date">
            <span class="hidden md:inline-block">-</span>
            {{ localizedDateTime(log.created_at) }}
          </slot>
        </span>
      </div>
      <timeline-entry-pin :resource-name="resourceName" :timelineable="log" />
    </div>
    <slot></slot>
  </div>
</template>
<script>
import TimelineEntryPin from './TimelineEntryPin'

export default {
  components: { TimelineEntryPin },
  props: {
    headingClass: [String, Object, Array],
    resourceName: { type: String, required: true },
    log: { type: Object, required: true },
    heading: String,
    icon: { type: String, required: true },
  },
}
</script>
