<template>
  <div class="mb-2 sm:mb-4">
    <i-card v-if="logPhoneCall || makePhoneCall">
      <template #actions v-if="hasVoIPClient && $gate.userCan('use voip')">
        <div class="text-right">
          <make-call-button
            :resource-name="resourceName"
            @call-requested="newCall"
          />
        </div>
      </template>

      <i-form-group v-show="makePhoneCall">
        <i-form-textarea :placeholder="$t('call.take_notes')" rows="5" />
      </i-form-group>
      <i-overlay :show="!fieldsConfigured">
        <fields-generator
          :form="form"
          view="create"
          :via-resource="resourceName"
          :via-resource-id="resourceRecord.id"
          :fields="fields"
        />
      </i-overlay>
      <template #footer>
        <div class="flex flex-col sm:flex-row sm:items-center">
          <div class="grow">
            <create-follow-up-task :form="form" />
          </div>
          <div class="mt-2 space-y-2 sm:mt-0 sm:space-y-0 sm:space-x-2">
            <i-button
              class="w-full sm:w-auto"
              variant="white"
              size="sm"
              @click=";(logPhoneCall = false), (makePhoneCall = false)"
              >{{ $t('app.cancel') }}</i-button
            >
            <i-button
              class="w-full sm:w-auto"
              size="sm"
              :disabled="form.busy"
              @click="store"
              >{{ $t('call.add') }}</i-button
            >
          </div>
        </div>
      </template>
    </i-card>
    <div
      class="mb-4 block"
      v-show="!logPhoneCall && !makePhoneCall"
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
              icon="Phone"
              class="h-5 w-5 text-neutral-700 dark:text-neutral-200"
            />
          </span>

          <div class="sm:ml-4">
            <div
              class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
              v-t="'call.info'"
            />
          </div>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-6 sm:flex-shrink-0">
          <i-button @click="showCreateCallForm" icon="Plus" size="sm">
            {{ $t('call.add') }}
          </i-button>
          <make-call-button
            v-if="$gate.userCan('use voip')"
            class="ml-2"
            :resource-name="resourceName"
            @call-requested="newCall"
          />
        </div>
      </div>
    </div>

    <div
      v-show="!logPhoneCall && !makePhoneCall && !showIntroductionSection"
      class="mb-8 text-right"
    >
      <i-button @click="showCreateCallForm" icon="Plus" size="sm">
        {{ $t('call.add') }}
      </i-button>
      <make-call-button
        v-if="$gate.userCan('use voip')"
        class="ml-2"
        :resource-name="resourceName"
        @call-requested="newCall"
      />
    </div>
  </div>
</template>
<script>
import CreateFollowUpTask from '@/views/Activity/CreateFollowUpTask'
import InteractsWithResourceFields from '@/mixins/InteractsWithResourceFields'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import MakeCallButton from '@/views/Calls/MakeCallButton'
import Form from '@/components/Form/Form'

export default {
  mixins: [InteractsWithResourceFields, InteractsWithResource],
  components: {
    CreateFollowUpTask,
    MakeCallButton,
  },
  props: {
    showIntroductionSection: {
      default: true,
      type: Boolean,
    },
  },
  data: () => ({
    logPhoneCall: false,
    makePhoneCall: false,
    form: new Form({
      with_task: false,
      task_date: null,
    }),
  }),
  methods: {
    /**
     * Show the create call form
     *
     * @return {Void}
     */
    showCreateCallForm() {
      this.logPhoneCall = true

      if (!this.fieldConfigured) {
        this.$store
          .dispatch('fields/getForResource', {
            resourceName: Innoclapps.config.fields.groups.calls,
            view: Innoclapps.config.fields.views.create,
            viaResource: this.resourceName,
            viaResourceid: this.resourceRecord.id,
          })
          .then(fields => this.setFields(fields))
      }
    },

    /**
     * Initiate new call
     *
     * @param {String} phoneNumber
     *
     * @return {Promise}
     */
    async newCall(phoneNumber) {
      this.makePhoneCall = true
      this.showCreateCallForm()
      let call = await this.$voip.makeCall(phoneNumber)
    },

    /**
     * Action executed after the call is created
     *
     * @param  {Object} call
     *
     * @return {Void}
     */
    handleCallCreated(call) {
      this.resetFormFields(this.form)
      // Reset this data to false, as if the user performed a call
      // before logging a call, the notes textarea will be shown,
      // now the call is disconnected and he logged call, after the call
      // is logged, just reset the makePhoneCall to hide the notes textarea because
      // we can't remove the notes textarea after the call is disconnected
      // as the user may have wroted notes in the textarea!
      this.makePhoneCall = false

      if (call.createdActivity) {
        this.addResourceRecordHasManyRelationship(
          call.createdActivity,
          'activities'
        )
        this.incrementResourceRecordCount(
          'incomplete_activities_for_user_count'
        )

        delete call.createdActivity
      }

      this.addResourceRecordHasManyRelationship(call, 'calls')
      this.incrementResourceRecordCount('calls_count')
      Innoclapps.success(this.$t('call.created'))
    },

    /**
     * Create a call
     *
     * @return {Void}
     */
    store() {
      this.form.set(this.resourceName, [this.resourceRecord.id])

      this.form.withQueryString({
        via_resource: this.resourceName,
        via_resource_id: this.resourceRecord.id,
      })

      this.fillFormFields(this.form)
        .post('/calls')
        .then(call => this.handleCallCreated(call))
    },
  },
}
</script>
