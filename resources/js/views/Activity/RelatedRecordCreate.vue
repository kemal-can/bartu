<template>
  <div class="mb-2 sm:mb-4">
    <form @submit.prevent="store" method="POST">
      <i-card v-show="activityBeingCreated" :overlay="!fieldsConfigured">
        <focus-able-fields-generator
          :form="form"
          :fields="fields"
          :via-resource="resourceName"
          :via-resource-id="resourceRecord.id"
          view="create"
        />
        <template #footer>
          <div
            class="flex w-full flex-wrap items-center justify-between sm:w-auto"
          >
            <div>
              <associations-popover
                :resource-name="resourceName"
                :associateable="resourceRecord"
                v-model="form.associations"
              />
            </div>
            <div
              class="mt-sm-0 mt-2 flex w-full flex-col sm:w-auto sm:flex-row sm:items-center sm:justify-end sm:space-x-2"
            >
              <i-form-toggle
                class="mr-4 mb-4 pr-4 sm:mb-0 sm:border-r sm:border-neutral-200 sm:dark:border-neutral-700"
                :label="$t('activity.mark_as_completed')"
                v-model="form.is_completed"
              />
              <i-button
                class="mb-2 sm:mb-0"
                variant="white"
                @click="activityBeingCreated = false"
                size="sm"
                >{{ $t('app.cancel') }}</i-button
              >
              <i-button
                type="submit"
                @click="store"
                size="sm"
                :disabled="form.busy"
                >{{ $t('activity.add') }}</i-button
              >
            </div>
          </div>
        </template>
      </i-card>
    </form>

    <div
      class="mb-4 block"
      v-show="!activityBeingCreated"
      v-if="showIntroductionSection"
    >
      <div
        class="rounded-md border border-neutral-200 bg-neutral-50 px-6 py-5 shadow-sm dark:border-neutral-900 dark:bg-neutral-900 sm:flex sm:items-start sm:justify-between"
      >
        <div class="sm:flex sm:items-center">
          <span
            class="hidden rounded border border-neutral-200 bg-neutral-100 px-3 py-1.5 dark:border-neutral-600 dark:bg-neutral-700/60 sm:inline-flex sm:self-start"
          >
            <icon
              icon="Calendar"
              class="h-5 w-5 text-neutral-700 dark:text-neutral-200"
            />
          </span>

          <div class="sm:ml-4">
            <div
              class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
              v-t="'activity.info'"
            />
          </div>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-6 sm:flex-shrink-0">
          <i-button @click="showCreateActivityForm" size="sm" icon="Plus">
            {{ $t('activity.add') }}
          </i-button>
        </div>
      </div>
    </div>

    <div
      v-show="!activityBeingCreated && !showIntroductionSection"
      class="mb-8 text-right"
    >
      <i-button @click="showCreateActivityForm" size="sm" icon="Plus">
        {{ $t('activity.add') }}
      </i-button>
    </div>
  </div>
</template>
<script>
import InteractsWithActivityFields from '@/views/Activity/InteractsWithActivityFields'
import AssociationsPopover from '@/components/AssociationsPopover'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import Form from '@/components/Form/Form'

export default {
  mixins: [InteractsWithActivityFields, InteractsWithResource],
  components: { AssociationsPopover },
  props: {
    showIntroductionSection: {
      default: true,
      type: Boolean,
    },
  },
  data: () => ({
    activityBeingCreated: false,
    form: new Form({
      is_completed: false,
    }),
  }),
  methods: {
    /**
     * Initialize the create fields
     */
    showCreateActivityForm() {
      if (!this.fieldsConfigured) {
        this.getActivityCreateFieldsForResource().then(fields =>
          this.setFields(fields)
        )
      }

      this.activityBeingCreated = true
    },

    /**
     * Store the activity in storage
     *
     * @return {Void}
     */
    store() {
      this.form.withQueryString({
        via_resource: this.resourceName,
        via_resource_id: this.resourceRecord.id,
      })

      this.$store
        .dispatch('activities/store', this.fillFormFields(this.form))
        .then(activity => {
          Innoclapps.success(this.$t('activity.created'))
          this.resetFormFields(this.form)
          this.incrementResourceRecordCount(
            'incomplete_activities_for_user_count'
          )
          this.addResourceRecordHasManyRelationship(activity, 'activities')
        })
    },
  },
  mounted() {
    // For form reset to have default value
    this.form.set('associations', {
      [this.resourceName]: [this.resourceRecord.id],
    })
  },
}
</script>
