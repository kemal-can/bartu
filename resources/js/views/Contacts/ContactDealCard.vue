<template>
  <deal-card :deals="deals" :empty-text="$t('contact.no_deals_associated')">
    <template #actions="{ deal }">
      <i-button
        size="sm"
        variant="white"
        v-show="$gate.allows('update', contact)"
        v-i-tooltip.left="$t('deal.dissociate')"
        @click="dissociateDeal(deal.id)"
        icon="X"
      />
    </template>
    <template #tail>
      <i-button
        v-if="$gate.allows('update', contact)"
        class="mt-6"
        variant="white"
        block
        :to="{ name: 'createDealViaContact', params: { id: contact.id } }"
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
    contact: {
      required: true,
      type: Object,
    },
  },
  computed: {
    /**
     * If set, get the single resource deal record
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
      return this.contact.deals || []
    },
  },
  methods: {
    /**
     * Dissociate deals from contact
     * @param  {String|Number} id
     * @return {void}
     */
    dissociateDeal(id) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete('associations/contacts/' + this.contact.id, {
            data: {
              deals: [id],
            },
          })
          .then(() => {
            this.$emit('dissociated', id)
            Innoclapps.success(this.$t('resource.dissociated'))
            // When preview is shown in deal single resource view
            // We need to actually remove the relation
            if (this.deal && this.deal.id == id) {
              this.$store.commit('deals/REMOVE_RECORD_HAS_MANY_RELATIONSHIP', {
                id: this.contact.id,
                relation: 'contacts',
              })
            }
          })
      })
    },
  },
}
</script>
