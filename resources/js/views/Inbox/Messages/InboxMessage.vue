<template>
  <div
    class="sticky top-0 z-50"
    :class="{
      'bg-opacity-75 pt-2 backdrop-blur-lg backdrop-filter':
        !messageInfoIsFullyVisible,
    }"
  >
    <div
      :class="[
        !messageInfoIsFullyVisible ? 'rounded-lg' : 'rounded-t-lg',
        'overflow-hidden bg-white shadow dark:bg-neutral-900',
      ]"
    >
      <div
        :class="{ blur: loading }"
        class="flex flex-col items-center border-b border-neutral-200 px-4 py-5 dark:border-neutral-700 sm:flex-row sm:p-5"
      >
        <div class="mr-3 grow">
          <h5
            class="text-lg font-semibold leading-6 text-neutral-800 dark:text-white"
            v-text="subject"
          />
          <associations-popover
            :disabled="syncingAssociations"
            @change="syncAssociations"
            class="inline-flex"
            placement="bottom-start"
            :associated="message"
          />
        </div>

        <div class="shrink-0">
          <div
            class="flex flex-col items-center space-y-2 sm:flex-row sm:flex-wrap sm:space-y-0 sm:space-x-2"
            v-if="componentReady"
          >
            <div v-show="!account.is_sync_stopped">
              <actions
                type="dropdown"
                :ids="message.id"
                :actions="message.actions"
                :action-request-query-string="actionRequestQueryString"
                resource-name="emails"
                @run="actionExecuted"
              />
            </div>

            <div class="flex items-center space-x-3">
              <i-button
                variant="white"
                size="sm"
                :disabled="account.is_sync_stopped"
                @click="reply(true)"
                icon="Reply"
                v-if="canReply"
              >
                {{ $t('inbox.reply') }}</i-button
              >
              <i-button
                variant="white"
                size="sm"
                :disabled="account.is_sync_stopped"
                @click="replyAll()"
                icon="Reply"
                v-if="canReply && hasMoreReplyTo"
              >
                <!-- TODO, find reply-all icon -->
                {{ $t('inbox.reply_all') }}
              </i-button>
              <i-button
                variant="white"
                size="sm"
                icon="Share"
                :disabled="account.is_sync_stopped"
                @click="forward(true)"
                v-if="canReply"
              >
                {{ $t('inbox.forward') }}</i-button
              >
              <i-minimal-dropdown type="horizontal">
                <i-dropdown-item @click="createComponent = 'create-contact'">
                  {{ $t('contact.convert') }}
                </i-dropdown-item>
                <i-dropdown-item @click="createComponent = 'create-deal'">
                  {{ $t('deal.create') }}
                </i-dropdown-item>
                <i-dropdown-item @click="createComponent = 'create-activity'">
                  {{ $t('activity.create') }}
                </i-dropdown-item>
              </i-minimal-dropdown>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <i-card
    class="mb-3 rounded-b-md"
    :rounded="false"
    id="messageInfo"
    :overlay="loading"
  >
    <div class="flex" v-if="componentReady">
      <div class="mr-2">
        <i-avatar v-once :src="message.avatar_url" />
      </div>
      <div>
        <message-recipients
          v-once
          :label="$t('inbox.from')"
          :recipients="message.from"
        />
        <message-recipients
          v-once
          :label="$t('inbox.reply_to')"
          :recipients="message.reply_to"
          :show-when-empty="false"
        />
        <message-recipients :label="$t('inbox.to')" :recipients="message.to" />
        <message-recipients
          v-once
          :label="$t('inbox.cc')"
          :recipients="message.cc"
          :show-when-empty="false"
        />
        <message-recipients
          v-once
          :label="$t('inbox.bcc')"
          :recipients="message.bcc"
          :show-when-empty="false"
        />
        <p class="mt-2 text-sm text-neutral-800 dark:text-neutral-100">
          <span class="mr-1 font-semibold">{{ $t('inbox.date') }}:</span>
          <span
            class="text-neutral-700 dark:text-neutral-300"
            v-once
            v-text="localizedDateTime(message.date)"
          ></span>
        </p>
      </div>
    </div>
  </i-card>

  <i-card class="mb-3" :overlay="loading">
    <message-preview v-if="!loading" :message="message" />
  </i-card>

  <i-card
    class="mb-3"
    no-body
    v-if="hasAttachments"
    :header="$t('mail.attachments')"
  >
    <message-attachments :email="message" />
  </i-card>

  <message-reply
    :message="message"
    :visible="isReplying || isForwarding"
    v-if="canReply"
    :to-all="replyToAll"
    :forward="isForwarding"
    @modal-hidden="replyModalHidden"
  />

  <component
    :is="createComponent"
    :message="message"
    @created="getMessage"
    @hidden="createComponent = null"
  />
