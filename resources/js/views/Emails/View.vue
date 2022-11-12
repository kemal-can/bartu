<template>
  <i-card
    :class="['email-' + email.id]"
    v-observe-visibility="{
      callback: visibilityChanged,
      once: true,
      throttle: 300,
      intersection: {
        threshold: 0.5,
      },
    }"
  >
    <template #header>
      <div class="flex space-x-1">
        <div class="inline-flex grow flex-col">
          <h3
            :class="[!email.is_read ? 'font-bold' : 'font-semibold']"
            class="truncate whitespace-normal text-base font-medium leading-6 text-neutral-700 dark:text-white md:text-lg"
          >
            <span
              v-once
              v-text="
                email.subject
                  ? email.subject
                  : '(' + $t('inbox.no_subject') + ')'
              "
            ></span>
          </h3>
          <span class="text-sm text-neutral-500 dark:text-neutral-300" v-once>
            {{ localizedDateTime(email.date) }}
          </span>
          <div class="flex">
            <associations-popover
              :resource-name="resourceName"
              :associateable="resourceRecord"
              :disabled="syncingAssociations"
              @change="syncAssociations"
              :associated="email"
            />
          </div>
        </div>
        <i-minimal-dropdown class="ml-3 self-start">
          <i-dropdown-item @click="destroy">{{
            $t('app.delete')
          }}</i-dropdown-item>
        </i-minimal-dropdown>
      </div>
    </template>
    <message-recipients
      v-once
      :label="$t('inbox.from')"
      :recipients="email.from"
    />
    <div class="flex">
      <div>
        <message-recipients
          v-once
          :label="$t('inbox.to')"
          :recipients="email.to"
        />
      </div>
      <div class="ml-3">
        <i-popover placement="top" class="flex items-center">
          <a href="#" class="link text-sm">{{ $t('app.details') }}</a>
          <template #popper>
            <message-recipients
              v-once
              :label="$t('inbox.from')"
              :recipients="email.from"
            />
            <message-recipients
              v-once
              :label="$t('inbox.to')"
              :recipients="email.to"
            />
            <message-recipients
              v-once
              :label="$t('inbox.reply_to')"
              :recipients="email.reply_to"
              :show-when-empty="false"
            />
            <message-recipients
              v-once
              :label="$t('inbox.cc')"
              :recipients="email.cc"
              :show-when-empty="false"
            />
            <message-recipients
              v-once
              :label="$t('inbox.bcc')"
              :recipients="email.bcc"
              :show-when-empty="false"
            />
          </template>
        </i-popover>
      </div>
    </div>

    <div class="mail-text all-revert" v-once>
      <div class="font-sans text-sm dark:text-white">
        <text-collapse
          v-if="email.visible_text"
          :text="email.visible_text"
          :length="250"
          class="mt-3"
        />

        <hidden-text :text="email.hidden_text" />
      </div>
    </div>

    <div
      v-once
      v-if="email.media.length > 0"
      class="mt-4 border-t border-neutral-200 py-4 dark:border-neutral-700"
    >
      <h4
        class="mb-3 font-medium text-neutral-700 dark:text-neutral-200"
        v-t="'mail.attachments'"
      />
      <message-attachments :email="email" />
    </div>
    <template #footer>
      <div class="flex divide-x divide-neutral-200 dark:divide-neutral-700">
        <a
          href="#"
          class="link flex items-center text-sm"
          @click.prevent="reply(true)"
        >
          <icon icon="Reply" class="mr-1.5 h-4 w-4" />
          {{ $t('inbox.reply') }}</a
        >
        <a
          href="#"
          class="link ml-2 flex items-center pl-2 text-sm"
          @click.prevent="replyAll"
          v-if="hasMoreReplyTo"
        >
          <icon icon="Reply" class="mr-1.5 h-4 w-4" />{{
            $t('inbox.reply_all')
          }}
        </a>
        <a
          href="#"
          class="link ml-2 flex items-center pl-2 text-sm"
          @click.prevent="forward(true)"
        >
          <icon icon="Share" class="mr-1.5 h-4 w-4" />
          {{ $t('inbox.forward') }}</a
        >
      </div>
    </template>
    <message-reply
      :message="email"
      :visible="isReplying || isForwarding"
      :forward="isForwarding"
      :resource-name="resourceName"
      :resource-record="resourceRecord"
      :to-all="replyToAll"
      @modal-hidden="replyModalHidden"
    />
  </i-card>
