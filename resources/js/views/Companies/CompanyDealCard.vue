<template>
  <deal-card :deals="deals" :empty-text="$t('company.no_deals_associated')">
    <template #actions="{ deal }">
      <i-button
        size="sm"
        variant="white"
        v-show="$gate.allows('update', company)"
        v-i-tooltip.left="$t('deal.dissociate')"
        @click="dissociateDeal(deal.id)"
        icon="X"
      />
    </template>

    <template #tail>
      <i-button
        v-if="$gate.allows('update', company)"
        variant="white"
        class="mt-6"
        block
        :to="{ name: 'createDealViaCompany', params: { id: company.id } }"
      >
        {{ $t('deal.add') }}
      </i-button>
    </template>
  </deal-card>
</template>
<script>
import DealCard from '@/views/Deals/DealCard'
export default {
  emits: ['dissociated'],
  components: { DealCard },
  props: {
    company: {
      required: true,
      type: Object,
    },
  },
  computed: {
    /**
     * If set, get the single deal resource record
     *
     * @return {Object|null}
     */
    deal() {
      return this.$store.state.deals.record
    },

    /**
     * Get the deals for the card
     *
     * @return {Array}
     */
    deals() {
      return this.company.deals || []
    },
  },
  methods: {
    /**
     * Dissociate deals from company
     * @param  {String|Number} id
     * @return {void}
     */
    dissociateDeal(id) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete('associations/companies/' + this.company.id, {
            data: {
              deals: [id],
            },
          })
          .then(() => {
            this.$emit('dissociated', id)
            Innoclapps.success(this.$t('resource.dissociated'))
            // When preview is shown in contact single resource view
            // We need to actually remove the relation
            if (this.deal && this.deal.id == id) {
              this.$store.commit('deals/REMOVE_RECORD_HAS_MANY_RELATIONSHIP', {
                id: this.company.id,
                relation: 'companies',
              })
            }
          })
      })
    },
  },
}
</script>
