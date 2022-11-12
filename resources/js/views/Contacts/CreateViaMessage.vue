<template>
  <i-slideover
    id="contactModal"
    :ok-title="$t('app.create')"
    :ok-disabled="form.busy"
    :cancel-title="$t('app.cancel')"
    @ok="store"
    @hidden="handleModalHiddenEvent"
    :title="$t('contact.create')"
    :visible="true"
    static-backdrop
    form
    @submit="store"
  >
    <form-fields-placeholder v-if="!fieldsConfigured" />

    <fields-generator
      :form="form"
      view="create"
      :is-floating="true"
      :fields="fields"
    />
  </i-slideover>
</template>
<script>
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'

export default {
  emits: ['created', 'hidden'],
  mixins: [InteractsWithResourceFields],
  data: () => ({
    form: new Form({
      avatar: null,
    }),
  }),
  props: {
    message: { required: true, type: Object },
  },
  computed: {
    /**
     * Inicates whether the message from has name set
     *
     * @return {Boolean}
     */
    hasName() {
      return Boolean(this.message.from.name)
    },

    /**
     * Get the first name from message
     *
     * @return {String|null}
     */
    firstName() {
      if (this.hasName) {
        return this.message.from.name.split(' ')[0]
      }
    },

    /**
     * Get the last name from message
     *
     * @return {String|null}
     */
    lastName() {
      if (this.hasName) {
        return this.message.from.name.split(' ')[1] || null
      }
    },
  },
  methods: {
    /**
     * Hide the create modal
     *
     * @return {Void}
     */
    hide() {
      this.$iModal.hide('contactModal')
    },

    /**
     * Handle the modal hidden event
     *
     * @return {Void}
     */
    handleModalHiddenEvent() {
      this.$emit('hidden')
    },

    /**
     * Store the contact in storage
     *
     * @return {Promise}
     */
    store() {
      this.form.fill('emails', [this.message.id])

      return this.$store
        .dispatch('contacts/store', this.fillFormFields(this.form))
        .then(contact => {
          Innoclapps.success(this.$t('resource.created'))
          this.$emit('created', contact)
          this.hide()
        })
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(this.$t('app.form_validation_failed'), 3000)
          }
          return Promise.reject(e)
        })
    },

    /**
     * Prepare the component fields
     *
     * @param  {Object} fields
     *
     * @return {Void}
     */
    prepareFields(fields) {
      this.setFields(fields)

      this.fields.update('email', {
        value: this.message.from.address,
      })

      this.fields.update('first_name', {
        value: this.firstName,
      })

      this.fields.update('last_name', {
        value: this.lastName,
      })
    },

    /**
     * Initialize the component
     *
     * @return {Void}
     */
    async initializeComponent() {
      let fields = await this.$store.dispatch('fields/getForResource', {
        resourceName: Innoclapps.config.fields.groups.contacts,
        view: Innoclapps.config.fields.views.create,
      })

      this.prepareFields(fields)
    },
  },
  created() {
    this.initializeComponent()
  },
}
</script>
