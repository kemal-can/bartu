/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import InteractsWithMailPlaceholders from '@/mixins/InteractsWithMailPlaceholders'
import Form from '@/components/Form/Form'
import findIndex from 'lodash/findIndex'
import find from 'lodash/find'

export default {
  mixins: [InteractsWithMailPlaceholders],
  props: {
    resourceName: String,
    resourceRecord: Object, // Needs to be provided if resourceName is provided
  },
  data() {
    return {
      sending: false,
      showTemplates: false,
      customAssociationsValue: {},
      attachmentsDraftId: null,
      attachments: [],
      form: new Form({
        with_task: false,
        task_date: null,
        subject: null,
        // Use store, as this.currentUser is not available
        message: this.$store.getters['users/current'].mail_signature
          ? '<br /><br />----------<br />' +
            this.$store.getters['users/current'].mail_signature
          : '',
        to: [],
        cc: null,
        bcc: null,
        associations: {},
      }),
    }
  },
  computed: {
    /**
     * Indicates whether any of the addresses are invalid
     *
     * @return {Boolean}
     */
    hasInvalidAddresses() {
      return Boolean(
        this.form.errors.get('to.0.address') ||
          this.form.errors.get('cc.0.address') ||
          this.form.errors.get('bcc.0.address')
      )
    },

    /**
     * Check whether the user wants to add CC
     *
     * @return {Boolean}
     */
    wantsCc() {
      return this.form.cc !== null
    },

    /**
     * Check whether the user wants to add BCC
     *
     * @return {Boolean}
     */
    wantsBcc() {
      return this.form.bcc !== null
    },
  },
  methods: {
    /**
     * Parse the placeholders from the first recipient
     *
     * @return {Void}
     */
    parsePlaceholdersForMessage() {
      if (!this.form.message) {
        return
      }

      const resources = []
      if (this.form.to.length > 0 && this.form.to[0].resourceName) {
        resources.push({
          name: this.form.to[0].resourceName,
          id: this.form.to[0].id,
        })
      }

      // viaResource
      if (this.resourceName) {
        resources.push({
          name: this.resourceName,
          id: this.resourceRecord.id,
        })
      }

      if (resources.length > 0) {
        this.parsePlaceholders(resources, this.form.message).then(
          content => (this.form.message = content)
        )
      }
    },

    /**
     * Handle the created follow up task
     *
     * @param  {Object} task
     *
     * @return {Void}
     */
    handleCreatedFollowUpTask(task) {
      this.$store.commit(
        this.resourceName + '/ADD_RECORD_HAS_MANY_RELATIONSHIP',
        {
          relation: 'activities',
          item: task,
        }
      )

      this.$store.commit(this.resourceName + '/SET_RECORD', {
        incomplete_activities_for_user_count:
          this.resourceRecord.incomplete_activities_for_user_count + 1,
      })
    },

    /**
     * Make a send request
     *
     * @param  {String} url
     *
     * @return {Void}
     */
    sendRequest(url) {
      this.sending = true
      this.form.fill('attachments_draft_id', this.attachmentsDraftId)

      if (this.resourceName) {
        this.form.fill('via_resource', this.resourceName)
        this.form.fill('via_resource_id', this.resourceRecord.id)
      }

      Innoclapps.request()
        .post(url, this.form.data())
        .then(response => {
          this.$refs.modal.hide()
          this.form.reset()

          if (response.status !== 202) {
            Innoclapps.success(this.$t('inbox.message_sent'))
            Innoclapps.$emit('email-sent', response.data.message)
          } else {
            Innoclapps.info(this.$t('mail.message_queued_for_sending'))
          }

          if (response.data.createdActivity && this.resourceName) {
            this.handleCreatedFollowUpTask(response.data.createdActivity)
          }
        })
        .finally(() => (this.sending = false))
    },

    /**
     * Set CC field
     */
    setWantsCC() {
      this.form.cc = []
      setTimeout(() => this.$refs.cc.focus(), 500)
    },

    /**
     * Set BCC field
     */
    setWantsBCC() {
      this.form.bcc = []
      setTimeout(() => this.$refs.bcc.focus(), 500)
    },

    /**
     * Handle attachment uploaded event
     *
     * @param  {Object} media
     *
     * @return {Void}
     */
    attachmentUploaded(media) {
      this.attachments.push(media)
    },

    /**
     * Destroy the pending attachment
     *
     * @param  {Object} media
     *
     * @return {Void}
     */
    destroyAttachment(media) {
      Innoclapps.request()
        .delete('/media/pending/' + media.pending_data.id)
        .then(() => {
          let index = findIndex(this.attachments, ['id', media.id])
          this.attachments.splice(index, 1)
        })
    },
    handleRecipientSelectedEvent(e) {
      this.associateSelectedRecipients(e)
      this.parsePlaceholdersForMessage()
    },
    /**
     * When a recipient is removed we will dissociate
     * the removed recipients from the associations component
     *
     * @param  {Object} option
     *
     * @return {Void}
     */
    dissociateRemovedRecipients(option) {
      if (
        !option.resourceName ||
        !this.customAssociationsValue[option.resourceName]
      ) {
        return
      }

      let index = findIndex(this.customAssociationsValue[option.resourceName], [
        'id',
        option.id,
      ])

      if (index !== -1) {
        this.customAssociationsValue[option.resourceName].splice(index, 1)
      }
    },

    /**
     * When a recipient is selected we will
     * associate automatically to the associatiosn component
     *
     * @param  {Array} records
     *
     * @return {Void}
     */
    associateSelectedRecipients(records) {
      records.forEach(record => {
        if (record.resourceName) {
          if (!this.customAssociationsValue[record.resourceName]) {
            this.customAssociationsValue[record.resourceName] = []
          }

          if (
            !find(this.customAssociationsValue[record.resourceName], [
              'id',
              record.id,
            ])
          ) {
            this.customAssociationsValue[record.resourceName].push({
              id: record.id,
              display_name: record.name,
            })
          }
        }
      })
    },
  },
}