</template>
<script>
import MessageAttachments from '@/views/Emails/Attachments'
import MessageRecipients from '@/views/Emails/Recipients'
import TextCollapse from '@/components/TextCollapse'
import MessageReply from '@/views/Emails/Reply'
import HiddenText from '@/views/Emails/HiddenText'
import AssociationsPopover from '@/components/AssociationsPopover'
import InteractsWithResource from '@/mixins/InteractsWithResource'
import { ObserveVisibility } from 'vue-observe-visibility'
export default {
  mixins: [InteractsWithResource],
  components: {
    MessageAttachments,
    MessageRecipients,
    TextCollapse,
    MessageReply,
    AssociationsPopover,
    HiddenText,
  },
  directives: {
    ObserveVisibility,
  },
  props: {
    email: { required: true, type: Object },
  },
  data: () => ({
    isReplying: false,
    isForwarding: false,
    replyToAll: false,
    syncingAssociations: false,
  }),
  computed: {
    /**
     * Check whether there is more then one reply-to headers
     *
     * @return {Boolean}
     */
    hasMoreReplyTo() {
      return this.email.cc && this.email.cc.length > 0
    },
  },
  methods: {
    /**
     * Handle the reply modal hidden event
     *
     * @return {Void}
     */
    replyModalHidden() {
      // Allow timeout because the hidden blur sometimes is not removed
      setTimeout(() => {
        this.reply(false)
        this.forward(false)
      }, 300)
    },

    /**
     * Handle email visibility changed callback
     *
     * @param  {Boolean} isVisible
     * @param  {Object}  entry
     *
     * @return {Void}
     */
    visibilityChanged(isVisible, entry) {
      if (isVisible && !this.email.is_read) {
        Innoclapps.request()
          .post(`/emails/${this.email.id}/read`)
          .then(({ data }) => {
            this.updateResourceRecordHasManyRelationship(data, 'emails')
            this.decrementResourceRecordCount('unread_emails_for_user_count')
            this.$store.dispatch('emailAccounts/decrementUnreadCountUI')
          })
      }
    },

    /**
     * Delete the message
     *
     * @return {Void}
     */
    async destroy() {
      await this.$dialog.confirm()

      Innoclapps.request()
        .delete(`/emails/${this.email.id}`)
        .then(() => {
          if (!this.email.is_read) {
            this.decrementResourceRecordCount('unread_emails_for_user_count')
            this.$store.dispatch('emailAccounts/decrementUnreadCountUI')
          }

          this.removeResourceRecordHasManyRelationship(this.email.id, 'emails')
        })
    },

    /**
     * Sync the email associations
     *
     * @param  {Object} data
     *
     * @return {Void}
     */
    syncAssociations(data) {
      this.syncingAssociations = true
      Innoclapps.request()
        .post('associations/emails/' + this.email.id, data)
        .then(({ data }) =>
          this.updateResourceRecordHasManyRelationship(data, 'emails')
        )
        .finally(() => (this.syncingAssociations = false))
    },

    /**
     * Initiate reply to the message
     *
     * @param  {Boolean} state
     *
     * @return {Void}
     */
    reply(state = true) {
      this.isReplying = state
      this.replyToAll = false
    },

    /**
     * Initiate forward message
     *
     * @param  {Boolean} state
     *
     * @return {Void}
     */
    forward(state = true) {
      this.isForwarding = state
    },

    /**
     * Initialize reply all to mesage
     *
     * @return {Void}
     */
    replyAll() {
      this.isReplying = true
      this.replyToAll = true
    },
  },
}
</script>
