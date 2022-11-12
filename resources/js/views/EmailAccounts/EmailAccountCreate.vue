<template>
  <i-slideover
    @hidden="goBack"
    :title="$t('mail.account.create')"
    :visible="true"
    static-backdrop
    form
    @submit="store"
    @keydown="form.onKeydown($event)"
  >
    <email-account-form-fields
      ref="form"
      :type="$route.query.type"
      :test-connection-form="testConnectionForm"
      @ready="setInitialSyncFromDefault"
      @submit="store"
      :form="form"
    />
    <template #modal-ok>
      <i-button
        v-if="isImapAccount && requirements.imap"
        :loading="testConnectionInProgress"
        :disabled="testConnectionInProgress"
        @click="testConnection"
      >
        {{ $t('mail.account.test_connection_and_retrieve_folders') }}
      </i-button>
      <i-button
        variant="primary"
        type="submit"
        :disabled="isSubmitDisabled"
        :loading="form.busy"
        >{{ $t('mail.account.connect') }}</i-button
      >
    </template>
  </i-slideover>
</template>
<script>
import EmailAccountFormFields from '@/views/EmailAccounts/EmailAccountFormFields'
import TestsImapConnection from '@/views/EmailAccounts/TestsImapConnection'
import Form from '@/components/Form/Form'

export default {
  mixins: [TestsImapConnection],
  components: { EmailAccountFormFields },
  data: () => ({
    form: {},
    requirements: Innoclapps.config.requirements,
  }),
  computed: {
    /**
     * Indicates whether the submit button is disabled
     *
     * @return {Boolean}
     */
    isSubmitDisabled() {
      return (
        (!this.isConnectionSuccessful && this.isImapAccount) ||
        this.form.busy ||
        this.accountConfigError !== null ||
        !this.form.connection_type
      )
    },

    /**
     * Get the account being connected configuration error
     *
     * @return {String|null}
     */
    accountConfigError() {
      return this.$store.state.emailAccounts.accountConfigError
    },

    /**
     * Indicates whether the connection is successful when connecting IMAP account
     *
     * @return {Boolean}
     */
    isConnectionSuccessful() {
      return this.$store.state.emailAccounts.formConnectionState
    },

    /**
     * Get the redirect URL when connecting OAuth Account
     *
     * @return {String}
     */
    oAuthRedirectUrl() {
      return (
        this.$store.getters['emailAccounts/OAuthConnectUrl'](
          this.form.connection_type,
          this.$route.query.type
        ) +
        '?period=' +
        this.form.initial_sync_from
      )
    },
  },
  methods: {
    /**
     * Set the initial sync option
     */
    setInitialSyncFromDefault() {
      this.form.set(
        'initial_sync_from',
        this.$refs.form.initialSyncOptions[2].value
      )
    },

    /**
     * Connect new email account
     *
     * @return {Void}
     */
    store() {
      if (!this.isImapAccount) {
        window.location.href = this.oAuthRedirectUrl
        return
      }

      this.$store.dispatch('emailAccounts/store', this.form).then(() => {
        Innoclapps.success(this.$t('mail.account.created'))
        this.$router.push({ name: 'email-accounts-index' })
      })
    },

    /**
     * Prepare the component
     *
     * @return {Void}
     */
    prepareComponent() {
      let formObject = {
        connection_type: null,
        email: null,
        password: null,
        username: null,
        imap_server: null,
        imap_port: 993,
        imap_encryption: 'ssl',
        smtp_server: null,
        smtp_port: 465,
        smtp_encryption: 'ssl',
        validate_cert: 1,
        folders: [],
        create_contact: false,
      }

      if (this.$route.query.type == 'shared') {
        // from_name_header is available for shared accounts only
        formObject['from_name_header'] =
          Innoclapps.config.mail.accounts.from_name
      } else if (this.$route.query.type == 'personal') {
        // Indicates that the account is shared
        formObject['user_id'] = this.currentUser.id
      } else {
        // We need indicator whether the account is shared or personal
        // if not provided e.q. route accessed directly, show 404
        this.$router.push({
          name: '404',
        })
      }

      this.form = new Form(formObject)
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
