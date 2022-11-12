<template>
  <div>
    <i-card
      no-body
      :header="$t('activity.activities')"
      class="mb-3"
      :overlay="!componentReady"
    >
      <ul class="divide-y divide-neutral-200 dark:divide-neutral-700">
        <li class="px-4 py-4 sm:px-6">
          <div
            class="space-y-3 space-x-0 md:flex md:items-center md:justify-between md:space-y-0 lg:space-x-3"
          >
            <div>
              <h5
                class="font-medium leading-relaxed text-neutral-700 dark:text-neutral-100"
                v-t="'activity.settings.send_contact_email'"
              />
              <p
                class="text-sm text-neutral-600 dark:text-neutral-300"
                v-t="'activity.settings.send_contact_email_info'"
              />
            </div>
            <div>
              <i-form-toggle
                :value="true"
                :unchecked-value="false"
                @change="submit"
                v-model="form.send_contact_attends_to_activity_mail"
              />
            </div>
          </div>
        </li>
        <li class="px-4 py-4 sm:px-6">
          <i-form-group
            :label="$t('activity.type.default_type')"
            class="mb-0"
            label-for="default_activity_type"
          >
            <i-custom-select
              input-id="default_activity_type"
              v-model="defaultType"
              class="xl:w-1/3"
              :clearable="false"
              @option:selected="handleActivityTypeInputEvent"
              label="name"
              :options="types"
            >
            </i-custom-select>
          </i-form-group>
        </li>
      </ul>
    </i-card>
    <activity-type-index></activity-type-index>
  </div>
</template>
<script>
import HandleSettingsForm from './HandleSettingsForm'
import ActivityTypeIndex from '@/views/Activity/TypesIndex'
import { mapState } from 'vuex'
export default {
  mixins: [HandleSettingsForm],
  components: { ActivityTypeIndex },
  data: () => ({
    defaultType: null,
  }),
  computed: {
    ...mapState({
      types: state => state.activities.types,
    }),
  },
  methods: {
    handleActivityTypeInputEvent(e) {
      this.form.default_activity_type = e.id
      this.submit(() => this.resetStoreState())
    },
  },
  created() {
    const unwatcher = this.$watch('componentReady', function (newVal, oldVal) {
      this.defaultType = this.types.find(
        type => type.id == this.form.default_activity_type
      )
      unwatcher()
    })
  },
}
</script>
