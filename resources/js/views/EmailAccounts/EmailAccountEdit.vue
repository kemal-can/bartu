<template>
  <i-slideover
    @hidden="goBack"
    :title="$t('mail.account.edit')"
    :visible="true"
    static-backdrop
    form
    @submit="update"
    @keydown="form.onKeydown($event)"
  >
    <email-account-form-fields
      :form="form"
      ref="form"
      :test-connection-form="testConnectionForm"
      :type="account.type || ''"
      :account="account"
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
        :loading="form.busy"
        :disabled="!isConnectionSuccessful || form.busy"
        type="submit"
        >{{ $t('app.save') }}</i-button
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
    account: {},
    requirements: Innoclapps.config.requirements,
  }),
  computed: {
    /**
     * Indicates whether the connection is successful when connecting IMAP account
     *
     * @return {Boolean}
     */
    isConnectionSuccessful() {
      return this.$store.state.emailAccounts.formConnectionState
    },
  },
  methods: {
    /**
     * Update the account in storage
     *
     * @return {Void}
     */
    update() {
      this.$store
        .dispatch('emailAccounts/update', {
          form: this.form,
          id: this.$route.params.id,
        })
        .then(account => {
          Innoclapps.success(this.$t('mail.account.updated'))
          this.goBack()
        })
    },

    /**
     * Prepare the component for edit
     *
     * @return {Void}
     */
    prepareComponent() {
      this.$store.commit('emailAccounts/SET_FORM_CONNECTION_STATE', true)

      this.$store
        .dispatch('emailAccounts/get', this.$route.params.id)
        .then(account => {
          this.form = new Form(account)
          this.form.folders = account.folders_tree
          this.account = account
        })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
