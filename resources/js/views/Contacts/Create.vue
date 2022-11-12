<template>
  <i-slideover
    @hidden="goBack"
    :title="modalTitle"
    :visible="true"
    static-backdrop
    form
    @submit="store"
  >
    <form-fields-placeholder v-if="!fieldsConfigured" />

    <div
      class="mb-4 rounded-md border border-success-400 px-4 py-3"
      v-if="viaResource && fieldsConfigured"
    >
      <fields-generator
        :form="associateForm"
        view="create"
        :fields="associateField"
      />
    </div>

    <div v-show="!hasSelectedExistingContact">
      <focus-able-fields-generator
        :form="form"
        :fields="fields"
        view="create"
        :is-floating="true"
      />
    </div>

    <template #modal-ok>
      <div v-show="!hasSelectedExistingContact">
        <i-dropdown-button-group
          placement="top-end"
          :disabled="form.busy"
          :loading="form.busy"
          :text="$t('app.create')"
          type="submit"
        >
          <i-dropdown-item @click="storeAndAddAnother">{{
            $t('app.create_and_add_another')
          }}</i-dropdown-item>
          <i-dropdown-item @click="storeAndGoToList">{{
            $t('app.create_and_go_to_list')
          }}</i-dropdown-item>
        </i-dropdown-button-group>
      </div>
      <i-button
        v-show="hasSelectedExistingContact"
        variant="primary"
        @click="associate"
        >{{ $t('app.associate') }}</i-button
      >
    </template>
    <teleport to="#after-email-field" v-if="trashedContact !== null">
      <i-alert dismissible>
        {{ $t('contact.exists_in_trash_by_email') }}

        <div class="mt-4">
          <div class="-mx-2 -my-1.5 flex">
            <i-button-minimal variant="info" @click="restoreTrashed">
              {{ $t('app.soft_deletes.restore') }}
            </i-button-minimal>
          </div>
        </div>
      </i-alert>
    </teleport>
  </i-slideover>
</template>
<script>
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import FieldsCollection from '@/services/FieldsCollection'
import Form from '@/components/Form/Form'
import debounce from 'lodash/debounce'

export default {
  emits: ['created'],
  mixins: [InteractsWithResourceFields],
  props: {
    viaResource: String,
  },
  data() {
    return {
      emailField: {},
      trashedContact: null,
      form: new Form({
        avatar: null,
      }),
      associateForm: new Form({
        id: null,
      }),
      associateField: new FieldsCollection({
        asyncUrl: '/contacts/search',
        attribute: 'id',
        component: 'select-field',
        helpText: this.$t('contact.associate_field_info'),
        helpTextDisplay: 'text',
        label: this.$t('contact.contact'),
        labelKey: 'display_name',
        valueKey: 'id',
      }),
    }
  },
  watch: {
    'emailField.currentValue': debounce(function (newVal, oldVal) {
      if (!newVal) {
        this.trashedContact = null
        return
      }

      Innoclapps.request()
        .get(`/trashed/contacts/search`, {
          params: {
            q: newVal,
            search_fields: 'email:=',
          },
        })
        .then(({ data: contacts }) => {
          this.trashedContact = contacts.length > 0 ? contacts[0] : null
        })
    }, 500),
  },
  computed: {
    /**
     * Get the via resource record
     *
     * @return {Object}
     */
    resourceRecord() {
      return this.viaResource ? this.$store.state[this.viaResource].record : {}
    },

    /**
     * Determine the modal title
     *
     * @return {String}
     */
    modalTitle() {
      if (!this.viaResource) {
        return this.$t('contact.create')
      }

      if (!this.hasSelectedExistingContact) {
        return this.$t('contact.create_with', {
          name: this.resourceRecord.display_name,
        })
      }

      return this.$t('contact.associate_with', {
        name: this.resourceRecord.display_name,
      })
    },

    /**
     * Indicates whether the user has selected existing contact
     *
     * @return {Boolean}
     */
    hasSelectedExistingContact() {
      return !!this.associateField.find('id').currentValue
    },
  },
  methods: {
    /**
     * Restore the found trashed contact by email
     *
     * @return {Void}
     */
    restoreTrashed() {
      Innoclapps.request()
        .post('/trashed/contacts/' + this.trashedContact.id)
        .then(() => {
          this.$router.push({
            name: 'view-contact',
            params: { id: this.trashedContact.id },
          })
        })
    },

    /**
     * Associate contact to resource
     *
     * @return {Void}
     */
    associate() {
      this.fillFormFields(this.associateForm, 'associateField')
      Innoclapps.request()
        .put('associations/contacts/' + this.associateForm.id, {
          [this.viaResource]: [this.resourceRecord.id],
        })
        .then(({ data }) => {
          Innoclapps.success(this.$t('resource.associated'))
          this.$emit('created', data)
          this.goBack()
        })
    },

    /**
     * Store contact in storage
     *
     * @return {Void}
     */
    store() {
      this.request().then(contact => {
        if (this.viaResource) {
          this.goBack()
          return
        }

        this.$router.contact = contact
        this.$router.push({
          name: 'view-contact',
          params: {
            id: contact.id,
          },
        })
      })
    },

    /**
     * Store contact in storage and add another
     *
     * @return {Void}
     */
    storeAndAddAnother() {
      this.request().then(contact => this.resetFormFields(this.form))
    },

    /**
     * Store contact in storage and go to list view
     *
     * @return {Void}
     */
    storeAndGoToList() {
      this.request().then(contact => this.$router.push('/contacts'))
    },

    /**
     * Perform request
     *
     * @return {Promise}
     */
    async request() {
      let contact = await this.$store
        .dispatch('contacts/store', this.fillFormFields(this.form))
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(this.$t('app.form_validation_failed'), 3000)
          }
          return Promise.reject(e)
        })

      Innoclapps.success(this.$t('resource.created'))

      this.$emit('created', contact)

      return contact
    },

    /**
     * Prepare component
     *
     * @return {Void}
     */
    prepareComponent() {
      this.$store
        .dispatch('fields/getForResource', {
          resourceName: Innoclapps.config.fields.groups.contacts,
          view: Innoclapps.config.fields.views.create,
        })
        .then(fields => {
          this.setFields(fields)
          this.emailField = this.fields.find('email')

          if (this.viaResource) {
            this.fields.update(this.viaResource, {
              value: [this.resourceRecord],
            })
          }
        })
    },
  },
  created() {
    this.prepareComponent()
  },
  mounted() {
    this.setPageTitle(this.modalTitle)
  },
}
</script>
