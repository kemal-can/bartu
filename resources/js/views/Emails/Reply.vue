<template>
  <i-modal
    size="xl"
    ref="modal"
    static-backdrop
    @hidden="modalHidden"
    @shown="modalShown"
    :hide-footer="showTemplates"
    :visible="visible"
    :title="
      $t('inbox.' + (forward ? 'forward_message' : 'reply_to_message'), {
        subject: message.subject,
      })
    "
  >
    <div
      class="-mx-6 mb-4 border-y border-neutral-200 px-6 py-3 dark:border-neutral-700"
    >
      <div class="flex">
        <div class="mr-4">
          <a
            href="#"
            @click.prevent="showTemplates = true"
            v-show="!showTemplates"
            class="link text-sm font-medium"
            v-t="'mail.templates.templates'"
          />
          <a
            href="#"
            class="link text-sm font-medium"
            v-show="showTemplates"
            @click.prevent="showTemplates = false"
            v-t="'mail.compose'"
          />
        </div>
        <div v-show="!showTemplates" class="font-medium">
          <associations-popover
            v-model="form.associations"
            :custom-selected-records="customAssociationsValue"
            :associated="message"
          />
        </div>
      </div>
    </div>

    <div v-show="!showTemplates">
      <i-alert variant="danger" dismissible :show="hasInvalidAddresses">
        {{ $t('mail.validation.invalid_recipients') }}
      </i-alert>
      <mail-recipient
        :form="form"
        type="to"
        ref="to"
        @recipient-removed="dissociateRemovedRecipients"
        @recipient-selected="handleRecipientSelectedEvent"
        :label="$t('inbox.to')"
      >
        <template #after>
          <div class="ml-2 space-x-2">
            <a
              href="#"
              @click.prevent="setWantsCC"
              v-if="!wantsCc"
              v-t="'inbox.cc'"
              class="link"
            />
            <a
              href="#"
              @click.prevent="setWantsBCC"
              v-if="!wantsBcc"
              v-t="'inbox.bcc'"
              class="link"
              >Bcc</a
            >
          </div>
        </template>
      </mail-recipient>
      <hr class="my-3 border-t border-neutral-200 dark:border-neutral-700" />
      <div v-if="wantsCc">
        <mail-recipient
          :form="form"
          type="cc"
          ref="cc"
          @recipient-removed="dissociateRemovedRecipients"
          @recipient-selected="associateSelectedRecipients"
          :label="$t('inbox.cc')"
        />
        <hr class="my-3 border-t border-neutral-200 dark:border-neutral-700" />
      </div>
      <div v-if="wantsBcc">
        <mail-recipient
          :form="form"
          type="bcc"
          ref="bcc"
          :label="$t('inbox.bcc')"
        />
        <hr class="my-3 border-t border-neutral-200 dark:border-neutral-700" />
      </div>
      <div class="flex items-center">
        <div class="w-14">
          <i-form-label for="subject" :label="$t('inbox.subject')" />
        </div>
        <div class="grow">
          <i-form-input id="subject" v-model="form.subject" />
          <form-error :form="form" field="subject" />
        </div>
      </div>
      <hr class="my-3 border-t border-neutral-200 dark:border-neutral-700" />
      <editor
        @placeholder-inserted="parsePlaceholdersForMessage"
        :placeholders="placeholders"
        ref="editor"
        :with-drop="true"
        v-model="form.message"
      />
      <div class="relative mt-3">
        <media-upload
          @file-uploaded="attachmentUploaded"
          :action-url="`${$store.state.apiURL}/media/pending/${attachmentsDraftId}`"
          :select-file-text="$t('app.attach_files')"
        >
          <media-items-list
            :class="{
              'border-b border-neutral-200 dark:border-neutral-700':
                attachmentsBeingForwarded.length > 0 && attachments.length > 0,
            }"
            :items="attachmentsBeingForwarded"
            :authorize-delete="true"
            @delete-requested="removeAttachmentBeingForwarded"
          />
          <media-items-list
            class="mb-3"
            :items="attachments"
            :authorize-delete="true"
            @delete-requested="destroyAttachment"
          />
        </media-upload>
      </div>
    </div>
    <template #modal-footer="{ cancel }">
      <div class="flex flex-col sm:flex-row sm:items-center">
        <div class="grow">
          <create-follow-up-task :form="form" v-show="resourceName" />
        </div>
        <div
          class="mt-2 space-y-2 sm:mt-0 sm:flex sm:items-center sm:space-y-0 sm:space-x-2"
        >
          <i-button class="w-full sm:w-auto" variant="white" @click="cancel">{{
            $t('app.cancel')
          }}</i-button>
          <i-button
            class="w-full sm:w-auto"
            :loading="sending"
            :disabled="sendButtonIsDisabled"
            @click="send"
          >
            {{ !forward ? $t('inbox.reply') : $t('inbox.forward') }}
          </i-button>
        </div>
      </div>
    </template>
    <mail-templates v-if="showTemplates" @selected="handleTemplateSelected" />
  </i-modal>
