<template>
  <i-slideover
    @hidden="handleModalHiddenEvent"
    @shown="handleModalShowEvent"
    :cancel-title="$t('app.cancel')"
    :ok-title="$t('app.create')"
    :visible="visible"
    :ok-disabled="form.busy"
    id="createActivityModal"
    @ok="store"
    static-backdrop
    :title="$t('activity.create')"
    form
    @submit="store"
  >
    <form-fields-placeholder v-if="!fieldsConfigured" />

    <focus-able-fields-generator
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
import map from 'lodash/map'

export default {
  emits: ['created', 'hidden'],
  mixins: [InteractsWithActivityFields],
  data: () => ({
    form: new Form(),
  }),
  props: [
    'title',
    'note',
    'description',
    'activityTypeId',
    'contacts',
    'companies',
    'deals',
    'dueDate',
    'endDate',
    'reminderMinutesBefore',
    'hideOnCreated',
    'visible',
  ],
  methods: {
    /**
     * Store the activity in storage
     *
     * @return {Void}
     */
    store() {
      this.$store
        .dispatch('activities/store', this.fillFormFields(this.form))
        .then(() => {
          Innoclapps.success(this.$t('activity.created'))
          this.$emit('created')

          if (this.hideOnCreated) {
            this.$iModal.hide('createActivityModal')
          }
        })
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(this.$t('app.form_validation_failed'), 3000)
          }
          return Promise.reject(e)
        })
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
     * Handle the modal show event
     *
     * @return {Void}
     */
    handleModalShowEvent() {
      this.initializeComponent()
    },

    /**
     * Initialize the component
     *
     * @return {Void}
     */
    initializeComponent() {
      this.getActivityCreateFields().then(fields => {
        this.setFields(
          map(this.cleanObject(fields), field => {
            if (
              [
                'contacts',
                'companies',
                'deals',
                'title',
                'note',
                'description',
              ].indexOf(field.attribute) > -1
            ) {
              field.value = this[field.attribute]
            } else if (
              field.attribute === 'activity_type_id' &&
              this.activityTypeId
            ) {
              field.value = this.activityTypeId
            } else if (field.attribute === 'due_date' && this.dueDate) {
              field.value = this.dueDate // object
            } else if (field.attribute === 'end_date' && this.endDate) {
              field.value = this.endDate // object
            } else if (
              field.attribute === 'reminder_minutes_before' &&
              this.reminderMinutesBefore
            ) {
              field.value = this.reminderMinutesBefore
            }

            return field
          })
        )
      })
    },
  },
}
</script>
