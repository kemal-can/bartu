<template>
  <i-slideover
    @shown="$emit('shown')"
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

    <div v-show="!hasSelectedExistingDeal">
      <focus-able-fields-generator
        :form="form"
        :fields="fields"
        view="create"
        :is-floating="true"
      />
    </div>

    <template #modal-ok>
      <div v-show="!hasSelectedExistingDeal">
        <i-dropdown-button-group
          placement="top-end"
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
      </div>
      <i-button
        v-show="hasSelectedExistingDeal"
        variant="primary"
        @click="associate"
        >{{ $t('app.associate') }}</i-button
      >
    </template>
  </i-slideover>
</template>
<script>
import FieldsCollection from '@/services/FieldsCollection'
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'

export default {
  emits: ['created'],
  mixins: [InteractsWithResourceFields],
  props: {
    viaResource: String,
  },
  data() {
    return {
      form: new Form(),
      associateForm: new Form({
        id: null,
      }),
      associateField: new FieldsCollection({
        asyncUrl: '/deals/search',
        attribute: 'id',
        component: 'select-field',
        helpText: this.$t('deal.associate_field_info'),
        helpTextDisplay: 'text',
        label: this.$t('deal.deal'),
        labelKey: 'name',
        valueKey: 'id',
      }),
    }
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
        return this.$t('deal.create')
      }

      if (!this.hasSelectedExistingDeal) {
        return this.$t('deal.create_with', {
          name: this.resourceRecord.display_name,
        })
      }

      return this.$t('deal.associate_with', {
        name: this.resourceRecord.display_name,
      })
    },

    /**
     * Indicates whether the user has selected existing deal
     *
     * @return {Boolean}
     */
    hasSelectedExistingDeal() {
      return !!this.associateField.find('id').currentValue
    },
  },
  methods: {
    /**
     * Associate deal to resource
     *
     * @return {Void}
     */
    associate() {
      this.fillFormFields(this.associateForm, 'associateField')

      Innoclapps.request()
        .put('associations/deals/' + this.associateForm.id, {
          [this.viaResource]: [this.resourceRecord.id],
        })
        .then(({ data }) => {
          Innoclapps.success(this.$t('resource.associated'))
          this.$emit('created', data)
          this.goBack()
        })
    },

    /**
     * Store deal in storage
     *
     * @return {Void}
     */
    store() {
      this.request().then(deal => {
        if (this.viaResource) {
          this.goBack()
          return
        }

        this.$router.deal = deal
        this.$router.push({
          name: 'view-deal',
          params: {
            id: deal.id,
          },
        })
      })
    },

    /**
     * Store deal in storage and add another
     *
     * @return {Void}
     */
    storeAndAddAnother() {
      this.request().then(deal => this.resetFormFields(this.form))
    },

    /**
     * Store deal in storage and go to list view
     *
     * @return {Void}
     */
    storeAndGoToList() {
      this.request().then(deal => this.$router.push('/deals'))
    },

    /**
     * Perform request
     *
     * @return {Promise}
     */
    async request() {
      let deal = await this.$store
        .dispatch('deals/store', this.fillFormFields(this.form))
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(this.$t('app.form_validation_failed'), 3000)
          }
          return Promise.reject(e)
        })

      Innoclapps.success(this.$t('resource.created'))

      this.$emit('created', deal)

      return deal
    },

    /**
     * Prepare the component
     *
     * @return {Void}
     */
    prepareComponent() {
      this.$store
        .dispatch('fields/getForResource', {
          resourceName: Innoclapps.config.fields.groups.deals,
          view: Innoclapps.config.fields.views.create,
        })
        .then(fields => {
          this.setFields(fields)

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