</template>
<script>
import CreateFollowUpTask from '@/views/Activity/CreateFollowUpTask'
import MailRecipient from './RecipientSelector'
import Editor from '@/components/MailEditor'
import AssociationsPopover from '@/components/AssociationsPopover'
import MediaUpload from '@/components/Media/MediaUpload'
import MediaItemsList from '@/components/Media/MediaItemsList'
import MailTemplates from '@/views/Emails/Templates/Index'
import HelpsComposingMessage from './HelpsComposingMessage'
import findIndex from 'lodash/findIndex'
const cleanSubjectSearch = [
  // Re
  'RE:',
  'SV:',
  'Antw:',
  'VS:',
  'RE:',
  'REF:',
  'ΑΠ:',
  'ΣΧΕΤ:',
  'Vá:',
  'R:',
  'RIF:',
  'BLS:',
  'RES:',
  'Odp:',
  'YNT:',
  'ATB:',
  // FW
  'FW:',
  'FWD:',
  'Doorst:',
  'VL:',
  'TR:',
  'WG:',
  'ΠΡΘ:',
  'Továbbítás:',
  'I:',
  'FS:',
  'TRS:',
  'VB:',
  'RV:',
  'ENC:',
  'PD:',
  'İLT:',
  'YML:',
]
import { randomString } from '@/utils'

