<template>
  <i-alert variant="info" class="mb-5" :show="accountConfigError !== null">
    <div v-html="accountConfigError" />
  </i-alert>
  <div
    :class="{
      'mb-3 rounded-md border border-warning-400 px-4 py-3':
        !form.connection_type,
    }"
  >
    <i-form-group
      required
      :label="$t('mail.account.type')"
      label-for="connection_type"
    >
      <i-custom-select
        :options="accountTypes"
        :clearable="false"
        :placeholder="$t('mail.account.select_type')"
        :disabled="!isCreateView"
        @option:selected="handleAccountConnectionTypeChange"
        v-model="form.connection_type"
      >
      </i-custom-select>
      <form-error :form="form" field="connection_type" />
    </i-form-group>
    <div
      v-if="isCreateView && hasHangingOAuthAccounts"
      :class="{ 'mb-3': form.connection_type }"
    >
      <p
        class="mb-2 mt-4 text-neutral-800 dark:text-neutral-200"
        v-t="'app.oauth.or_choose_existing'"
      ></p>
      <account-row
        v-for="oAuthAccount in notConnectedOAuthAccounts"
        :account="oAuthAccount"
        :key="oAuthAccount.id"
        :with-reconnect-link="false"
        class="mb-2"
      >
        <i-button
          class="ml-2"
          size="sm"
          @click="connectExistingOAuthAccount(oAuthAccount)"
          :disabled="oAuthAccount.requires_auth"
          >{{ $t('app.oauth.connect') }}</i-button
        >
      </account-row>
    </div>
  </div>
  <div
    class="mb-3 rounded-md border border-neutral-200 p-3 dark:border-neutral-600"
    v-if="isCreateView"
  >
    <i-form-label :label="$t('mail.account.sync_emails_from')" />
    <div class="mt-3 flex flex-col items-center sm:flex-row sm:space-x-2">
      <i-form-radio
        v-for="initialSync in initialSyncOptions"
        :key="initialSync.value"
        :label="initialSync.text"
        class="self-start"
        :id="'initial-sync-' + initialSync.value"
        :value="initialSync.value"
        v-model="form.initial_sync_from"
        name="initial_sync_from"
      />
    </div>
  </div>
  <div
    :class="{
      'pointer-events-none blur-sm': shouldBlurServerConfigureableFields,
    }"
  >
    <i-form-group
      :label="$t('mail.account.email_address')"
      label-for="email"
      required
    >
      <i-form-input
        v-model="form.email"
        name="email"
        :disabled="!isCreateView"
        spellcheck="false"
        autocomplete="off"
        type="email"
      >
      </i-form-input>
      <form-error :form="form" field="email" />
    </i-form-group>
    <i-form-group>
      <i-form-checkbox
        id="create_contact"
        name="create_contact"
        v-model:checked="form.create_contact"
        :label="$t('mail.account.create_contact')"
      />
    </i-form-group>
  </div>
  <div
    :class="{
      'pointer-events-none blur-sm': shouldBlurServerConfigureableFields,
    }"
    v-show="shouldShowServerConfigureableFields || isCreateView"
  >
    <i-form-group
      :label="$t('mail.account.password')"
      label-for="password"
      required
    >
      <i-form-input
        v-model="form.password"
        name="password"
        :placeholder="form.id ? '•••••••••••' : ''"
        spellcheck="false"
        autocomplete="off"
        type="password"
      >
      </i-form-input>
      <form-error :form="form" field="password" />
    </i-form-group>
    <i-form-group
      label-for="username"
      :label="$t('mail.account.username')"
      optional
    >
      <i-form-input
        v-model="form.username"
        autocomplete="off"
        spellcheck="false"
        name="username"
        id="username"
      >
      </i-form-input>
    </i-form-group>
    <div class="mb-3 mt-4">
      <h5
        class="mb-3 font-medium text-neutral-700 dark:text-neutral-100"
        v-t="'mail.account.incoming_mail'"
      ></h5>
      <i-form-group
        required
        :label="$t('mail.account.server')"
        label-for="imap_server"
      >
        <i-form-input
          v-model="form.imap_server"
          name="imap_server"
          placeholder="imap.example.com"
          spellcheck="false"
          autocomplete="off"
        >
        </i-form-input>
        <form-error :form="form" field="imap_server" />
      </i-form-group>
      <div class="grid grid-cols-6 gap-6">
        <div class="col-span-2">
          <i-form-group
            :label="$t('mail.account.port')"
            label-for="imap_port"
            required
          >
            <i-form-input
              v-model="form.imap_port"
              name="imap_port"
              type="number"
              autocomplete="off"
            >
            </i-form-input>
            <form-error :form="form" field="imap_port" />
          </i-form-group>
        </div>
        <div class="col-span-4">
          <i-form-group
            :label="$t('mail.account.encryption')"
            label-for="imap_encryption"
          >
            <i-custom-select
              :options="encryptions"
              :placeholder="$t('mail.account.without_encryption')"
              v-model="form.imap_encryption"
            />
            <form-error :form="form" field="imap_encryption" />
          </i-form-group>
        </div>
      </div>
    </div>
    <h5
      class="mb-3 font-medium text-neutral-700 dark:text-neutral-100"
      v-t="'mail.account.outgoing_mail'"
    ></h5>
    <i-form-group
      required
      :label="$t('mail.account.server')"
      label-for="smtp_server"
    >
      <i-form-input
        v-model="form.smtp_server"
        name="smtp_server"
        placeholder="smtp.example.com"
        spellcheck="false"
        autocomplete="off"
      >
      </i-form-input>
      <form-error :form="form" field="smtp_server" />
    </i-form-group>
    <div class="grid grid-cols-6 gap-6">
      <div class="col-span-2">
        <i-form-group
          :label="$t('mail.account.port')"
          label-for="smtp_port"
          required
        >
          <i-form-input
            v-model="form.smtp_port"
            name="smtp_port"
            type="number"
            autocomplete="off"
          >
          </i-form-input>
          <form-error :form="form" field="smtp_port" />
        </i-form-group>
      </div>
      <div class="col-span-4">
        <i-form-group
          :label="$t('mail.account.encryption')"
          label-for="smtp_encryption"
        >
          <i-custom-select
            :options="encryptions"
            :placeholder="$t('mail.account.without_encryption')"
            v-model="form.smtp_encryption"
          />
          <form-error :form="form" field="smtp_encryption" />
        </i-form-group>
      </div>
    </div>
    <i-form-group>
      <i-form-checkbox
        id="validate_cert"
        name="validate_cert"
        v-model:checked="form.validate_cert"
        :label="$t('mail.account.allow_non_secure_certificate')"
        :value="0"
        :unchecked-value="1"
      />
      <form-error :form="form" field="validate_cert" />
    </i-form-group>
  </div>
  <!-- Outlook account from custom header not working -->
  <div
    v-if="isShared"
    :class="{
      'pointer-events-none blur-sm': shouldBlurServerConfigureableFields,
      hidden: form.connection_type === 'Outlook',
    }"
  >
    <h5
      class="mb-3 font-medium text-neutral-700 dark:text-neutral-100"
      v-t="'mail.from_header'"
    ></h5>
    <div
      class="mb-3 rounded-md border border-neutral-200 p-3 dark:border-neutral-600"
    >
      <i-form-group
        :label="$t('mail.from_name')"
        :description="
          $t('mail.placeholders_info', { placeholders: '{agent}, {company}' })
        "
      >
        <i-form-input
          v-model="form.from_name_header"
          name="from_name_header"
          autocomplete="off"
        >
        </i-form-input>
        <form-error :form="form" field="from_name_header" />
      </i-form-group>
      <i-form-group>
        <p
          class="mb-1 font-medium text-neutral-700 dark:text-neutral-100"
          v-t="'app.preview'"
        ></p>
        <p
          class="mb-2 text-sm text-neutral-700 dark:text-neutral-300"
          v-t="'mail.from_header_info'"
        ></p>
        <div
          class="rounded-md border border-neutral-200 p-3 dark:border-neutral-600"
        >
          <div class="flex items-center">
            <div class="mr-4">
              <icon
                icon="Mail"
                class="h-6 w-6 text-neutral-600 dark:text-neutral-300"
              />
            </div>
            <div>
              <h6
                class="font-medium text-neutral-800 dark:text-neutral-100"
                v-text="parsedFromNameHeader"
              ></h6>
              <p
                class="text-neutral-700 dark:text-neutral-300"
                v-show="form.email"
                v-text="'<' + form.email + '>'"
              ></p>
            </div>
          </div>
        </div>
      </i-form-group>
    </div>
  </div>
  <div
    :class="{
      'pointer-events-none blur-sm': shouldBlurServerConfigureableFields,
    }"
    v-show="shouldShowServerConfigureableFields"
  >
    <i-form-group
      v-if="testConnectionForm.errors && testConnectionForm.errors.any()"
    >
      <i-alert variant="danger" class="mt-3" show>
        <p
          v-for="(error, field) in testConnectionForm.errors.all()"
          :key="field"
          class="mb-1"
          v-text="testConnectionForm.errors.get(field)"
        ></p>
      </i-alert>
    </i-form-group>
  </div>
  <div v-if="account">
    <folder-type-select
      v-model="form.sent_folder_id"
      :form="form"
      :folders="account.folders"
      field="sent_folder_id"
      :required="true"
      :label="$t('mail.account.select_sent_folder')"
    />
    <folder-type-select
      v-model="form.trash_folder_id"
      :form="form"
      :required="true"
      :folders="account.folders"
      field="trash_folder_id"
      :label="$t('mail.account.select_trash_folder')"
    />
  </div>
  <i-form-group
    v-if="foldersFetched"
    :label="$t('mail.account.active_folders')"
  >
    <form-folders class="mt-3" :folders="form.folders" />
  </i-form-group>
