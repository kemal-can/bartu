<template>
  <!-- If recipients is empty, it can be a draft message with not yet added e.q. to headers -->
  <div v-show="showRecipients">
    <p class="space-x-1 text-sm text-neutral-800 dark:text-neutral-100">
      <span class="font-semibold">{{ label }}:</span>
      <span v-for="recipient in wrappedRecipients">
        <mail-recipient :recipient="recipient" />
        <span
          v-if="!hasRecipients"
          v-text="'(' + $t('inbox.unknown_address') + ')'"
        ></span>
      </span>
    </p>
  </div>
</template>
<script>
import castArray from 'lodash/castArray'
import MailRecipient from '@/views/Emails/Recipient'
export default {
  components: { MailRecipient },
  props: {
    showWhenEmpty: {
      default: true,
      type: Boolean,
    },
    label: String,
    recipients: {},
  },
  computed: {
    wrappedRecipients() {
      return castArray(this.recipients)
    },
    hasRecipients() {
      return !this.recipients || this.wrappedRecipients.length > 0
    },
    showRecipients() {
      if (this.showWhenEmpty) {
        return true
      }

      if (!this.hasRecipients && this.showWhenEmpty === false) {
        return false
      }

      return true
    },
  },
}
</script>
