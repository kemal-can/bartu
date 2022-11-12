<template>
  <i-card :overlay="!fieldsConfigured">
    <fields-generator
      :form="form"
      :via-resource="resourceName"
      :via-resource-id="resourceRecord.id"
      view="update"
      :fields="fields"
    />
    <template #footer>
      <div class="flex justify-end space-x-2">
        <i-button variant="white" size="sm" @click="$emit('cancelled')">{{
          $t('app.cancel')
        }}</i-button>
        <i-button
          variant="primary"
          size="sm"
          @click="update"
          :disabled="form.busy"
          >{{ $t('app.save') }}</i-button
        >
      </div>
    </template>
  </i-card>
</template>
<script>
import Editor from '@/components/Editor'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import Form from '@/components/Form/Form'

export default {
  mixins: [InteractsWithResource, InteractsWithResourceFields],
  emits: ['updated', 'cancelled'],
  components: { Editor },
  props: {
    call: { type: Object, required: true },
  },
  data: () => ({
    form: new Form(),
  }),
  methods: {
    /**
     * Handle call updated event
     *
     * @param  {Object} call
     *
     * @return {Void}
     */
    handleCallUpdated(call) {
      this.updateResourceRecordHasManyRelationship(call, 'calls')

      this.$emit('updated', call)

      Innoclapps.success(this.$t('call.updated'))
    },

    /**
     * Update call in storage
     *
     * @return {Void}
     */
    update() {
      this.setFormResourceParams(this.form)

      this.fillFormFields(this.form)
        .put(`/calls/${this.call.id}`)
        .then(call => this.handleCallUpdated(call))
    },

    /**
     * Set form resource params
     *
     * @param {Object} form
     */
    setFormResourceParams(form) {
      form.withQueryString({
        via_resource: this.resourceName,
        via_resource_id: this.resourceRecord.id,
      })
    },

    /**
     * Prepare the component for update
     *
     * @return {Void}
     */
    prepareComponent() {
      this.$store
        .dispatch('fields/getForResource', {
          resourceName: Innoclapps.config.fields.groups.calls,
          view: Innoclapps.config.fields.views.update,
          viaResource: this.resourceName,
          viaResourceid: this.resourceRecord.id,
          resourceId: this.call.id,
        })
        .then(fields => this.setFieldsForUpdate(fields, this.call))
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
