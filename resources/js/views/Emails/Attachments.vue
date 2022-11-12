<template>
  <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
    <li v-for="item in email.media" :key="item.id">
      <div class="block hover:bg-neutral-50 dark:hover:bg-neutral-700/60">
        <div class="flex flex-col px-4 py-4 sm:px-6">
          <a
            :href="item.view_url"
            target="_blank"
            rel="noopener noreferrer"
            class="text-neutral-800 dark:text-neutral-200"
            tabindex="0"
            >{{ item.file_name }}</a
          >
          <span
            class="text-sm text-neutral-500 dark:text-neutral-300"
            v-text="formatBytes(item.size)"
          ></span>
        </div>
      </div>
    </li>
  </ul>
</template>
<script>
import { formatBytes } from '@/utils'
export default {
  props: {
    email: {
      required: true,
      type: Object,
    },
  },
  methods: {
    formatBytes,
  },
}
</script>
