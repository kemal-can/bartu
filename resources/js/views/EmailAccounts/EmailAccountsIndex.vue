<template>
  <i-layout>
    <template #actions>
      <navbar-separator class="hidden lg:block" v-show="hasAccounts" />
      <i-button
        variant="primary"
        size="sm"
        :to="{ name: 'inbox' }"
        v-show="hasAccounts"
        >{{ $t('inbox.inbox') }}</i-button
      >
    </template>

    <i-card
      v-show="hasAccounts"
      no-body
      :overlay="loading"
      class="mx-auto max-w-7xl"
      :header="$t('mail.account.accounts')"
      actions-class="w-full sm:w-auto"
    >
      <template #actions>
        <div
          class="mt-4 flex w-full flex-col space-y-1 sm:mt-0 sm:w-auto sm:flex-row sm:justify-end sm:space-x-2 sm:space-y-0"
        >
          <span
            class="grid sm:inline"
            v-i-tooltip="
              $gate.isRegularUser()
                ? $t('user.not_authorized')
                : $t('mail.account.create_shared_info')
            "
          >
            <i-button
              size="sm"
              variant="white"
              :disabled="$gate.isRegularUser()"
              @click="createShared"
              >{{ $t('mail.account.connect_shared') }}</i-button
            >
          </span>
          <i-button size="sm" variant="white" @click="createPersonal">
            {{ $t('mail.account.connect_personal') }}
          </i-button>
        </div>
      </template>

      <transition-group
        class="divide-y divide-neutral-200 dark:divide-neutral-700"
        name="flip-list"
        tag="ul"
      >
        <li v-for="account in accounts" :key="account.id">
          <div
            class="flex items-center justify-between border-b border-warning-100 bg-warning-50 px-4 py-2"
            v-if="account.is_sync_stopped || account.requires_auth"
          >
            <div class="text-sm font-medium text-warning-700">
              <p
                v-if="account.requires_auth"
                v-t="'app.oauth.requires_authentication'"
              />
              <p
                v-if="account.is_sync_stopped"
                v-text="account.sync_state_comment"
              />
            </div>

            <i-button-minimal
              size="sm"
              variant="warning"
              class="shrink-0 self-start"
              v-if="account.requires_auth"
              @click="reAuthenticate(account)"
              >{{ $t('app.oauth.re_authenticate') }}</i-button-minimal
            >
          </div>
          <div
            :class="[
              'flex items-center px-4 py-4 sm:px-6',
              {
                'opacity-70':
                  account.is_sync_stopped || account.is_sync_disabled,
              },
            ]"
          >
            <div
              class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between"
            >
              <div class="truncate">
                <div class="flex text-sm">
                  <component
                    :is="account.authorizations.update ? 'a' : 'p'"
                    @click="account.authorizations.update ? edit(account) : ''"
                    :class="[
                      'truncate font-medium text-primary-600 dark:text-primary-400',
                      account.authorizations.update ? 'cursor-pointer' : '',
                    ]"
                  >
                    {{ account.email }}
                  </component>
                </div>
                <div class="mt-2 flex">
                  <div class="flex items-center text-sm">
                    <icon
                      icon="Mail"
                      class="mr-1.5 h-5 w-5 shrink-0 text-neutral-400"
                    />
                    <p class="mr-3 text-neutral-600 dark:text-neutral-300">
                      {{ account.connection_type }}
                    </p>
                    <i-badge
                      :variant="account.is_personal ? 'neutral' : 'info'"
                      v-text="$t('mail.account.' + account.type)"
                    />
                  </div>
                </div>
              </div>
              <div class="mt-4 shrink-0 sm:mt-0 sm:ml-5">
                <div class="flex space-x-3">
                  <i-form-toggle
                    @change="togglePrimaryState($event, account)"
                    :disabled="account.is_sync_stopped"
                    :model-value="account.is_primary"
                    :label="$t('mail.account.is_primary')"
                  />
                  <i-form-toggle
                    @change="toggleDisabledSyncState(account)"
                    v-show="
                      !account.is_sync_stopped && account.authorizations.update
                    "
                    :model-value="account.is_sync_disabled"
                    :label="$t('mail.disable_sync')"
                  />
                </div>
              </div>
            </div>
            <div
              class="ml-5 shrink-0 self-start sm:self-auto"
              v-if="
                account.authorizations.update || account.authorizations.delete
              "
            >
              <i-minimal-dropdown>
                <i-dropdown-item
                  @click="edit(account)"
                  v-if="account.authorizations.update"
                >
                  {{ $t('app.edit') }}
                </i-dropdown-item>
                <i-dropdown-item
                  v-if="account.authorizations.delete"
                  @click="destroy(account.id)"
                >
                  {{ $t('app.delete') }}
                </i-dropdown-item>
              </i-minimal-dropdown>
            </div>
          </div>
        </li>
      </transition-group>
    </i-card>

    <i-card :overlay="loading" v-show="!hasAccounts" class="m-auto max-w-5xl">
      <div v-show="!loading" class="mx-auto max-w-2xl p-4">
        <h2
          class="text-center text-2xl font-medium text-neutral-800 dark:text-neutral-200"
          v-t="'mail.account.no_accounts_configured'"
        />

        <p
          class="text-center text-neutral-600 dark:text-neutral-300"
          v-t="'mail.account.no_accounts_configured_info'"
        />

        <ul
          role="list"
          class="mt-6 grid grid-cols-1 gap-6 py-10 lg:grid-cols-2"
        >
          <li
            v-for="(item, itemIdx) in emptyStateItems"
            :key="itemIdx"
            class="flow-root"
          >
            <div
              class="relative -m-2 flex items-center space-x-4 rounded-xl p-2"
            >
              <div
                :class="[
                  item.background,
                  'flex h-12 w-12 shrink-0 items-center justify-center rounded-lg',
                ]"
              >
                <icon :icon="item.icon" class="h-6 w-6 text-white" />
              </div>
              <div>
                <p class="mt-1 text-sm text-neutral-600 dark:text-neutral-200">
                  {{ item.description }}
                </p>
              </div>
            </div>
          </li>
        </ul>
        <div class="mt-4 space-y-1 text-center sm:space-x-2">
          <span
            v-i-tooltip="
              $gate.isRegularUser()
                ? $t('user.not_authorized')
                : $t('mail.account.create_shared_info')
            "
          >
            <i-button
              variant="secondary"
              @click="createShared"
              class="sm:w-justify-around w-full justify-center sm:w-auto"
              :disabled="$gate.isRegularUser()"
              v-t="'mail.account.connect_shared'"
            ></i-button>
          </span>

          <i-button
            variant="secondary"
            @click="createPersonal"
            class="sm:w-justify-around w-full justify-center sm:w-auto"
            >{{ $t('mail.account.connect_personal') }}</i-button
          >
        </div>
      </div>
    </i-card>
    <router-view></router-view>
  </i-layout>