</template>
<script>
import find from 'lodash/find'
import reject from 'lodash/reject'
import FolderTypeSelect from './EmailAccountFormFolderTypeSelect'
import FormFolders from './EmailAccountFormFolders'
import AccountRow from '@/views/OAuth/AccountRow'
import { mapMutations, mapGetters } from 'vuex'

export default {
  emits: ['submit', 'ready'],
  components: {
    FormFolders,
    FolderTypeSelect,
    AccountRow,
  },
  data: () => ({
    encryptions: Innoclapps.config.mail.accounts.encryptions,
    accountTypes: Innoclapps.config.mail.accounts.connections,
    connectedUserOAuthAccounts: [],
  }),
  props: {
    type: {
      required: true,
      type: String,
    },
    account: Object,
    form: {
      required: true,
      type: Object,
      default() {
        return {}
      },
    },
    testConnectionForm: {
      required: true,
      type: Object,
      default() {
        return {}
      },
    },
  },
  computed: {
    ...mapGetters({
      accounts: 'emailAccounts/accounts',
    }),

    /**
     * Get all the user not connected OAuth accounts to email account
     *
     * @return {array}
     */
    notConnectedOAuthAccounts() {
      return reject(this.connectedUserOAuthAccounts, account =>
        find(this.accounts, ['email', account.email])
      )
    },

    /**
     * Check whether the user has OAuth accounts that are not connected as email account
     *
     * @return {Boolean}
     */
    hasHangingOAuthAccounts() {
      return this.notConnectedOAuthAccounts.length > 0
    },

    /**
     * The initial sync radio options
     *
     * @return {Array}
     */
    initialSyncOptions() {
      return [
        {
          text: this.$t('mail.account.sync_period_now'),
          value: this.appDate(),
        },
        {
          text: this.$t('mail.account.sync_period_1_month_ago'),
          value: this.createPeriodDate(1),
        },
        {
          text: this.$t('mail.account.sync_period_3_months_ago'),
          value: this.createPeriodDate(3),
        },
        {
          text: this.$t('mail.account.sync_period_6_months_ago'),
          value: this.createPeriodDate(6),
        },
      ]
    },

    /**
     * Check whether the account is shared
     *
     * @return {Boolean}
     */
    isShared() {
      return this.type === 'shared'
    },

    /**
     * Get the account config error
     *
     * @return {String|Null}
     */
    accountConfigError() {
      return this.$store.state.emailAccounts.accountConfigError
    },

    /**
     * Get the FROM NAME header for the preview
     *
     * @return {String}
     */
    parsedFromNameHeader() {
      if (!this.form.from_name_header) {
        return ''
      }

      return this.form.from_name_header
        .replace('{agent}', this.currentUser.name)
        .replace('{company}', this.setting('company_name') || '')
    },

    /**
     * Check whether the form is for create
     *
     * @return {Boolean}
     */
    isCreateView() {
      return !Boolean(this.account)
    },

    /**
     * Check whether the selected acount is IMAP
     *
     * @return {Boolean}
     */
    isImapAccount() {
      return this.form.connection_type === 'Imap'
    },

    /**
     * Check whether the server configurable fields should be blurred
     *
     * @return {Boolean}
     */
    shouldBlurServerConfigureableFields() {
      return this.isCreateView && !this.isImapAccount
    },

    /**
     * Check whether the server configurable fields should be hidden
     *
     * @return {Boolean}
     */
    shouldShowServerConfigureableFields() {
      return this.isImapAccount
    },

    /**
     * Check whether the IMAP account folders are fetched
     *
     * @return {Boolean}
     */
    foldersFetched() {
      if (!this.form.folders) {
        return false
      }

      return this.form.folders.length > 0
    },
  },
  methods: {
    ...mapMutations({
      setConnectionSuccessful: 'emailAccounts/SET_FORM_CONNECTION_STATE',
      setAccountConfigError: 'emailAccounts/SET_ACCOUNT_CONFIG_ERROR',
    }),
    /**
     * Create period date for the option for initial sync
     *
     * @param  {Number} months
     *
     * @return {String}
     */
    createPeriodDate(months) {
      return this.appMoment()
        .subtract(months, 'months')
        .format('YYYY-MM-DD HH:mm:ss')
    },
    /**
     * Handle account connection type changes
     *
     * @param  {String} val
     *
     * @return {Void}
     */
    handleAccountConnectionTypeChange(val) {
      this.setAccountConfigError(null)
      if (val == 'Outlook' && !this.isMicrosoftGraphConfigured) {
        this.setAccountConfigError(`Microsoft application not configured,
                        you must <a href="/settings/integrations/microsoft" rel="noopener noreferrer" target="_blank" class="font-medium underline text-danger-700 hover:text-danger-600">configure</a> your
                        Microsoft application in order to connect Outlook mail client.`)
      } else if (val == 'Gmail' && !this.isGoogleApiConfigured) {
        this.setAccountConfigError(`Google application project not configured,
                        you must <a href="/settings/integrations/google" rel="noopener noreferrer" target="_blank" class="font-medium underline text-danger-700 hover:text-danger-600">configure</a> your
                        Google application project in order to connect Gmail mail client.`)
      } else if (val === 'Imap' && !Innoclapps.config.requirements.imap) {
        this.setAccountConfigError(
          `In order to use IMAP account type, you will need to enable the PHP extension "imap".`
        )
      }
    },

    /**
     * Retrieve the oAuth accounts for the user
     *
     * @return {void}
     */
    retrieveUserConnectedOAuthAccounts() {
      Innoclapps.request()
        .get('oauth/accounts')
        .then(({ data }) => (this.connectedUserOAuthAccounts = data))
    },

    /**
     * Connect the existing OAuth account
     *
     * @param  {Object} account
     *
     * @return {Void}
     */
    connectExistingOAuthAccount(account) {
      switch (account.type) {
        case 'microsoft':
          this.form.fill('connection_type', 'Outlook')
          break
        case 'google':
          this.form.fill('connection_type', 'Gmail')
          break
        default:
          this.form.fill('connection_type', account.type)
      }

      this.$emit('submit')
    },
  },
  mounted() {
    this.retrieveUserConnectedOAuthAccounts()
    this.$emit('ready')
  },
  unmounted() {
    // Reset connection state
    this.setConnectionSuccessful(false)
    this.setAccountConfigError(null)
  },
}
</script>
