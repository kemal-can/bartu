<template>
  <contact-card
    :contacts="contacts"
    :empty-text="$t('deal.no_contacts_associated')"
  >
    <template #actions="{ contact }">
      <i-button
        size="sm"
        variant="white"
        v-show="$gate.allows('update', deal)"
        v-i-tooltip.left="$t('contact.dissociate')"
        icon="X"
        @click="dissociateContact(contact.id)"
      />
    </template>
    <template #tail>
      <i-button
        v-if="$gate.allows('update', deal)"
        class="mt-6"
        variant="white"
        block
        :to="{ name: 'createContactViaDeal', params: { id: deal.id } }"
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
    deal: {
      required: true,
      type: Object,
    },
  },
  computed: {
    /**
     * If set, get the contact resource record
     *
     * @return {Object|null}
     */
    contact() {
      return this.$store.state.contacts.record
    },

    /**
     * Get the contacts for the card
     *
     * @return {Array}
     */
    contacts() {
      return this.deal.contacts || []
    },
  },
  methods: {
    /**
     * Dissociate contact from deal
     *
     * @param  {String|Number} id
     *
     * @return {Void}
     */
    dissociateContact(id) {
      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete('associations/deals/' + this.deal.id, {
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
