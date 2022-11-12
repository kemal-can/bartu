<template>
  <div :class="{ 'sync-stopped-by-system': account.is_sync_stopped }">
    <resource-table
      resource-name="emails"
      ref="table"
      :table-id="tableId"
      :row-class="rowClass"
      :action-request-query-string="actionRequestQueryString"
      :data-request-query-string="dataRequestQueryString"
    >
      <template #subject="{ row, formatted }">
        <message-subject :message="row" :account="account" />
      </template>
      <template #from="{ row, formatted }">
        <mail-recipient :recipient="row.from" v-if="row.from" />
        <p v-else v-text="'(' + $t('inbox.unknown_address') + ')'"></p>
      </template>
    </resource-table>
  </div>
</template>
<script>
import ResourceTable from '@/components/Table'
import MessageSubject from './InboxMessageSubject'
import MailRecipient from '@/views/Emails/Recipient'
export default {
  components: {
    ResourceTable,
    MessageSubject,
    MailRecipient,
  },
  props: {
    tableId: {
      required: true,
      type: String,
    },
    dataRequestQueryString: {
      type: Object,
      required: true,
    },
    actionRequestQueryString: {
      type: Object,
      required: true,
    },
    account: {
      type: Object,
      required: true,
    },
  },
  methods: {
    rowClass(row) {
      return !row.is_read ? 'unread' : 'read'
    },
  },
}
</script>
<style>
.read td {
  font-weight: normal !important;
}
.unread td {
  font-weight: bold !important;
}
</style>
