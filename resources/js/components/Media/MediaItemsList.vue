<template>
  <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
    <li
      v-for="media in items"
      :key="media.id"
      class="group flex items-center space-x-3 py-4"
    >
      <div class="shrink-0">
        <span
          :class="[
            media.was_recently_created
              ? 'bg-success-500 text-white'
              : 'bg-neutral-200 text-neutral-400 dark:bg-neutral-700 dark:text-neutral-300',
          ]"
          class="inline-flex h-10 w-10 items-center justify-center rounded-full text-sm"
        >
          <icon
            icon="Check"
            class="h-5 w-5"
            v-if="media.was_recently_created"
          />
          <span v-text="media.extension" v-else></span>
        </span>
      </div>

      <div class="min-w-0 flex-1 truncate">
        <a
          :href="media.view_url"
          class="text-sm font-medium text-neutral-800 hover:text-neutral-500 focus:outline-none dark:text-white dark:hover:text-neutral-300"
          target="_blank"
          rel="noopener noreferrer"
          tabindex="0"
          >{{ media.file_name }}</a
        >
        <span class="ml-2 text-sm text-neutral-500 dark:text-neutral-300">{{
          formatBytes(media.size)
        }}</span>
        <p class="text-sm text-neutral-500 dark:text-neutral-300">
          {{ localizedDateTime(media.created_at) }}
        </p>
      </div>
      <div class="block shrink-0 md:hidden md:group-hover:block">
        <div class="flex items-center space-x-2">
          <a
            :href="media.download_url"
            class="text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
          >
            <icon icon="Download" class="h-5 w-5" />
          </a>
          <div v-if="authorizeDelete">
            <i-button-icon icon="X" @click="deleteRequested(media)" />
          </div>
        </div>
      </div>
    </li>
  </ul>
</template>
<script>
import { formatBytes } from '@/utils'
export default {
  emits: ['delete-requested'],
  props: {
    items: Array,
    authorizeDelete: { type: Boolean, default: false },
  },
  methods: {
    formatBytes,
    deleteRequested(media) {
      this.$emit('delete-requested', media)
    },
  },
}
</script>
