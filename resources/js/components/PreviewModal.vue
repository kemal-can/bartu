<template>
  <i-slideover
    @shown="handleModalShownEvent"
    @hidden="handleModalHiddenEvent"
    :id="resourceName + '-preview'"
    v-model:visible="isModalVisible"
    :title="record.display_name"
    :description="titleDescription"
    static-backdrop
    form
    @submit="update"
  >
    <form-fields-placeholder v-if="!componentReady" />

    <component
      :is="resourceSingular + '-preview'"
      :record="record"
      :form="recordForm"
      :preview-ready="componentReady"
      :update-fields-function="updateFields"
      resource-name="recordPreview"
      :fields="fields"
    />

    <template #modal-footer>
      <div class="flex justify-end space-x-2">
        <i-button
          variant="white"
          @click="view"
          :disabled="!componentReady"
          v-show="!isPreviewingSameResourceAsViaResource"
        >
          {{ $t('app.view_record') }}
        </i-button>
        <i-button
          variant="primary"
          type="submit"
          :loading="recordForm.busy"
          :disabled="
            !componentReady || recordForm.busy || !record.authorizations.update
          "
          >{{ $t('app.save') }}</i-button
        >
      </div>
    </template>
  </i-slideover>
</template>
<script>
import HandlesResourceUpdate from '@/mixins/HandlesResourceUpdate'
import { mapState, mapMutations } from 'vuex'
import { windowState } from '@/utils'
import Form from '@/components/Form/Form'

