<template>
  <div class="flex flex-wrap space-x-0 lg:flex-nowrap lg:space-x-4">
    <template v-if="!componentReady">
      <div class="w-full lg:w-1/2" v-for="p in totalPlaceholders" :key="p">
        <card-placeholder pulse class="mb-4" />
      </div>
    </template>

    <div v-for="card in cards" :class="card.width" :key="card.uriKey">
      <component :is="card.component" class="mb-4" :card="card" />
    </div>
  </div>
</template>
<script>
import CardPlaceholder from '@/components/Loaders/CardPlaceholder'

export default {
  components: { CardPlaceholder },
  data: () => ({
    cards: [],
    componentReady: false,
  }),
  props: {
    resourceName: {
      required: true,
      type: String,
    },
    totalPlaceholders: {
      default: 2,
      type: Number,
    },
  },
  methods: {
    /**
     * Fetch the resource cards
     *
     * @return {Void}
     */
    fetch() {
      Innoclapps.request()
        .get(`/${this.resourceName}/cards`)
        .then(({ data: cards }) => {
          this.cards = cards
          this.componentReady = true
        })
    },
  },
  created() {
    this.fetch()
  },
}
</script>
