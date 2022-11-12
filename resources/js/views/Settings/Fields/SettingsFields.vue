<template>
  <div>
    <i-modal
      id="newCustomField"
      size="sm"
      @shown="handleCreateModalShown"
      @hidden="handleModalHidden"
      :ok-title="$t('app.save')"
      :cancel-title="$t('app.cancel')"
      :title="$t('fields.custom.create')"
      form
      @keydown="form.onKeydown($event)"
      @submit="storeCustomField"
      static-backdrop
    >
      <custom-field-form-fields :form="form" />
    </i-modal>
    <i-modal
      size="sm"
      id="editCustomField"
      form
      @keydown="form.onKeydown($event)"
      @submit="updateCustomField"
      @hidden="handleModalHidden"
      :ok-title="$t('app.save')"
      :cancel-title="$t('app.cancel')"
      :title="$t('fields.custom.update')"
      static-backdrop
    >
      <custom-field-form-fields :form="form" :edit="true" />
    </i-modal>
    <i-card no-body :header="title" class="mb-3">
      <template #actions>
        <i-button
          variant="white"
          icon="Plus"
          size="sm"
          v-i-modal="'newCustomField'"
          class="ml-3"
        >
          {{ $t('fields.add') }}
        </i-button>
      </template>
    </i-card>
    <div v-show="!editingRelatedFieldResource">
      <customize-fields
        v-if="resourceName !== 'products'"
        class="mb-3"
        ref="detail"
        :group="resourceName"
        :view="detailView"
        :heading="$t('fields.settings.detail')"
        :sub-heading="$t('fields.settings.detail_info')"
        @delete-requested="destroy"
        @update-requested="handleUpdateRequestedEvent"
      />
      <customize-fields
        class="mb-3"
        ref="create"
        :group="resourceName"
        :view="createView"
        :heading="$t('fields.settings.create')"
        :sub-heading="$t('fields.settings.create_info')"
        @delete-requested="destroy"
        @update-requested="handleUpdateRequestedEvent"
        :collapse-option="false"
      />
      <customize-fields
        class="mb-3"
        ref="update"
        :group="resourceName"
        :heading="$t('fields.settings.update')"
        :sub-heading="$t('fields.settings.update_info')"
        @delete-requested="destroy"
        @update-requested="handleUpdateRequestedEvent"
        :view="updateView"
      />
      <i-card class="mh-96" no-body>
        <template #header>
          <i-card-heading class="text-base" v-t="'fields.settings.list'" />
        </template>

        <i18n-t
          scope="global"
          tag="p"
          class="inline-block px-4 py-4 text-sm text-neutral-600 dark:text-white sm:px-6"
          keypath="fields.settings.list_info"
        >
          <template #icon>
            <icon icon="DotsHorizontal" class="mx-1 inline h-5 w-5" />
          </template>
          <template #resourceName>
            {{ resource.label }}
          </template>
        </i18n-t>
      </i-card>
    </div>
    <related-field-resource
      v-if="editingRelatedFieldResource"
      :resource-name="editingRelatedFieldResource"
      @created="refreshFieldsData"
      @updated="refreshFieldsData"
      @deleted="refreshFieldsData"
      @cancel="editingRelatedFieldResource = null"
    />
  </div>
</template>
<script>
import CustomizeFields from '@/views/Settings/Fields/SettingsCustomizeFields'
import CustomFieldFormFields from '@/views/Settings/Fields/CustomFieldFormFields'
import CardHeaderGridBackground from '@/components/Cards/HeaderGridBackground'
import RelatedFieldResource from '@/components/SimpleResourceCRUD'
import Form from '@/components/Form/Form'
import cloneDeep from 'lodash/cloneDeep'

