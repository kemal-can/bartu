<template>
  <i-slideover
    id="dealModal"
    :ok-title="$t('app.create')"
    :ok-disabled="form.busy"
    :cancel-title="$t('app.cancel')"
    form
    @submit="store"
    @hidden="handleModalHiddenEvent"
    :title="$t('deal.create')"
    :visible="true"
    static-backdrop
  >
    <form-fields-placeholder v-if="!fieldsConfigured" />

    <fields-generator
      :form="form"
      :fields="fields"
      view="create"
      :is-floating="true"
    />
  </i-slideover>
</template>
<script>
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'

export default {
  emits: ['created', 'hidden'],
  mixins: [InteractsWithResourceFields],
  props: {
    message: { required: true, type: Object },
  },
  data: () => ({
    form: new Form(),
  }),
  methods: {
    /**
     * Hide the create modal
     *
     * @return {Void}
     */
    hide() {
      this.$iModal.hide('dealModal')
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
     * Store the deal in storage
     *
     * @return {Promise}
     */
    store() {
      this.form.fill('emails', [this.message.id])

      return this.$store
        .dispatch('deals/store', this.fillFormFields(this.form))
        .then(deal => {
          Innoclapps.success(this.$t('resource.created'))
          this.$emit('created', deal)
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
     * Prepare the component
     *
     * @return {Void}
     */
    async prepareComponent() {
      let { data: contacts } = await Innoclapps.request().get(
        'contacts/search',
        {
          params: {
            q: this.message.from.address,
            search_fields: 'email:=',
          },
        }
      )

      let fields = await this.$store.dispatch('fields/getForResource', {
        resourceName: config.fields.groups.deals,
        view: config.fields.views.create,
      })

      this.setFields(fields)

      this.fields.update('contacts', {
        value: contacts,
      })

      this.fields.update('name', {
        value: this.message.subject,
      })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
