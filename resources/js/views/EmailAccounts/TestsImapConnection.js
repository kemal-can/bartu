/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import Form from '@/components/Form/Form'

export default {
  data: () => ({
    testConnectionForm: {},
    testConnectionInProgress: false,
  }),
  computed: {
    /**
     * Check whether the selected acount is IMAP
     *
     * @return {Boolean}
     */
    isImapAccount() {
      return this.form.connection_type === 'Imap'
    },
  },
  methods: {
    /**
     * Test IMAP account connection
     *
     * @return {Void}
     */
    testConnection() {
      this.testConnectionForm = new Form({
        id: this.form.id || null,
        connection_type: this.form.connection_type,
        email: this.form.email,
        password: this.form.password,
        username: this.form.username,
        imap_server: this.form.imap_server,
        imap_port: this.form.imap_port,
        imap_encryption: this.form.imap_encryption,
        smtp_server: this.form.smtp_server,
        smtp_port: this.form.smtp_port,
        smtp_encryption: this.form.smtp_encryption,
        validate_cert: this.form.validate_cert,
      })

      this.testConnectionInProgress = true

      this.testConnectionForm
        .post('/mail/accounts/connection')
        .then(data => {
          this.form.requires_auth = false
          this.$refs.form.setConnectionSuccessful(true)
          this.form.folders = data.folders
        })
        .catch(error => this.$refs.form.setConnectionSuccessful(false))
        .finally(() => (this.testConnectionInProgress = false))
    },
  },
}