</template>
<script>
import MessageRecipients from '@/views/Emails/Recipients'
import MessageReply from '@/views/Emails/Reply'
import MessageAttachments from '@/views/Emails/Attachments'
import MessagePreview from '@/views/Emails/Preview'
import Actions from '@/components/Actions/Actions'
import AssociationsPopover from '@/components/AssociationsPopover'
import CreateActivity from '@/views/Activity/CreateViaMessage'
import CreateContact from '@/views/Contacts/CreateViaMessage'
import CreateDeal from '@/views/Deals/CreateViaMessage'
export default {
  components: {
    MessageRecipients,
    MessageReply,
    MessagePreview,
    MessageAttachments,
    AssociationsPopover,
    Actions,
    CreateActivity,
    CreateContact,
    CreateDeal,
  },
  props: {
    account: {
      required: true,
      type: Object,
    },
  },
  data: () => ({
    message: {},
    createComponent: null,
    loading: false,
    isReplying: false,
    isForwarding: false,
    replyToAll: false,
    syncingAssociations: false,
    scrollObserver: null,
    messageInfoIsFullyVisible: false,
  }),
  computed: {
    hasMoreReplyTo() {
      return this.message.cc && this.message.cc.length > 0
    },

    /**
     * Provides the actios request query string
     *
     * @return {Object}
     */
    actionRequestQueryString() {
      return {
        folder_id: this.$route.params.folder_id,
        account_id: this.$route.params.account_id,
      }
    },

    /**
     * Check whether a reply can be performed to this message
     *
     * @return {Boolean}
     */
    canReply() {
      return this.componentReady && !this.message.is_draft
    },

    /**
     * Get the message subject
     *
     * @return {String}
     */
    subject() {
      if (!this.message.subject) {
        return this.$t('inbox.no_subject')
      }

      return this.message.subject
    },

    /**
     * Check whether the message has attachments
     *
     * @return {Boolean}
     */
    hasAttachments() {
      return this.componentReady && this.message.media.length > 0
    },

    /**
     * Checks whether the component is ready based if the message
     * data has keys, if don't means that it's not yet fetched
     * @return {Boolean}
     */
    componentReady() {
      return Object.keys(this.message).length > 0
    },

    /**
     * Get the message route
     *
     * @return {String}
     */
    messageRoute() {
      return `/inbox/emails/folders/${this.$route.params.folder_id}/${this.$route.params.id}`
    },

    /**
     * Get the total unread messages for all accounts
     *
     * @return {Void}
     */
    totalUnreadMessages() {
      return this.$store.getters.getMenuItem('inbox').badge
    },
  },
  methods: {
    /**
     * Handle the reply modal hidden event
     *
     * @return {Void}
     */
    replyModalHidden() {
      this.reply(false)
      this.forward(false)
    },

    /**
     * Sync the email associations
     *
     * @param  {Object} data
     *
     * @return {Void}
     */
    syncAssociations(data) {
      this.syncingAssociations = true
      Innoclapps.request()
        .post('associations/emails/' + this.message.id, data)
        .finally(() => (this.syncingAssociations = false))
    },

    /**
     * Handle action executed event
     *
     * @param  {Object} action
     *
     * @return {Void}
     */
    actionExecuted(action) {
      // After a move action is executed we need to change the route
      // to the actual new folder, to prevent showing error message e.q. MessageNotFound
      // when executing move action again as the old folder will be passed to the params request
      if (action.uriKey === 'email-account-message-move') {
        this.replaceMessageRouteFolder(action.response.moved_to_folder_id)
      } else if (action.uriKey === 'email-account-message-delete') {
        // Message parmanently deleted, navigate to inbox
        if (this.$route.params.folder_id === this.account.trash_folder.id) {
          this.$router.replace({
            name: 'inbox',
          })
        } else {
          this.replaceMessageRouteFolder(this.account.trash_folder.id)
        }
      }
    },

    /**
     * Replace the current route folder id
     *
     * @param  {Number} folderId
     *
     * @return {Void}
     */
    replaceMessageRouteFolder(folderId) {
      this.$router.replace({
        name: 'inbox-message',
        params: {
          account_id: this.account.id,
          folder_id: folderId,
          id: this.message.id,
        },
      })
    },

    /**
     * Change reply data state
     *
     * @param  {Boolean} state
     *
     * @return {Void}
     */
    reply(state = true) {
      this.isReplying = state
      this.replyToAll = false
    },

    /**
     * Change forward data state
     *
     * @param  {Boolean} state
     *
     * @return {Void}
     */
    forward(state = true) {
      this.isForwarding = state
    },

    /**
     * Initialize reply all to mesage
     *
     * @return {Void}
     */
    replyAll() {
      this.isReplying = true
      this.replyToAll = true
    },

    /**
     * Get the message from storage
     * @return {Void}
     */
    async getMessage() {
      this.loading = true
      let { data } = await Innoclapps.request().get(this.messageRoute, {
        // Pass params for the actions
        params: {
          account_id: this.account.id,
          folder_id: this.$route.params.folder_id,
        },
      })

      this.message = data

      // Update the active folder so unread/read keys
      // can be updated too for the folders menu
      this.$store.commit('emailAccounts/UPDATE', {
        id: this.account.id,
        item: {
          ...this.account,
          active_folders_tree: data.account_active_folders_tree,
        },
      })

      if (data.is_read === false) {
        this.$store.dispatch(
          'emailAccounts/updateUnreadCountUI',
          this.totalUnreadMessages === 0 ? 0 : this.totalUnreadMessages - 1
        )
      }

      this.setPageTitle(this.subject)
      this.loading = false
    },
  },
  created() {
    this.getMessage()
  },
  mounted() {
    this.scrollObserver = new IntersectionObserver(
      entries => {
        this.messageInfoIsFullyVisible = entries[0].isIntersecting
      },
      {
        root: document.getElementById('main'),
        threshold: 1,
      }
    )
    this.scrollObserver.observe(document.getElementById('messageInfo'))
  },
  beforeUnmount() {
    if (this.scrollObserver) {
      this.scrollObserver.unobserve(document.getElementById('messageInfo'))
      this.scrollObserver = null
    }
  },
}
</script>
