<template>
  <i-card
    v-bind="$attrs"
    v-show="!activityBeingEdited"
    :class="['activity-' + activity.id]"
    footer-class="inline-flex flex-col w-full"
    no-body
  >
    <template #header>
      <div class="flex">
        <div class="mr-2 mt-px flex shrink-0 self-start">
          <state-change
            class="ml-px md:mt-px"
            @state-changed="handleActivityStateChanged"
            :activity="activity"
          />
        </div>
        <div
          class="flex grow flex-col space-y-1 md:flex-row md:space-y-0 md:space-x-3"
        >
          <div class="flex grow flex-col items-start">
            <h3
              class="truncate whitespace-normal text-base font-medium leading-6 text-neutral-700 dark:text-white"
              :class="{ 'line-through': activity.is_completed }"
            >
              {{ activity.title }}
            </h3>

            <associations-popover
              class="-mt-1"
              @change="syncAssociations"
              :resource-name="resourceName"
              :associateable="resourceRecord"
              :disabled="syncingAssociations"
              :associated="activity"
            />
          </div>
          <text-background
            :color="activity.type.swatch_color"
            class="inline-flex shrink-0 items-center self-start rounded-md py-0.5 dark:!text-white sm:rounded-full"
          >
            <dropdown-select
              :items="types"
              :model-value="activity.type"
              label-key="name"
              value-key="id"
              @change="updateActivity({ activity_type_id: $event.id })"
              v-if="activity.authorizations.update"
            >
              <template v-slot="{ item }">
                <button
                  type="button"
                  class="flex w-full items-center justify-between rounded-md px-2.5 text-sm leading-5 focus:outline-none"
                >
                  <div class="flex items-center">
                    <icon :icon="item.icon" class="mr-1 h-4 w-4" />
                    {{ item.name }}

                    <icon icon="ChevronDown" class="ml-1 h-4 w-4" />
                  </div>
                </button>
              </template>
            </dropdown-select>
            <span class="flex items-center px-1 text-sm" v-else>
              <icon :icon="activity.type.icon" class="mr-1 h-4 w-4" />
              {{ activity.type.name }}
            </span>
          </text-background>
        </div>
        <div class="ml-2 mt-px inline-flex self-start md:ml-5">
          <i-minimal-dropdown class="mt-1 md:mt-0.5">
            <i-dropdown-item
              @click="toggleEdit"
              v-if="activity.authorizations.update"
              :text="$t('app.edit')"
            />
            <i-dropdown-item
              @click="downloadICS"
              :text="$t('activity.download_ics')"
            />
            <i-dropdown-item
              v-if="activity.authorizations.delete"
              @click="destroy(activity.id)"
              :text="$t('app.delete')"
            />
          </i-minimal-dropdown>
        </div>
      </div>
    </template>

    <i-alert
      v-if="activity.is_due"
      variant="warning"
      icon="Clock"
      :rounded="false"
      class="-mt-px"
      wrapper-class="-ml-px sm:ml-1.5"
    >
      {{
        $t('activity.activity_was_due', {
          date: activity.due_date.time
            ? localizedDateTime(
                activity.due_date.date + ' ' + activity.due_date.time
              )
            : localizedDate(activity.due_date.date),
        })
      }}
    </i-alert>

    <div @dblclick="toggleEdit">
      <div
        v-if="activity.note"
        :class="[
          '-mt-px bg-warning-50',
          { 'border-t border-warning-100': Boolean(activity.note) },
        ]"
      >
        <text-collapse
          :text="activity.note"
          :length="100"
          class="wysiwyg-text px-4 py-2.5 text-warning-700 sm:px-6"
        >
          <template #action="{ collapsed, toggle }">
            <div
              v-show="collapsed"
              @click="toggle"
              class="absolute bottom-1 h-1/2 w-full cursor-pointer bg-gradient-to-t from-warning-50 to-transparent dark:from-warning-100"
            />

            <a
              href="#"
              v-show="!collapsed"
              class="my-2.5 inline-block px-4 text-sm font-medium text-warning-800 hover:text-warning-900 sm:px-6"
              @click.prevent="toggle"
              v-t="'app.show_less'"
            />
          </template>
        </text-collapse>
      </div>

      <i-card-body>
        <div class="space-y-4 sm:space-y-6">
          <div v-if="activity.description" class="mb-8">
            <p
              class="mb-3 inline-flex text-sm font-medium text-neutral-800 dark:text-white"
            >
              <icon icon="MenuAlt2" class="mr-3 h-5 w-5 text-current" />
              <span v-t="'activity.description'"></span>
            </p>
            <text-collapse
              :text="activity.description"
              :length="200"
              class="wysiwyg-text ml-8 dark:text-neutral-300 sm:mb-0"
            />
          </div>
          <div
            class="flex flex-col flex-wrap space-x-0 space-y-2 align-baseline lg:flex-row lg:space-x-4 lg:space-y-0"
          >
            <div
              v-if="activity.user"
              v-i-tooltip="$t('activity.owner')"
              class="self-start sm:self-auto"
            >
              <dropdown-select
                v-if="activity.authorizations.update"
                :items="users"
                :model-value="activity.user"
                toggle-class="px-0"
                value-key="id"
                label-key="name"
                @change="updateActivity({ user_id: $event.id })"
              >
                <template #label="{ item, label }">
                  <i-avatar size="xs" class="mr-3" :src="item.avatar_url" />
                  <span
                    class="text-neutral-800 hover:text-neutral-500 dark:text-neutral-200 dark:hover:text-neutral-400"
                    v-text="label"
                  />
                </template>
              </dropdown-select>
              <p
                v-else
                class="flex items-center text-sm font-medium text-neutral-800 dark:text-neutral-200"
              >
                <i-avatar
                  size="xs"
                  class="mr-3"
                  :src="activity.user.avatar_url"
                />
                {{ activity.user.name }}
              </p>
            </div>

            <activity-date-display
              class="font-medium"
              :due-date="activity.due_date"
              :end-date="activity.end_date"
              :is-due="activity.is_due"
            />
          </div>
        </div>
        <p
          v-if="activity.reminder_minutes_before && !activity.is_reminded"
          class="mt-2 flex items-center text-sm text-neutral-800 dark:text-neutral-200 sm:mt-3"
        >
          <icon
            icon="Bell"
            class="mr-3 h-5 w-5 text-neutral-800 dark:text-white"
          />

          <span>
            {{ reminderText }}
          </span>
        </p>
      </i-card-body>
    </div>
    <div
      class="border-y border-neutral-100 py-2.5 px-4 dark:border-neutral-800 sm:px-6"
    >
      <media-card
        :card="false"
        :show="attachmentsAreVisible"
        :wrapper-class="[
          'ml-8',
          {
            'py-4': totalAttachments === 0,
            'mb-4': totalAttachments > 0,
          },
        ]"
        class="mt-1"
        :record="activity"
        @deleted="handleActivityMediaDeleted"
        @uploaded="handleActivityMediaUploaded"
        resource-name="activities"
      >
        <template #heading>
          <p
            class="inline-flex items-center text-sm font-medium text-neutral-800 dark:text-white"
          >
            <icon icon="PaperClip" class="mr-3 h-5 w-5 text-current" />
            <a
              href="#"
              @click.prevent="attachmentsAreVisible = !attachmentsAreVisible"
              class="inline-flex items-center"
            >
              <span>
                {{ $t('app.attachments') }} ({{ totalAttachments }})
              </span>
              <icon
                :icon="attachmentsAreVisible ? 'ChevronDown' : 'ChevronRight'"
                class="ml-3 h-4 w-4"
              />
            </a>
          </p>
        </template>
      </media-card>
    </div>
    <div
      class="border-b border-neutral-100 px-4 py-2.5 dark:border-neutral-800 sm:px-6"
      v-show="activity.comments_count"
    >
      <comments-collapse
        :via-resource="resourceName"
        :commentable-id="activity.id"
        commentable-type="activities"
        :count="activity.comments_count"
        @update:count="
          $store.commit(resourceName + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP', {
            relation: 'activities',
            id: activity.id,
            item: { comments_count: $event },
          })
        "
        list-wrapper-class="ml-8"
        class="mt-1"
      />
    </div>

    <template #footer>
      <add-comment
        class="self-end"
        @created="updateCommentsVisibility(true)"
        :via-resource="resourceName"
        :commentable-id="activity.id"
        commentable-type="activities"
      />
    </template>
  </i-card>

  <edit-activity
    v-if="activityBeingEdited"
    @cancelled="activityBeingEdited = false"
    @updated="activityBeingEdited = false"
    :resource-name="resourceName"
    :activity-id="activity.id"
  />
