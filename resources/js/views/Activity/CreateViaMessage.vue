<template>
  <i-slideover
    id="createActivityModal"
    :ok-title="$t('app.create')"
    :ok-disabled="form.busy"
    :cancel-title="$t('app.cancel')"
    @ok="store"
    @hidden="handleModalHiddenEvent"
    :title="$t('activity.create')"
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
import InteractsWithActivityFields from '@/views/Activity/InteractsWithActivityFields'
import Form from '@/components/Form/Form'

export default {
  emits: ['created', 'hidden'],
  mixins: [InteractsWithActivityFields],
  data: () => ({
    form: new Form(),
  }),
  props: {
    message: {
      required: true,
      type: Object,
    },
  },
  methods: {
    /**
     * Hide the create modal
     *
     * @return {Void}
     */
    hide() {
      this.$iModal.hide('createActivityModal')
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
     * Store the message in storage
     *
     * @return {Void}
     */
    store() {
      this.$store
        .dispatch('activities/store', this.fillFormFields(this.form))
        .then(activity => {
          Innoclapps.success(this.$t('activity.created'))
          this.$emit('created', activity)
          this.hide()
        })
    },

    /**
     * Initialize the component
     *
     * @return {Void}
     */
    async initializeComponent() {
      let fields = await this.getActivityCreateFields()

      let { data: contacts } = await Innoclapps.request().get(
        'contacts/search',
        {
          params: {
            q: this.message.from.address,
            search_fields: 'email:=',
          },
        }
      )

      this.setFields(fields)

      this.fields.update('contacts', {
        value: contacts,
      })

      this.fields.update('title', {
        value: this.$t('activity.title_via_create_message', {
          subject: this.message.subject,
        }),
      })
    },
  },
  created() {
    this.initializeComponent()
  },
}
</script>
