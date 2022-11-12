<template>
  <i-layout :overlay="loadingAccounts">
    <template #actions>
      <navbar-separator class="hidden lg:block" />
      <div class="inline-flex items-center">
        <i-minimal-dropdown
          type="horizontal"
          :placement="
            !(account.is_initial_sync_performed && !isSyncDisabled)
              ? 'bottom-end'
              : 'bottom'
          "
        >
          <i-dropdown-item
            v-if="account.authorizations.update"
            :to="{
              name: `edit-email-account`,
              params: { id: account.id },
            }"
          >
            {{ $t('mail.account.edit') }}
          </i-dropdown-item>
          <i-dropdown-item :to="{ name: 'email-accounts-index' }">
            {{ $t('mail.account.manage') }}
          </i-dropdown-item>
        </i-minimal-dropdown>
        <i-button
          v-i-tooltip.left="$t('inbox.synchronize')"
          variant="secondary"
          size="sm"
          class="ml-3 lg:ml-6"
          v-if="account.is_initial_sync_performed && !isSyncDisabled"
          :disabled="syncInProgress"
          :loading="syncInProgress"
          @click="sync"
          icon="Refresh"
        />
      </div>
    </template>
    <div class="mx-auto max-w-7xl">
      <div
        class="grid grid-cols-12 gap-4"
        v-if="!loadingAccounts && hasAccounts"
      >
        <div class="col-span-12 lg:col-span-3">
          <div class="sm:sticky sm:top-2">
            <dropdown-select
              :items="accounts"
              :model-value="account"
              @change="accountSelected"
              class="w-full"
              auto-size="min"
              label-key="email"
            >
              <template v-slot="{ label }">
                <i-button
                  variant="white"
                  class="justify-between"
                  :loading="syncInProgress"
                  block
                >
                  <span class="truncate font-medium">{{ label }}</span>
                  <icon
                    icon="ChevronDown"
                    class="-mr-1 ml-2 h-5 w-5 shrink-0"
                  /> </i-button
              ></template>
            </dropdown-select>

            <i-button
              variant="primary"
              class="my-3"
              block
              :disabled="account.is_sync_stopped"
              @click="compose(true)"
              >{{ $t('mail.compose') }}</i-button
            >
            <folders-menu :folders="account && account.active_folders_tree" />
          </div>
        </div>
        <div class="col-span-12 lg:col-span-9">
          <i-alert
            class="mb-4 border border-warning-200"
            variant="warning"
            v-if="isSyncDisabled"
          >
            {{ account.sync_state_comment }}
          </i-alert>

          <i-alert
            class="mb-4 border border-warning-200"
            variant="warning"
            v-if="!account.sent_folder_id"
          >
            <p>Action required, select the sent folder for this account.</p>

            <router-link
              :to="{ name: 'edit-email-account', params: { id: account.id } }"
              class="font-medium text-warning-700 hover:text-warning-600"
              >Edit Account <span aria-hidden="true">&rarr;</span>
            </router-link>
          </i-alert>

          <i-alert
            class="mb-4 border border-warning-200"
            variant="warning"
            v-if="!account.trash_folder_id"
          >
            <p>Action required, select the trash folder for this account.</p>
            <router-link
              :to="{ name: 'edit-email-account', params: { id: account.id } }"
              class="font-medium text-warning-700 hover:text-warning-600"
              >Edit Account <span aria-hidden="true">&rarr;</span>
            </router-link>
          </i-alert>

          <i-alert
            class="mb-4 border border-info-200"
            v-if="!account.is_initial_sync_performed"
          >
            {{ $t('mail.initial_sync_info') }}
          </i-alert>

          <router-view name="message" :account="account" />

          <router-view
            name="messages"
            :account="account"
            v-if="hasFolders"
            ref="messages"
          />
          <i-card v-else class="h-60">
            <div class="m-auto mt-8 block max-w-2xl text-center">
              <icon icon="Folder" class="mx-auto h-12 w-12 text-neutral-400" />
              <p
                class="mt-1 text-sm text-neutral-500"
                v-t="'mail.account.no_active_folders'"
              ></p>
              <div class="mt-6 space-x-2">
                <i-button
                  variant="primary"
                  :to="{
                    name: 'edit-email-account',
                    params: { id: account.id },
                  }"
                  v-if="account.authorizations.update"
                  >{{ $t('mail.account.activate_folders') }}</i-button
                >
                <i-button
                  :to="{ name: 'email-accounts-index' }"
                  variant="secondary"
                  >{{ $t('mail.account.manage') }}</i-button
                >
              </div>
            </div>
          </i-card>
        </div>
      </div>
    </div>
    <compose
      :visible="isComposing"
      :default-account="account"
      @modal-hidden="compose(false)"
    />
  </i-layout>