</template>
<script>
import EditActivity from './RelatedRecordEdit'
import ActivityDateDisplay from './ActivityDateDisplay'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import CommentsCollapse from '@/views/Comments/CommentsCollapse'
import AddComment from '@/views/Comments/AddComment'
import TextCollapse from '@/components/TextCollapse'
import AssociationsPopover from '@/components/AssociationsPopover'
import MediaCard from '@/components/Media/ResourceRecordMediaCard'
import StateChange from './StateChange'
import FileDownload from 'js-file-download'
import TextBackground from '@/components/TextBackground'
import {
  determineReminderTypeBasedOnMinutes,
  determineReminderValueBasedOnMinutes,
} from '@/utils'
import { mapState } from 'vuex'

export default {
  inheritAttrs: false,
  mixins: [InteractsWithResource],
  components: {
    EditActivity,
    ActivityDateDisplay,
    CommentsCollapse,
    AddComment,
    TextCollapse,
    AssociationsPopover,
    MediaCard,
    StateChange,
    TextBackground,
  },
  props: {
    activity: { required: true, type: Object },
  },
  data: () => ({
    activityBeingEdited: false,
    syncingAssociations: false,
    activityBeingUpdated: false,
    attachmentsAreVisible: false,
    collapsed: true,
  }),
  computed: {
    reminderText() {
      return this.$t('app.reminder_set_for', {
        value: determineReminderValueBasedOnMinutes(
          this.activity.reminder_minutes_before
        ),
        type: determineReminderTypeBasedOnMinutes(
          this.activity.reminder_minutes_before
        ),
      })
    },
    ...mapState({
      users: state => state.users.collection,
      types: state => state.activities.types,
    }),

    /**
     * Get the activity total attachments
     *
     * @return {int}
     */
    totalAttachments() {
      return this.activity.media.length
    },
  },
  methods: {
    /**
     * Download ICS file for the activity
     *
     * @return {Void}
     */
    downloadICS() {
      Innoclapps.request()
        .get(`/activities/${this.activity.id}/ics`, {
          responseType: 'blob',
        })
        .then(response => {
          FileDownload(
            response.data,
            response.headers['content-disposition'].split('filename=')[1]
          )
        })
    },

    /**
     * Update the current activity
     *
     * @param  {Object} payload
     *
     * @return {Void}
     */
    updateActivity(payload = {}) {
      this.activityBeingUpdated = true
      Innoclapps.request()
        .put(`/activities/${this.activity.id}`, {
          via_resource: this.resourceName,
          via_resource_id: this.resourceRecord.id,
          ...payload,
        })
        .then(({ data }) =>
          this.updateResourceRecordHasManyRelationship(data, 'activities')
        )
        .finally(() => (this.activityBeingUpdated = false))
    },

    /**
     * Update the collapsed value indicator
     *
     * @param  {Boolean} value
     *
     * @return {VOid}
     */
    updateCommentsVisibility(value) {
      this.$store.commit('comments/SET_VISIBILITY', {
        commentableId: this.activity.id,
        commentableType: 'activities',
        visible: value,
      })
    },

    /**
     * Delete activity from storage
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    destroy(id) {
      this.$store.dispatch('activities/destroy', id).then(() => {
        this.removeResourceRecordHasManyRelationship(id, 'activities')
        this.decrementResourceRecordCount(
          'incomplete_activities_for_user_count'
        )
        Innoclapps.success(this.$t('activity.deleted'))
      })
    },

    /**
     * Activity state changed
     *
     * @param {Object} activity
     *
     * @return {Void}
     */
    handleActivityStateChanged(activity) {
      this.updateResourceRecordHasManyRelationship(activity, 'activities')

      if (activity.is_completed) {
        this.decrementResourceRecordCount(
          'incomplete_activities_for_user_count'
        )
      } else {
        this.incrementResourceRecordCount(
          'incomplete_activities_for_user_count'
        )
      }
    },

    /**
     * Toggle edit
     *
     * @param  {Object} e
     *
     * @return {Void}
     */
    toggleEdit(e) {
      // The double click to edit should not work while in edit mode
      if (e.type == 'dblclick' && this.activityBeingEdited) return
      // For double click event
      if (!this.activity.authorizations.update) return

      this.activityBeingEdited = !this.activityBeingEdited
    },

    /**
     * Handle activity media uploaded
     *
     * @param  {Object} media
     *
     * @return {Void}
     */
    handleActivityMediaUploaded(media) {
      this.addResourceRecordSubRelation(
        'activities',
        this.activity.id,
        'media',
        media
      )
    },

    /**
     * Handle activity media deleted
     *
     * @param  {Object} media
     *
     * @return {Void}
     */
    handleActivityMediaDeleted(media) {
      this.removeResourceRecordSubRelation(
        'activities',
        this.activity.id,
        'media',
        media.id
      )
    },

    /**
     * Sync the activity associations
     *
     * @param  {Object} data
     *
     * @return {Void}
     */
    syncAssociations(data) {
      this.syncingAssociations = true

      Innoclapps.request()
        .post('associations/activities/' + this.activity.id, data)
        .then(({ data }) =>
          this.updateResourceRecordHasManyRelationship(data, 'activities')
        )
        .finally(() => (this.syncingAssociations = false))
    },
  },
}
</script>
