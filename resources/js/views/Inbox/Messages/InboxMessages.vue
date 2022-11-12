<template>
  <component
    :is="messagesComponent"
    :table-id="messagesComponent"
    :account="account"
    ref="table"
    :data-request-query-string="tableDataRequestQueryString"
    :action-request-query-string="actionRequestQueryString"
  />
</template>
<script>
import MessagesOutgoing from './MessagesOutgoing'
import MessagesIncoming from './MessagesIncoming'
import find from 'lodash/find'

export default {
  components: {
    MessagesOutgoing,
    MessagesIncoming,
  },
  props: {
    account: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    folderId: null,
    accountId: null,
    folder: {},
  }),
  watch: {
    '$route.params': {
      immediate: true,
      handler: function (newVal, oldVal) {
        const samePageNavigation =
          newVal.account_id && newVal.folder_id && !newVal.id // we will check if there is a message id, if yes, then it's another page
        this.accountId = newVal.account_id
        this.folderId = newVal.folder_id

        if (!oldVal || (oldVal && oldVal.folder_id != newVal.folder_id)) {
          this.folder = find(
            this.account.folders,
            folder => Number(folder.id) === Number(newVal.folder_id)
          )
        }

        if (samePageNavigation) {
          this.$nextTick(() =>
            this.setPageTitle(
              `${this.folder.display_name} - ${this.account.email}`
            )
          )
        }

        // We need to refetch the table settings
        // when an account has been changed because of the MOVE TO
        // action is using the request params to compose the field options
        if (
          samePageNavigation &&
          oldVal &&
          Number(newVal.account_id) !== Number(oldVal.account_id)
        ) {
          this.$nextTick(() => this.$refs.table.$refs.table.refetchActions())
        }
      },
    },
  },
  computed: {
    /**
     * The current messages table id
     *
     * @return {String}
     */
    tableId() {
      return this.messagesComponent
    },

    /**
     * Provides the actions request query string
     *
     * @return {Object}
     */
    actionRequestQueryString() {
      return {
        folder_id: this.folderId,
        account_id: this.accountId,
      }
    },

    /**
     * Determine the messages component based on the folder typ
     *
     * @return {String}
     */
    messagesComponent() {
      return this.folderType === 'incoming' ||
        this.folderType === Innoclapps.config.mail.folders.other
        ? 'messages-incoming'
        : 'messages-outgoing'
    },

    /**
     * Server params for the table
     * @return {Object}
     */
    tableDataRequestQueryString() {
      return {
        account_id: this.accountId,
        folder_id: this.folderId,
        folder_type: this.folderType,
      }
    },

    /**
     * Determine the folder group type
     *
     * @return {String}
     */
    folderType() {
      if (this.isOutgoingFolderType) {
        return 'outgoing'
      } else if (this.isIncomingFolderType) {
        return 'incoming'
      }

      return Innoclapps.config.mail.folders.other
    },

    /**
     * Checks whether the current folder of type outgoing
     * The computed also checks whether this folder is child in outgoing folder
     *
     * @return {Boolean}
     */
    isOutgoingFolderType() {
      let currentFolderIsOutgoing =
        Innoclapps.config.mail.folders.outgoing.indexOf(this.folder.type) > -1

      if (currentFolderIsOutgoing) {
        return true
      }

      // Look more deeply to see if this is a child of the sent folder
      return this.isFolderChildIn('outgoing')
    },

    /**
     * Checks whether the current folder of type incoming
     * The computed also checks whether this folder is child in incoming folder
     *
     * @return {Boolean}
     */
    isIncomingFolderType() {
      let currentFolderIsIncoming =
        Innoclapps.config.mail.folders.incoming.indexOf(this.folder.type) > -1

      if (currentFolderIsIncoming) {
        return true
      }

      // Look more deeply to see if this is a child of the sent folder
      return this.isFolderChildIn('incoming')
    },
  },
  methods: {
    /**
     * Check hierarchically whether the current folder
     * is a deep child of the the sent folder
     *
     * @param  {Object|null}  hierarchicalFolder
     * @param  {string}  The key name, to use for the check
     * incoming or outgoing
     *
     * @return {Boolean}
     */
    isFolderChildIn(key, hierarchicalFolder) {
      let folder = hierarchicalFolder || this.folder

      if (!folder.parent_id) {
        return false
      }

      let parent = find(this.account.folders, ['id', Number(folder.parent_id)])

      if (Innoclapps.config.mail.folders[key].indexOf(parent.type) > -1) {
        return true
      } else if (parent.parent_id) {
        return this.isFolderChildIn(key, parent)
      }

      return false
    },

    /**
     * Reload table
     *
     * @return {Void}
     */
    reload() {
      this.$iTable.reload(this.tableId)
    },

    /**
     * When the user is viewing directly e.q. the sent folder
     * after the message is sent, we need to reload the folder
     *
     * @return {Void}
     */
    reloadOutgoingFolderTable() {
      if (this.isOutgoingFolderType) {
        this.reload()
      }
    },
  },
  mounted() {
    Innoclapps.$on('user-synchronized-email-account', this.reload)
    Innoclapps.$on('email-accounts-sync-finished', this.reload)
    Innoclapps.$on('email-sent', this.reloadOutgoingFolderTable)
  },
  unmounted() {
    Innoclapps.$off('user-synchronized-email-account', this.reload)
    Innoclapps.$off('email-accounts-sync-finished', this.reload)
    Innoclapps.$off('email-sent', this.reloadOutgoingFolderTable)
  },
}
</script>
<style>
.sync-stopped-by-system table .form-check {
  pointer-events: none;
  opacity: 0.5;
}
</style>
