<template>
  <company-card
    :companies="companies"
    :empty-text="$t('contact.no_companies_associated')"
  >
    <template #actions="{ company }">
      <i-button
        size="sm"
        variant="white"
        v-show="$gate.allows('update', contact)"
        v-i-tooltip.left="$t('company.dissociate')"
        @click="dissociateCompany(company.id)"
        icon="X"
      />
    </template>
    <template #tail>
      <i-button
        v-if="$gate.allows('update', contact)"
        variant="white"
        class="mt-6"
        block
        :to="{ name: 'createCompanyViaContact', params: { id: contact.id } }"
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
    contact: {
      required: true,
      type: Object,
    },
  },
  computed: {
    /**
     * If set, get the single resource company record
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
      return this.contact.companies || []
    },
  },
  methods: {
    /**
     * Dissociate company from contact
     *
     * @param  {Number} id
     *
     * @return {void}
     */
    dissociateCompany(id) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete('associations/contacts/' + this.contact.id, {
            data: {
              companies: [id],
            },
          })
          .then(() => {
            this.$emit('dissociated', id)
            Innoclapps.success(this.$t('resource.dissociated'))
            // When preview is shown in deal single resource view
            // We need to actually remove the relation
            if (this.company && this.company.id == id) {
              this.$store.commit(
                'companies/REMOVE_RECORD_HAS_MANY_RELATIONSHIP',
                {
                  id: this.contact.id,
                  relation: 'contacts',
                }
              )
            }
          })
      })
    },
  },
}
</script>
