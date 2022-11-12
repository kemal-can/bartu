<template>
  <form @submit.prevent="update" method="POST">
    <i-card :overlay="!componentReady">
      <fields-generator
        :form="recordForm"
        view="update"
        :via-resource="resourceName"
        :via-resource-id="resourceRecord.id"
        :fields="fields"
      />
      <template #footer>
        <div
          class="flex w-full flex-col sm:w-auto sm:flex-row sm:items-center sm:justify-end"
        >
          <i-form-toggle
            class="mr-4 mb-4 pr-4 sm:mb-0 sm:border-r sm:border-neutral-200 sm:dark:border-neutral-700"
            :label="$t('activity.mark_as_completed')"
            v-model="recordForm.is_completed"
          />
          <i-button
            class="mb-2 ml-0 sm:mb-0 sm:mr-2"
            variant="white"
            size="sm"
            @click="$emit('cancelled', $event)"
            >{{ $t('app.cancel') }}</i-button
          >
          <i-button
            type="submit"
            variant="primary"
            @click="update"
            size="sm"
            :disabled="recordForm.busy"
            >{{ $t('app.save') }}</i-button
          >
        </div>
      </template>
    </i-card>
  </form>
</template>
<script>
import HandlesResourceUpdate from '@/mixins/HandlesResourceUpdate'
import InteractsWithActivityFields from '@/views/Activity/InteractsWithActivityFields'
import InteractsWithResource from '@/mixins/InteractsWithResource'
export default {
  emits: ['cancelled', 'updated'],
  mixins: [
    InteractsWithActivityFields,
    HandlesResourceUpdate,
    InteractsWithResource,
  ],
  props: {
    activityId: {
      type: Number,
      required: true,
    },
  },
  methods: {
    /**
     * Get the record fields
     *
     * @return {Promise}
     */
    getResourceUpdateFields() {
      return this.getActivityUpdateFieldsForResource(this.activityId)
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
     * Handle activity updated event
     *
     * @param  {Object} record
     *
     * @return {Void}
     */
    handleActivityUpdated(record) {
      this.updateResourceRecordHasManyRelationship(record, 'activities')

      this.$emit('updated', record)
    },
  },
  beforeMount() {
    this.bootRecordUpdate({
      resource: 'activities',
      id: this.activityId,
      beforeUpdateRecord: this.setFormResourceParams,
      beforeSetRecord: record => {
        do {
          if (!this.componentReady) {
            return
          }

          this.fields.update('guests', {
            activity: record,
          })
        } while (!this.componentReady)
      },
      // For checkbox mark as completed
      afterFieldsConfigured: () =>
        this.recordForm.set('is_completed', this.record.is_completed),
    })
  },
  mounted() {
    Innoclapps.$on('activities-record-updated', this.handleActivityUpdated)
  },
  unmounted() {
    Innoclapps.$off('activities-record-updated', this.handleActivityUpdated)
  },
}
</script>
