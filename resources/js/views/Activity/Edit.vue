<template>
  <i-slideover
    :visible="true"
    @hidden="modalHidden"
    form
    @submit="update"
    :ok-disabled="recordForm.busy || $gate.denies('update', record)"
    :ok-title="$t('app.save')"
    :hide-footer="activeTab === 'comments'"
    static-backdrop
    id="activityEdit"
    :title="recordForm.title"
    :description="modalDescription"
  >
    <div class="absolute right-5 -top-2 sm:top-4">
      <actions
        v-if="fieldsConfigured"
        type="dropdown"
        :ids="actionId"
        :actions="actions"
        :resource-name="resourceName"
        @run="actionExecuted"
      />
    </div>

    <form-fields-placeholder v-if="!fieldsConfigured" />

    <div v-else class="mt-6 sm:mt-0">
      <i-tabs v-model="activeTab">
        <i-tab :title="$t('activity.activity')" tab-id="activity">
          <p
            class="small mb-3 text-neutral-800 dark:text-neutral-300"
            v-if="componentReady"
          />
          <fields-generator :fields="fields" view="update" :form="recordForm" />
          <media-card
            :record="record"
            :resource-name="resourceName"
            @deleted="removeResourceRecordMedia"
            @uploaded="addResourceRecordMedia"
          />
        </i-tab>
        <i-tab
          lazy
          @activated-first-time="loadComments"
          :title="$t('comment.comments') + ' (' + record.comments_count + ')'"
          tab-id="comments"
        >
          <div class="my-3 text-right">
            <add-comment
              :commentable-id="record.id"
              commentable-type="activities"
              @created="incrementResourceRecordCount('comments_count')"
            />
          </div>

          <i-overlay :show="!commentsAreLoaded">
            <comments
              v-if="commentsAreLoaded"
              :comments="record.comments || []"
              commentable-type="activities"
              :commentable-id="record.id"
              :auto-focus-if-required="true"
              @deleted="decrementResourceRecordCount('comments_count')"
            />
          </i-overlay>
        </i-tab>
      </i-tabs>
    </div>
  </i-slideover>
</template>
<script>
import Actions from '@/components/Actions/Actions'
import MediaCard from '@/components/Media/ResourceRecordMediaCard'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import HandlesResourceUpdate from '@/mixins/HandlesResourceUpdate'
import InteractsWithActivityFields from '@/views/Activity/InteractsWithActivityFields'
import Comments from '@/views/Comments/Index'
import AddComment from '@/views/Comments/AddComment'

export default {
  mixins: [
    InteractsWithActivityFields,
    InteractsWithResource,
    HandlesResourceUpdate,
  ],
  components: {
    Actions,
    MediaCard,
    Comments,
    AddComment,
  },
  props: {
    onHidden: Function,
    onActionExecuted: Function,
    id: Number,
  },
  data: () => ({
    commentsAreLoaded: false,
    activeTab: 'activity',
  }),
  computed: {
    /**
     * Get the modal description
     *
     * @return {String|null}
     */
    modalDescription() {
      if (!this.componentReady) {
        return null
      }

      return `${this.$t('app.created_at')}: ${this.localizedDateTime(
        this.record.created_at
      )} - ${this.record.creator.name}`
    },

    /**
     * Action id
     *
     * @return {Number|Array}
     */
    actionId() {
      return this.record.id || []
    },

    /**
     * Get the record actions
     *
     * @return {Array}
     */
    actions() {
      return this.record.actions || []
    },

    /**
     * Get the edit activity id
     */
    computedId() {
      return this.id || this.$route.params.id
    },
  },
  methods: {
    /**
     * Load the activity comments
     */
    loadComments() {
      this.$store
        .dispatch('comments/getAll', {
          resourceName: 'activities',
          resourceId: this.computedId,
        })
        .then(comments => {
          this.$store.commit('activities/SET_RECORD', {
            comments,
          })
          this.commentsAreLoaded = true
        })
    },

    /**
     * Handle the action executed event
     *
     * @param  {Object} action
     *
     * @return {Void}
     */
    actionExecuted(action) {
      if (this.onActionExecuted) {
        this.onActionExecuted(action)
        return
      }

      // Reload the record data on any action executed except delete
      // If we try to reload on delete will throw 404 error
      if (!action.destroyable) {
        this.initRecord()
      } else {
        this.$router.push({ name: 'activity-index' })
      }
    },

    /**
     * Handle the activity hidden modal
     *
     * @return {Void}
     */
    modalHidden() {
      this.onHidden ? this.onHidden() : this.goBack()
    },
  },
  beforeMount() {
    this.bootRecordUpdate({
      resource: this.resourceName,
      id: this.computedId,
      beforeSetRecord: record => {
        this.setPageTitle(record.display_name)

        if (this.$route.query.comment_id) {
          this.activeTab = 'comments'
        }

        do {
          if (!this.componentReady) {
            return
          }

          this.fields.update('guests', {
            activity: record,
          })
        } while (!this.componentReady)
      },
    })
  },
}
</script>
