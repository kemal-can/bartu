<template>
  <company-card
    :companies="companies"
    :empty-text="$t('deal.no_companies_associated')"
  >
    <template #actions="{ company }">
      <i-button
        size="sm"
        variant="white"
        v-show="$gate.allows('update', deal)"
        icon="X"
        v-i-tooltip.left="$t('company.dissociate')"
        @click="dissociateCompany(company.id)"
      />
    </template>
    <template #tail>
      <i-button
        v-if="$gate.allows('update', deal)"
        variant="white"
        class="mt-6"
        block
        :to="{ name: 'createCompanyViaDeal', params: { id: deal.id } }"
      >
        {{ $t('company.add') }}
      </i-button>
    </template>
  </company-card>
</template>
<script>
import CompanyCard from '@/views/Companies/CompanyCard'
export default {
  emits: ['dissociated'],
  components: { CompanyCard },
  props: {
    deal: {
      required: true,
      type: Object,
    },
  },
  computed: {
    /**
     * If set, get the company resource record
     *
     * @return {Object|null}
     */
    company() {
      return this.$store.state.companies.record
    },

    /**
     * Get the companies for the card
     *
     * @return {Array}
     */
    companies() {
      return this.deal.companies || []
    },
  },
  methods: {
    /**
     * Dissociate company from deal
     * @param  {String|Number} id
     * @return {void}
     */
    dissociateCompany(id) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete('associations/deals/' + this.deal.id, {
            data: {
              companies: [id],
            },
          })
          .then(() => {
            this.$emit('dissociated', id)
            Innoclapps.success(this.$t('resource.dissociated'))
            // When preview is shown in contact single resource view
            // We need to actually remove the relation
            if (this.company && this.company.id == id) {
              this.$store.commit(
                'companies/REMOVE_RECORD_HAS_MANY_RELATIONSHIP',
                {
                  id: this.deal.id,
                  relation: 'deals',
                }
              )
            }
          })
      })
    },
  },
}
</script>
