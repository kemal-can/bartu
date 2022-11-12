<template>
  <router-link
    class="text-neutral-900 hover:text-neutral-600 dark:text-neutral-100 dark:hover:text-neutral-300"
    :to="{
      name: 'inbox-message',
      params: {
        account_id: account.id,
        folder_id: folderId,
        id: message.id,
      },
    }"
  >
    {{ message.subject ? message.subject : '(' + $t('inbox.no_subject') + ')' }}
  </router-link>
</template>
<script>
export default {
  props: ['message', 'account'],
  data: () => ({
    folderId: null,
  }),
  created() {
    this.folderId = this.$route.params.folder_id
  },
}
</script>
