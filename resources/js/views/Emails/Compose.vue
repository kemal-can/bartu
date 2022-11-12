<template>
  <i-modal
    size="xl"
    ref="modal"
    static-backdrop
    @hidden="modalHidden"
    @shown="modalShown"
    :hide-footer="showTemplates"
    :visible="visible"
    :title="$t('inbox.new_message')"
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
            :resource-name="resourceName"
            :associateable="
              resourceName ? $store.state[resourceName].record : {}
            "
            :custom-selected-records="customAssociationsValue"
            v-model="form.associations"
          />
        </div>
      </div>
    </div>
    <div v-show="!showTemplates">
      <i-overlay :show="!componentReady">
        <i-alert variant="danger" dismissible :show="hasInvalidAddresses">
          {{ $t('mail.validation.invalid_recipients') }}
        </i-alert>
        <mail-recipient
          :form="form"
          type="to"
          @recipient-removed="dissociateRemovedRecipients"
          @recipient-selected="handleRecipientSelectedEvent"
          :label="$t('inbox.to')"
        >
          <template #after>
            <div class="ml-2 space-x-2" v-if="!wantsBcc || !wantsCc">
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
                class="link"
                v-t="'inbox.bcc'"
              />
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
          <hr
            class="my-3 border-t border-neutral-200 dark:border-neutral-700"
          />
        </div>
        <div v-if="wantsBcc">
          <mail-recipient
            ref="bcc"
            :form="form"
            type="bcc"
            :label="$t('inbox.bcc')"
          />
          <hr
            class="my-3 border-t border-neutral-200 dark:border-neutral-700"
          />
        </div>
        <div class="flex items-center">
          <i-form-label :label="$t('inbox.from')" class="w-14" />
          <dropdown-select
            :items="accounts"
            value-key="id"
            auto-size="min"
            v-model="account"
            label-key="email"
          >
            <template #label="{ label, item }">
              {{ item.formatted_from_name_header }}
              <span v-text="'<' + label + '>'"></span>
            </template>
          </dropdown-select>
        </div>
        <hr class="my-3 border-t border-neutral-200 dark:border-neutral-700" />
        <div class="flex items-center">
          <div class="w-14">
            <i-form-label :label="$t('inbox.subject')" for="subject" />
          </div>
          <div class="grow">
            <i-form-input id="subject" v-model="form.subject" ref="subject" />
            <form-error :form="form" field="subject" />
          </div>
        </div>
        <hr class="my-3 border-t border-neutral-200 dark:border-neutral-700" />
        <editor
          v-model="form.message"
          :placeholders="placeholders"
          :with-drop="true"
          @placeholder-inserted="parsePlaceholdersForMessage"
          ref="editor"
        />
        <div class="relative mt-3">
          <media-upload
            @file-uploaded="attachmentUploaded"
            :action-url="`${$store.state.apiURL}/media/pending/${attachmentsDraftId}`"
            :select-file-text="$t('app.attach_files')"
          >
            <media-items-list
              class="mb-3"
              :items="attachments"
              :authorize-delete="true"
              @delete-requested="destroyAttachment"
            />
          </media-upload>
        </div>
      </i-overlay>
    </div>
    <template #modal-footer="{ cancel }">
      <div class="flex flex-col sm:flex-row sm:items-center">
        <div class="grow">
          <create-follow-up-task :form="form" v-show="Boolean(resourceName)" />
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
            {{ $t('inbox.send') }}
          </i-button>
        </div>
      </div>
    </template>
    <mail-templates v-if="showTemplates" @selected="handleTemplateSelected" />
  </i-modal>
</template>
<script>
import { mapGetters } from 'vuex'
import CreateFollowUpTask from '@/views/Activity/CreateFollowUpTask'
import MailRecipient from './RecipientSelector'
import Editor from '@/components/MailEditor'
import AssociationsPopover from '@/components/AssociationsPopover'
import MediaUpload from '@/components/Media/MediaUpload'
import MediaItemsList from '@/components/Media/MediaItemsList'
import MailTemplates from '@/views/Emails/Templates/Index'
import HelpsComposingMessage from './HelpsComposingMessage'
import { randomString } from '@/utils'

export default {
  emits: ['modal-hidden'],
  mixins: [HelpsComposingMessage],
  components: {
    CreateFollowUpTask,
    MailRecipient,
    Editor,
    AssociationsPopover,
    MediaUpload,
    MediaItemsList,
    MailTemplates,
  },
  props: {
    to: {
      type: Array,
      default: function () {
        return []
      },
    },
    visible: {
      type: Boolean,
      default: false,
    },
    defaultAccount: Object,
  },
  data() {
    return {
      account: {},
      componentReady: false,
    }
  },
  watch: {
    /**
     * Watch the defualt account for changes
     *
     * @param  {Object} newVal
     *
     * @return {Void}
     */
    defaultAccount: function (newVal) {
      this.account = newVal
    },

    // In case the to is updated
    // we need to update the form value too
    // e.q. update contact email and click create email
    // if we don't update the value the old email will be used
    to: {
      handler: function (newVal) {
        this.form.to = newVal
        this.associateSelectedRecipients(newVal)
      },
      immediate: true,
    },
  },
  computed: {
    ...mapGetters({
      accounts: 'emailAccounts/accounts',
    }),

    /**
     * Indicates whether the send button is disaled
     *
     * @return {Boolean}
     */
    sendButtonIsDisabled() {
      return this.form.to.length === 0 || !this.form.subject || this.sending
    },
  },
  methods: {
    /**
     * Handle template selected
     *
     * @param  {Object} template
     *
     * @return {Void}
     */
    handleTemplateSelected(template) {
      // Allow the sales agent to enter custom subject if needed
      if (!this.form.subject) {
        this.form.subject = template.subject
      }

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
      // If prevously there was to selected, use the same to as associations
      // e.q. open deal modal, close deal modal, open again, the form.to won't be associated
      if (this.form.to) {
        this.associateSelectedRecipients(this.form.to)
      }

      this.attachmentsDraftId = randomString(10)
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

      // Add to again if there was TO recipients provided
      if (this.to) {
        this.form.to = this.to
      }

      this.attachments = []
      this.customAssociationsValue = {}
      this.$emit('modal-hidden')
    },

    /**
     * Send an email
     *
     * @return {Void}
     */
    send() {
      this.sendRequest(`/inbox/emails/${this.account.id}`)
    },

    /**
     * Fetch the email accounts if not fetched
     *
     * @return {Void}
     */
    prepareComponent() {
      this.$store.dispatch('emailAccounts/fetch').then(accounts => {
        this.account = this.defaultAccount || accounts[0]
        this.componentReady = true
      })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