export default {
  emits: ['modal-hidden'],
  mixins: [HelpsComposingMessage],
  components: {
    CreateFollowUpTask,
    MailRecipient,
    Editor,
    MediaUpload,
    MediaItemsList,
    AssociationsPopover,
    MailTemplates,
  },
  props: {
    visible: {
      type: Boolean,
      default: false,
    },
    toAll: {
      type: Boolean,
      default: false,
    },
    forward: {
      type: Boolean,
      default: false,
    },
    message: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    attachmentsBeingForwarded: [],
  }),
  computed: {
    /**
     * Indicates whether the send button is disabled
     *
     * @return {Boolean}
     */
    sendButtonIsDisabled() {
      return this.form.to.length === 0 || this.sending
    },

    /**
     * Check whether the message has cc header
     *
     * @return {Boolean}
     */
    hasCC() {
      return this.message.cc && this.message.cc.length > 0
    },

    /**
     * Check whether the message has reply to header
     *
     * @return {Boolean}
     */
    hasReplyTo() {
      return this.message.reply_to && this.message.reply_to.length > 0
    },
  },

  methods: {
    /**
     * Remove the attachment being forwarded
     *
     * @param  {Object} media
     *
     * @return {Void}
     */
    removeAttachmentBeingForwarded(media) {
      const index = findIndex(this.attachmentsBeingForwarded, ['id', media.id])
      this.attachmentsBeingForwarded.splice(index, 1)
    },

    /**
     * Handle template selected
     *
     * @param  {Object} template
     *
     * @return {Void}
     */
    handleTemplateSelected(template) {
      this.form.message = template.body + this.form.message
      this.showTemplates = false
      this.parsePlaceholdersForMessage()
      this.$nextTick(() => this.$refs.editor.focus())
    },

    /**
     * Handle modal shown event
     * Each time the modal is shown we need to generate new draft id
     * for the attachments
     *
     * @return {Void}
     */
    modalShown() {
      this.attachmentsDraftId = randomString(10)
      let subject = this.cleanupSubject(this.message.subject)

      if (this.forward) {
        this.attachmentsBeingForwarded = this.cleanObject(this.message.media)

        // Reset the recipients on forward
        ;['cc', 'bcc', 'to'].forEach(key =>
          this.form.set(key, key === 'to' ? [] : null)
        )

        this.form.message =
          "<br /><div class='bartu_attr'>" +
          this.$t('inbox.forwarded_message_placeholder', {
            from: `${
              this.message.from.name ? this.message.from.name + ' ' : ''
            }&lt;${this.message.from.address}&gt;`,
            date: this.dateFromAppTimezone(this.message.date, 'llll'),
            subject: this.message.subject,
            to: this.message.to
              .reduce((carry, to) => {
                carry.push(
                  (to.name ? to.name + ' ' : '') + `&lt;${to.address}&gt;`
                )
                return carry
              }, [])
              .join(', '),
            pre: '----------',
            after: '---------',
          }) +
          '</div>' +
          '<br /><div>' +
          this.message.editor_text +
          '</div>'

        if (subject) {
          subject = Innoclapps.config.mail.forward_prefix + subject
        }

        this.$refs.to.focus()
      } else {
        this.attachmentsBeingForwarded = []
        this.setRecipients()
        this.form.message = this.createQuoteOfPreviousMessage()

        setTimeout(() => this.$refs.editor.focus(), 500)

        if (subject) {
          subject = Innoclapps.config.mail.reply_prefix + subject
        }
      }

      this.form.subject = subject
    },

    /**
     * Create quote from the message
     *
     * @return {String}
     */
    createQuoteOfPreviousMessage() {
      let body = this.message.editor_text

      // Maybe the message was empty?
      if (!body) {
        return ''
      }

      let from = `&lt;${this.message.from.address}&gt;`

      if (this.message.from.name) {
        from = this.message.from.name + ' ' + from
      }

      let wroteText = `On ${this.dateFromAppTimezone(
        this.message.date,
        'llll'
      )} ${from} wrote:`

      // 2 new lines allow the EmailReplyParser to properly determine the actual reply message
      return (
        "<br /><div class='bartu_attr'>" +
        wroteText +
        '</div><blockquote class="bartu_quote">' +
        body +
        '</blockquote>'
      )
    },

    /**
     * Handle modal shown hidden
     *
     * Reset the state, we need to reset the form and the
     * attachments because when the modal is hidden each time
     * new attachmentsDraftId is generated
     *
     * @return {Void}
     */
    modalHidden() {
      this.form.reset()
      this.attachments = []
      this.customAssociationsValue = {}
      this.$emit('modal-hidden')
    },

    /**
     * Send the message
     *
     * @return {Void}
     */
    send() {
      if (!this.forward) {
        this.sendRequest(`/emails/${this.message.id}/reply`)
      } else {
        this.form.fill(
          'forward_attachments',
          this.attachmentsBeingForwarded.map(attach => attach.id)
        )
        this.sendRequest(`/emails/${this.message.id}/forward`)
      }
    },

    /**
     * Set reply to all
     */
    setReplyToAll() {
      if (!this.setToViaReplyTo()) {
        this.setToViaFrom()
      }

      if (this.hasCC) {
        let cc = []
        this.message.cc.forEach((recipient, index) => {
          let existsAsReplyTo = findIndex(this.message.reply_to, [
            'address',
            recipient.address,
          ])

          if (existsAsReplyTo === -1) {
            cc.push({
              address: recipient.address,
              name: recipient.name,
            })
          }
        })
        if (cc.length > 0) {
          this.form.set('cc', cc)
        }
      }
    },

    /**
     * Set the toa via reply to header
     */
    setToViaReplyTo() {
      if (this.hasReplyTo) {
        let recipients = []
        this.message.reply_to.forEach((recipient, index) => {
          recipients.push({
            address: recipient.address,
            name: recipient.name,
          })
        })

        this.form.set('to', recipients)

        return true
      }

      return false
    },

    /**
     * Set the toa via the from header
     */
    setToViaFrom() {
      if (this.message.from) {
        // Maybe draft with no from header?
        this.form.set('to', [
          {
            address: this.message.from.address,
            name: this.message.from.name,
          },
        ])
      }
    },

    /**
     * Set the message recipients
     */
    setRecipients() {
      if (this.toAll) {
        this.setReplyToAll()
      } else {
        if (!this.setToViaReplyTo()) {
          this.setToViaFrom()
        }
        // Reset the CC in case of previous replyToAll clicked
        this.form.set('cc', null)
      }
    },

    /**
     * Clean the given subject
     *
     * @param  {String|null} subject
     *
     * @return {String|null}
     */
    cleanupSubject(subject) {
      if (subject === null) {
        return subject
      }

      const search = cleanSubjectSearch
      search.push(
        ...[
          Innoclapps.config.mail.reply_prefix,
          Innoclapps.config.mail.forward_prefix,
        ]
      )

      return subject.replace(new RegExp(search.join('|'), 'gmi'), '').trim()
    },
  },
  created() {
    this.form.set('associations', this.message.associations)
  },
}
</script>