</template>
<script>
import { mapGetters } from 'vuex'
export default {
  data: () => ({
    loading: true,
  }),
  computed: {
    hasAccounts() {
      return this.accounts.length > 0
    },
    ...mapGetters({
      accounts: 'emailAccounts/accounts',
      latest: 'emailAccounts/latest',
    }),
    emptyStateItems() {
      return [
        {
          description: this.$t('mail.account.featured.sync'),
          icon: 'Refresh',
          background: 'bg-success-500',
        },
        {
          description: this.$t('mail.account.featured.save_time'),
          icon: 'DocumentAdd',
          background: 'bg-success-500',
        },
        {
          description: this.$t('mail.account.featured.placeholders'),
          icon: 'Variable',
          background: 'bg-success-500',
        },
        {
          description: this.$t('mail.account.featured.signature'),
          icon: 'Pencil',
          background: 'bg-success-500',
        },
        {
          description: this.$t('mail.account.featured.associations', {
            resources:
              this.$t('contact.contacts') + ', ' + this.$t('company.companies'),
            resource: this.$t('deal.deals'),
          }),
          icon: 'Mail',
          background: 'bg-success-500',
        },
        {
          description: this.$t('mail.account.featured.types'),
          icon: 'CheckCircle',
          background: 'bg-success-500',
        },
      ]
    },
  },
  methods: {
    /**
     * Delete the given account
     *
     * @param  {[type]} id
     *
     * @return {Void}
     */
    destroy(id) {
      this.$store
        .dispatch('emailAccounts/destroy', id)
        .then(() => Innoclapps.success(this.$t('mail.account.deleted')))
    },

    /**
     * Handle create shared account route
     *
     * @return {Void}
     */
    createShared() {
      this.$dialog
        .confirm({
          message: this.$t('mail.account.create_shared_confirmation_message'),
          title: false,
          icon: 'QuestionMarkCircle',
          iconWrapperColorClass: 'bg-info-100',
          iconColorClass: 'text-info-400',
          html: true,
          confirmText: this.$t('app.continue'),
          confirmVariant: 'secondary',
        })
        .then(() =>
          this.$router.push({
            name: 'create-email-account',
            query: {
              type: 'shared',
            },
          })
        )
    },

    /**
     * Handle create personal account route
     *
     * @return {Void}
     */
    createPersonal() {
      this.$router.push({
        name: 'create-email-account',
        query: {
          type: 'personal',
        },
      })
    },

    /**
     * Edit account
     *
     * @param  {Object} account
     *
     * @return {Void}
     */
    edit(account) {
      this.$router.push({
        name: 'edit-email-account',
        params: { id: account.id },
      })
    },

    /**
     * Toggle the account is primary state
     *
     * @param  {Boolean} isPrimary
     * @param  {Object}  account
     *
     * @return {Void}
     */
    togglePrimaryState(isPrimary, account) {
      if (isPrimary) {
        this.$store.dispatch('emailAccounts/setPrimary', account.id)
      } else {
        this.$store.dispatch('emailAccounts/removePrimary')
      }
    },

    /**
     * Toggle the account disabled state
     *
     * @param  {Object} account
     *
     * @return {Void}
     */
    toggleDisabledSyncState(account) {
      let action = account.is_sync_disabled ? 'enable' : 'disable'
      this.$store.dispatch(`emailAccounts/${action}Sync`, account.id)
    },

    /**
     * Re-authenticate account
     *
     * @param  {Object} account
     *
     * @return {Void}
     */
    reAuthenticate(account) {
      if (account.connection_type === 'Imap') {
        this.edit(account)
      } else {
        window.location.href =
          this.$store.getters['emailAccounts/OAuthConnectUrl'](
            account.connection_type,
            account.type
          ) + '?re_auth=1'
      }
    },
  },
  created() {
    // Used in EmailAccountForm.vue as well via the store
    this.$store.dispatch('emailAccounts/fetch').finally(() => {
      this.loading = false
      if (this.$route.query.viaOAuth) {
        this.edit(this.latest)
      }
    })
  },
}
</script>