export default {
  emits: ['resource-record-updated', 'action-executed', 'shown', 'hidden'],
  mixins: [HandlesResourceUpdate],
  provide() {
    return {
      setDescription: this.setDescription,
    }
  },
  props: {
    viaResource: String,
  },
  data: () => ({
    isModalVisible: false,
    viaHistory: false,
    titleDescription: null,
    unregisterRouterGuard: null,
  }),
  computed: {
    ...mapState({
      record: state => state.recordPreview.record,
      resourceId: state => state.recordPreview.resourceId,
      resourceName: state => state.recordPreview.resourceName,
    }),

    /**
     * Check whether the user is previwing the same record
     * when viewing single resource
     *
     * @return {Boolean}
     */
    isPreviewingSameResourceAsViaResource() {
      if (!this.viaResource) {
        return false
      }

      return (
        this.resourceName == this.viaResource &&
        this.$store.state[this.viaResource].record.id === this.resourceId
      )
    },

    /**
     * The preview key for catching changes
     *
     * @return {String}
     */
    previewKey() {
      return String(String(this.resourceId) + String(this.resourceName))
    },

    /**
     * Indicates wheter there is preview resource to shown
     *
     * @return {Boolean}
     */
    hasRecordForPreview() {
      return Boolean(this.resourceName) && Boolean(this.resourceId)
    },
  },
  methods: {
    ...mapMutations({
      resetRecord: 'recordPreview/RESET_RECORD',
      resetRecordPreview: 'recordPreview/RESET_PREVIEW',
    }),

    /**
     * Handle the preview action executed event
     *
     * @param {Object} action
     */
    previewActionExecutedEventHandler(action) {
      this.$emit('action-executed', action)
      this.actionExecuted(action)
    },

    /**
     * Set the preview modal description
     *
     * @param {String} description
     */
    setDescription(description) {
      this.titleDescription = description
    },

    /**
     * Set the record in store
     *
     * @param {Object} record
     */
    setRecordInStore(record) {
      this.$store.commit('recordPreview/SET_VIA_RESOURCE', this.viaResource)
      this.$store.commit('recordPreview/SET_RECORD', record)
    },

    /**
     * Get the hash preview
     *
     * @return {null|Array}
     */
    locationHashPreview() {
      // Make sure it's valid preview e.q. contacts-12
      let regex = /([a-z]*)-([0-9]*)$/i

      return window.location.hash.match(regex)
    },

    /**
     * Handle the modal pop state to support
     * back and forward actions for the previewed record
     *
     * @return {Void}
     */
    handlePopState() {
      if (!window.location.hash) {
        this.hide()
        return
      }

      let matches = this.locationHashPreview()

      if (matches) {
        const [hash, resourceName, resourceId] = matches
        this.viaHistory = true

        this.$store.commit('recordPreview/SET_PREVIEW_RESOURCE', {
          resourceName: resourceName,
          resourceId: Number(resourceId),
        })

        this.$nextTick(() => {
          this.boot()
          this.viaHistory = false
        })
      }
    },

    /**
     * Handle the on window load event
     * The preview modal won't work when refreshing the page
     * In this case, if the page is refreshed, do not load the preview resource
     * which was viewed before refresh the page
     *
     * In order to achieve this we need to clear the hash
     *
     * @return {Void}
     */
    handleWindowLoad() {
      windowState.clearHash(this.$route.fullPath.split('#')[0])
    },

    /**
     * Dispatch the update action
     *
     * @returns {Promise}
     */
    dispatchUpdateAction() {
      return this.$store
        .dispatch(this.updateAction, {
          form: this.fillFormFields(this.recordForm),
          id: this.updateConfig.id,
        })
        .catch(e => {
          if (e.response.status === 422) {
            Innoclapps.error(this.$t('app.form_validation_failed'), 3000)
          }
          return Promise.reject(e)
        })
    },

    /**
     * Modal shown event
     *
     * @return {Void}
     */
    handleModalShownEvent() {
      this.$emit('shown')
    },

    /**
     * Modal hidden event
     *
     * @return {Void}
     */
    handleModalHiddenEvent() {
      this.resetPreview()
      this.$emit('hidden')
    },

    /**
     * Hide the modal helper function
     *
     * @return {Void}
     */
    hide() {
      this.$iModal.hide(this.resourceName + '-preview')
    },

    /**
     * Boot the preview
     *
     * @return {Void}
     */
    boot() {
      if (!this.hasRecordForPreview) {
        return
      }

      this.removeRecordUpdatedEvent()

      // Don't push to state if it's via history
      // as it will produce more states, keep only the previews
      // the users clicked on
      if (!this.viaHistory) {
        windowState.push('#' + this.resourceName + '-' + this.resourceId)
      }

      this.isModalVisible = true
      this.addRecordUpdatedEvent()
      // The fields and the form must be resetted each time a new
      // preview record is initialized
      this.recordForm = new Form()
      this.fields = []

      this.bootRecordUpdate({
        resource: this.resourceName,
        id: this.resourceId,
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
      // Reload the record data on any action executed except delete
      // If we try to reload on delete will throw 404 error
      if (!action.destroyable) {
        this.initRecord(record => this.handleRecordUpdated(record))
      } else if (!this.viaResource) {
        // When no viaResource is passed, just hide the modal
        // and leave the parent company to handle any updates
        this.hide()
      } else {
        // Is previewing the same resource passed viaResource prop,
        // In this case, redirect to the resource index named route
        if (this.isPreviewingSameResourceAsViaResource) {
          this.$router.push({
            name: this.resourceSingular + '-index',
          })
        } else {
          // Deleted a record which was previewed
          this.hide()
          // In case viaResource is paseed, remove the resource
          // relation too, this should be always true (if(this.viaResource))
          if (this.viaResource) {
            this.$store.commit(
              this.viaResource + '/REMOVE_RECORD_HAS_MANY_RELATIONSHIP',
              {
                relation: this.resourceName,
                id: this.resourceId,
              }
            )
          }
        }
      }
    },

    /**
     * Helper method to navigate to the actual record full view/update
     * The method uses the current already fetched record from database and passes as meta
     * This helps not making the same request again to the server
     *
     * @return {Void}
     */
    view() {
      this.$router[this.resourceSingular] = this.record

      this.$router.push({
        name: 'view-' + this.resourceSingular,
        params: {
          id: this.record.id,
        },
      })

      // Hide the modal in case tryig to view the same
      // resource type via resource e.q. child company so it can show
      // the new company directly
      this.hide()
    },

    /**
     * Handle record updated event
     *
     * @param  {Object} record
     *
     * @return {Void}
     */
    handleRecordUpdated(record) {
      if (!this.viaResource) {
        return
      }

      // Update the actual resource main record
      if (this.isPreviewingSameResourceAsViaResource) {
        this.$emit('resource-record-updated', record)
        this.$store.commit(this.viaResource + '/SET_RECORD', record)
      } else {
        this.$store.commit(
          this.viaResource + '/UPDATE_RECORD_HAS_MANY_RELATIONSHIP',
          {
            relation: this.resourceName,
            id: record.id,
            item: record,
          }
        )
      }
    },

    /**
     * Reset the preview store so i can catch changes on next clicks
     *
     * @return {Void}
     */
    resetPreview() {
      this.removeRecordUpdatedEvent()
      windowState.clearHash()
      this.resetRecord()
      this.resetRecordPreview()
      this.titleDescription = null
    },
    addRecordUpdatedEvent() {
      Innoclapps.$on(
        `${this.resourceName}-record-updated`,
        this.handleRecordUpdated
      )
    },
    removeRecordUpdatedEvent() {
      Innoclapps.$off(
        `${this.resourceName}-record-updated`,
        this.handleRecordUpdated
      )
    },
  },
  created() {
    window.addEventListener('popstate', this.handlePopState)
    window.addEventListener('load', this.handleWindowLoad)

    this.unregisterRouterGuard = this.$router.beforeEach((to, from, next) => {
      // Check to.hash, if there is no hash, it's not modal history matching
      if (this.isModalVisible && !to.hash) {
        // Make sure that there is no state/hash on back when user is navigated
        // outside of the modal routes
        // This ensures after navigation, when clicking back, it goes
        // to the original preview page without the hashes
        windowState.clearHash(from.fullPath.split('#')[0])
      }
      next()
    })
  },
  mounted() {
    this.$watch(
      'previewKey',
      function (newVal, oldVal) {
        if (!this.viaHistory) {
          this.boot()
        }
      },
      {
        immediate: true,
      }
    )
  },
  beforeUnmount() {
    window.removeEventListener('popstate', this.handlePopState)
    window.removeEventListener('load', this.handleWindowLoad)
    this.unregisterRouterGuard()
    this.resetPreview()
  },
}
</script>
