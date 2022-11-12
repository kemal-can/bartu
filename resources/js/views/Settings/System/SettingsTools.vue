<template>
  <i-card :header="$t('settings.tools.tools')" no-body>
    <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
      <li class="px-4 py-4 sm:px-6" v-for="tool in tools" :key="tool">
        <div
          class="flex flex-col space-y-2 sm:flex-row sm:items-center sm:space-y-0"
        >
          <div class="grow">
            <h5
              class="font-medium leading-relaxed text-neutral-900 dark:text-neutral-200"
            >
              {{ tool }}
            </h5>
            <span
              v-t="'settings.tools.' + tool"
              class="text-sm text-neutral-600 dark:text-neutral-300"
            ></span>
          </div>
          <div class="shrink-0">
            <i-button
              variant="white"
              size="sm"
              @click="run(tool)"
              :loading="toolBeingExecuted === tool"
              :disabled="toolBeingExecuted !== null"
            >
              {{ $t('settings.tools.run') }}
            </i-button>
          </div>
        </div>
      </li>
    </ul>
  </i-card>
</template>
<script>
export default {
  data: () => ({
    tools: [
      'i18n-generate',
      'clear-cache',
      'optimize',
      'storage-link',
      'migrate', // used in MigrateDatabase.vue as well
      'seed-mailables',
    ],
    toolBeingExecuted: null,
  }),
  methods: {
    /**
     * Run the given tool
     *
     * @param  {string} tool
     *
     * @return {Void}
     */
    run(tool) {
      this.toolBeingExecuted = tool
      Innoclapps.request()
        .get('/tools/' + tool)
        .then(() => {
          Innoclapps.success(this.$t('settings.tools.executed'))
          setTimeout(() => window.location.reload(true), 1000)
        })
        .finally(() => (this.toolBeingExecuted = false))
    },
  },
}
</script>
