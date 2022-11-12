<template>
  <div class="group mt-8 px-2">
    <h3
      class="hidden px-3 text-xs font-medium uppercase tracking-wider text-neutral-50 group-hover:inline-flex group-hover:items-center"
      id="highlights-headline"
      v-i-tooltip="{
        content: $t('app.highlights.refresh_interval', { interval: 10 }),
        placement: 'right',
      }"
    >
      <icon icon="QuestionMarkCircle" class="mr-2 h-5 w-5 text-current"></icon>
      {{ $t('app.highlights.highlights') }}
    </h3>
    <div
      class="mt-1 space-y-1"
      role="group"
      aria-labelledby="highlights-headline"
    >
      <router-link
        v-for="highlight in highlights"
        :key="highlight.name"
        class="group flex items-center rounded-md px-3 py-2 text-sm font-medium text-neutral-50 hover:bg-neutral-600"
        :to="highlight.to"
      >
        <span
          :class="[
            highlight.count > 0 ? highlight.bgColorClass : 'bg-success-500',
            'mr-4 h-2.5 w-2.5 rounded-full',
          ]"
          aria-hidden="true"
        />
        <span class="mr-1 truncate" v-text="highlight.name"></span>
        ({{ highlight.count }})
      </router-link>
    </div>
  </div>
</template>
<script>
export default {
  data: () => ({
    highlights: [],
    timer: null,
  }),
  methods: {
    /**
     * Fetch the highlights
     */
    fetch() {
      Innoclapps.request()
        .get('/highlights')
        .then(({ data }) => (this.highlights = data))
    },
  },
  created() {
    this.highlights = Innoclapps.config.highlights
  },
  mounted() {
    this.timer = setInterval(this.fetch, 1000 * 60 * 10)
  },
  beforeUnmount() {
    clearInterval(this.timer)
  },
}
</script>
