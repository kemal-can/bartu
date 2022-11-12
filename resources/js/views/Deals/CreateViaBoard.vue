<template>
  <i-slideover
    @hidden="handleModalHiddenEvent"
    @shown="handleModalShownEvent"
    :visible="visible"
    :title="modalTitle"
    :id="modalId"
    static-backdrop
    form
    @submit="request"
  >
    <form-fields-placeholder v-if="!fieldsConfigured" />

    <focus-able-fields-generator
      :form="form"
      :fields="fields"
      view="create"
      :is-floating="true"
    />
    <template #modal-ok>
      <i-dropdown-button-group
        :disabled="form.busy"
        :loading="form.busy"
        :text="$t('app.create')"
        type="submit"
      >
        <i-dropdown-item
          @click="storeAndAddAnother"
          :text="$t('app.create_and_add_another')"
        />
        <i-dropdown-item
          @click="storeAndGoToList"
          :text="$t('app.create_and_go_to_list')"
        />
      </i-dropdown-button-group>
    </template>
  </i-slideover>
</template>
<script>
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import find from 'lodash/find'
import Form from '@/components/Form/Form'

export default {
  emits: ['created', 'hidden'],
  mixins: [InteractsWithResourceFields],
  props: {
    selectedPipeline: Object,
    selectedStageId: {},
    visible: { type: Boolean, default: false },
  },
  data() {
    return {
      modalId: 'create-deal-via-board',
      form: new Form(),
    }
  },
  computed: {
    modalTitle() {
      return this.$t('deal.create')
    },
  },
  methods: {
    /**
     * Hide the create deal modal
     *
     * @return {Void}
     */
    hide() {
      this.$iModal.hide(this.modalId)
    },

    /**
     * Handle the modal hidden event
     *
     * @return {Void}
     */
    handleModalHiddenEvent() {
      this.fields = []
      this.$emit('hidden')
    },

    /**
     * Handle the modal shown event
     *
     * @return {Void}
     */
    handleModalShownEvent() {
      this.prepareComponent()
    },

    /**
     * Store deal in storage and create another
     *
     * @return {Void}
     */
    storeAndAddAnother() {
      this.request(true).then(deal => this.resetFormFields(this.form))
    },

    /**
     * Store deal in storage and redirect to index
     *
     * @return {Void}
     */
    storeAndGoToList() {
      this.request().then(deal => this.$router.push('/deals'))
    },

    /**
     * Make store deal request
     *
     * @param  {Boolean} createAnother
     *
     * @return {Promise}
     */
    async request(createAnother = false) {
      let deal = await this.$store
        .dispatch('deals/store', this.fillFormFields(this.form))
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(this.$t('app.form_validation_failed'), 3000)
          }
          return Promise.reject(e)
        })

      this.$emit('created', {
        deal: deal,
        wantAnother: createAnother,
      })

      return deal
    },
    /**
     * Prepare the CreateViaBoard component
     *
     * @return {Promise}
     */
    async prepareComponent() {
      let fields = await this.$store.dispatch('fields/getForResource', {
        resourceName: Innoclapps.config.fields.groups.deals,
        view: Innoclapps.config.fields.views.create,
      })

      this.setFields(fields)

      this.fields.update('pipeline_id', {
        value: this.selectedPipeline,
        readonly: true,
      })

      // Sets to read only as if the user change the e.q. stage
      // manually will have unexpected results
      this.fields.update('stage_id', {
        value: this.selectedStageId
          ? find(this.selectedPipeline.stages, [
              'id',
              Number(this.selectedStageId),
            ])
          : null,
        readonly: this.selectedStageId ? true : false,
      })
    },
  },
}
</script>
