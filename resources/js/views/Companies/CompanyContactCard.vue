<template>
  <contact-card
    :contacts="contacts"
    :empty-text="$t('company.no_contacts_associated')"
  >
    <template #actions="{ contact }">
      <i-button
        size="sm"
        variant="white"
        v-show="$gate.allows('update', company)"
        v-i-tooltip.left="$t('contact.dissociate')"
        @click="dissociateContact(contact.id)"
        icon="X"
      />
    </template>
    <template #tail>
      <i-button
        v-if="$gate.allows('update', company)"
        variant="white"
        class="mt-6"
        block
        :to="{ name: 'createContactViaCompany', params: { id: company.id } }"
      >
        {{ $t('contact.add') }}
      </i-button>
    </template>
  </contact-card>
</template>
<script>
import ContactCard from '@/views/Contacts/ContactCard'
export default {
  emits: ['dissociated'],
  components: { ContactCard },
  props: {
    company: {
      required: true,
      type: Object,
    },
  },
  computed: {
    /**
     * If set, get the single resource record
     *
     * @return {Object|null}
     */
    contact() {
      return this.$store.state.contacts.record
    },

    /**
     * Get the compant conacts for the card
     *
     * @return {Array}
     */
    contacts() {
      return this.company.contacts || []
    },
  },
  methods: {
    /**
     * Dissociate contact from company
     * @param  {String|Number} id
     * @return {Void}
     */
    dissociateContact(id) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete('associations/companies/' + this.company.id, {
            data: {
              contacts: [id],
            },
          })
          .then(() => {
            this.$emit('dissociated', id)
            Innoclapps.success(this.$t('resource.dissociated'))
            // When preview is shown in contact single resource view
            // We need to actually remove the relation
            if (this.contact && this.contact.id == id) {
              this.$store.commit(
                'contacts/REMOVE_RECORD_HAS_MANY_RELATIONSHIP',
                {
                  id: this.company.id,
                  relation: 'companies',
                }
              )
            }
          })
      })
    },
  },
}
</script>