</template>
<script>
import FoldersMenu from './InboxFoldersMenu'
import Compose from '@/views/Emails/Compose'
import { mapGetters, mapState, mapMutations, mapActions } from 'vuex'
export default {
  data: () => ({
    loadingAccounts: true,
    isComposing: false,
  }),
  components: {
    FoldersMenu,
    Compose,
  },

  /**
   * When navigating e.q. from message and directly clicking
   * on the MENU item INBOX, we need to trigger the initAccounts methods
   * as the accounts are not loaded nor redirecting to the messages route
   */
  beforeRouteUpdate(to, from, next) {
    if (to.name === 'inbox') {
      this.toAccountMessages(this.account)
    } else {
      next()
    }
  },
  computed: {
    ...mapGetters({
      accounts: 'emailAccounts/accounts',
      account: 'emailAccounts/activeInboxAccount',
    }),
    ...mapState({
      syncInProgress: state => state.emailAccounts.syncInProgress,
    }),

    /**
     * Check whether the account has syncable folders
     * @return {Boolean}
     */
    hasFolders() {
      return this.account.active_folders.length > 0
    },

    /**
     * Check whether synchronization is disabled for the active account
     * Whether is by user or by the system
     *
     * @return {Boolean}
     */
    isSyncDisabled() {
      return this.account.is_sync_stopped || this.account.is_sync_disabled
    },

    /**
     * Indicates whether ther are accounts for inbox
     *
     * @return {Boolean}
     */
    hasAccounts() {
      return this.accounts.length > 0
    },
  },
  methods: {
    ...mapMutations({
      setActiveAccount: 'emailAccounts/SET_INBOX_ACCOUNT',
      updateAccountInStore: 'emailAccounts/UPDATE',
    }),

    ...mapActions('emailAccounts', {
      fetchAccounts: 'fetch',
      syncAccount: 'syncAccount',
    }),

    /**
     * Invoke composing new email
     *
     * @param  {Boolean} boolean
     *
     * @return {Void}
     */
    compose(boolean = true) {
      this.isComposing = boolean
    },

    /**
     * Perform action when account is selected
     * @param  {Object} account
     * @return {Void}
     */
    accountSelected(account) {
      this.setActiveAccount(account)
      this.toAccountMessages(account)
    },

    /**
     * Handle action executed event
     *
     * @param  {Object} action
     *
     * @return {Void}
     */
    handleActionExecutedEvent(action) {
      // Makes sure to update the account after an action is executed
      // This will be update data like the folders unread count
      if (action.response.hasOwnProperty('account')) {
        this.$store.commit('emailAccounts/UPDATE', {
          id: action.response.account.id,
          item: action.response.account,
        })
      }

      // Update global unread messages count
      if (action.response.hasOwnProperty('unread_count')) {
        this.$store.dispatch(
          'emailAccounts/updateUnreadCountUI',
          action.response.unread_count
        )
      }
    },

    /**
     * Redirect to account messages route
     * @param  {Object} account
     * @return {Void}
     */
    toAccountMessages(account) {
      let folderId = account.active_folders[0]
        ? account.active_folders[0].id
        : null

      // When account does not have active folders
      if (!folderId) {
        return
      }

      this.$router.replace({
        name: 'inbox-messages',
        params: {
          account_id: account.id,
          // Sets the first syncable folder as default
          folder_id: folderId,
        },
        query: this.$route.query,
      })
    },

    /**
     * Perform action when accounts synchronization finishes
     *
     * @return {Void}
     */
    handleSyncFinishedEvent() {
      this.initAccounts(true)
    },

    /**
     * Init accounts
     *
     * @param  {Boolean} force Whether to skip the cached accounts
     *
     * @return {Promise}
     */
    async initAccounts(force) {
      await this.fetchAccounts({
        force: force,
      })

      if (!this.hasAccounts) {
        this.$router.replace({
          name: 'email-accounts-index',
        })

        return
      }

      // Check if the account is configured when handleSyncFinishedEvent
      // method calls this function
      if (this.$route.params.account_id && this.$route.params.folder_id) {
        this.setActiveAccount(Number(this.$route.params.account_id))
      } else if (Object.keys(this.account).length === 0) {
        this.setActiveAccount(this.accounts[0])
      }

      // When accessing the INBOX route without any params
      // Redirect to the messages
      if (this.$route.name === 'inbox' && this.hasFolders) {
        this.toAccountMessages(this.account)
      }
    },

    /**
     * Manually sync the currenct active inbox account
     *
     * @return {Void}
     */
    sync() {
      this.syncAccount(this.account.id).then(data => {
        // Update the account in store in case of folder changes
        this.updateAccountInStore({
          id: data.id,
          item: data,
        })

        Innoclapps.$emit('user-synchronized-email-account', data)
      })
    },
  },

  /**
   * Component created
   *
   * @return {Void}
   */
  created() {
    this.loadingAccounts = true
    this.initAccounts()
      .then(() => this.$route.query.compose && this.compose())
      .finally(() => (this.loadingAccounts = false))

    Innoclapps.$on('action-executed', this.handleActionExecutedEvent)
    Innoclapps.$on('email-accounts-sync-finished', this.handleSyncFinishedEvent)
  },

  /**
   * Handle the component unmounted lifecycle hook
   *
   * @return {Void}
   */
  unmounted() {
    Innoclapps.$off('action-executed', this.handleActionExecutedEvent)
    Innoclapps.$off(
      'email-accounts-sync-finished',
      this.handleSyncFinishedEvent
    )
  },
}
</script>