export default {
  components: {
    CustomizeFields,
    CardHeaderGridBackground,
    CustomFieldFormFields,
    RelatedFieldResource,
  },
  watch: {
    resourceName: function (newVal, oldVal) {
      // When navigating out of the setting route and the resource in the URL is removed
      // this watcher is triggered and in this case, the resource and the titles are null, no need
      // to set any title
      if (this.title) {
        this.setPageTitle(this.title)
      }

      // In case the user is editing some field data
      // to show the fields again, we need to set the editingRelatedFieldResource to null
      this.editingRelatedFieldResource = null
    },
  },
  data() {
    return {
      form: new Form({
        label: null,
        field_type: 'Text',
        field_id: null,
        resource_name: null,
        options: [],
      }),
      editingRelatedFieldResource: null,
      createView: Innoclapps.config.fields.views.create,
      updateView: Innoclapps.config.fields.views.update,
      detailView: Innoclapps.config.fields.views.detail,
    }
  },
  computed: {
    /**
     * Get the component title
     *
     * @return {String}
     */
    title() {
      if (!this.resource) {
        return null
      }

      return this.$t('resource.settings.fields', {
        resourceName: this.resource.singularLabel,
      })
    },

    /**
     * Get the resource name
     *
     * @return {String}
     */
    resourceName() {
      return this.$route.params.resourceName
    },

    /**
     * Get the resources the fields are intended for
     *
     * @return {Object}
     */
    resource() {
      return Innoclapps.config.resources[this.resourceName]
    },
  },
  methods: {
    /**
     * Initialize edit for custom field
     *
     * @param  {Object} field
     *
     * @return {Void}
     */
    handleUpdateRequestedEvent(field) {
      if (!field.customField) {
        this.editingRelatedFieldResource = field.optionsViaResource
        return
      }

      this.form.fill('id', field.customField.id)
      this.form.fill('label', field.customField.label)
      this.form.fill('field_type', field.customField.field_type)
      this.form.fill('field_id', field.customField.field_id)
      this.form.fill('resource_name', field.customField.resource_name)
      // Clone options deep, when removing an option and not saving
      // the custom field, will remove the option from the field.customField.options array
      // too, in this case, we need the option to the original field in case
      // user access edit again to be shown on the form
      this.form.fill('options', cloneDeep(field.customField.options) || [])

      this.$iModal.show('editCustomField')
    },

    /**
     * Update custom field
     *
     * @return {Void}
     */
    updateCustomField() {
      this.form.put('/custom-fields/' + this.form.id).then(field => {
        this.$iModal.hide('editCustomField')
        this.form.reset()

        this.refreshFieldsData()
        Innoclapps.success(this.$t('fields.custom.updated'))
      })
    },

    /**
     * Delete custom field by given id
     *
     * @param  {Int} id
     *
     * @return {Void}
     */
    async destroy(id) {
      await this.$dialog.confirm()

      Innoclapps.request()
        .delete('/custom-fields/' + id)
        .then(() => {
          this.refreshFieldsData()
          Innoclapps.success(this.$t('fields.custom.deleted'))
        })
    },

    /**
     * Refresh the fields data
     *
     * @return {Void}
     */
    refreshFieldsData() {
      this.$store.commit('table/RESET_SETTINGS')
      const fieldsRefs = ['create', 'update', 'detail']
        .map(view => this.$refs[view] || null)
        .filter(ref => ref !== null)

      Promise.all(fieldsRefs.map(ref => ref.fetch())).then(() => {
        this.$nextTick(() => fieldsRefs.forEach(ref => ref.submit()))
      })
    },

    /**
     * Create new custom field
     *
     * @return {Void}
     */
    storeCustomField() {
      this.form.post('/custom-fields').then(field => {
        this.$iModal.hide('newCustomField')
        this.form.reset()

        this.refreshFieldsData()
        Innoclapps.success(this.$t('fields.custom.created'))
      })
    },

    /**
     * Handle the create modal created event
     *
     * @return {Void}
     */
    handleCreateModalShown() {
      this.form.fill('resource_name', this.resourceName)
    },

    /**
     * Handle the modals hidden event
     *
     * @return {Void}
     */
    handleModalHidden() {
      this.form.reset()
      this.form.errors.clear()
    },
  },
}
</script>
