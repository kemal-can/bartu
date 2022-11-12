<template>
  <i-vertical-navigation-item
    :active-class="activeClass"
    :to="folderRoute"
    fixed
  >
    <template #title>
      <div class="flex w-full">
        <div class="grow">{{ folder.display_name }}</div>
        <i-badge size="circle" v-if="folder.unread_count">{{
          folder.unread_count
        }}</i-badge>
      </div>
    </template>
    <template v-if="hasChildren">
      <inbox-folders-menu-item
        v-for="child in folder.children"
        :folder="child"
        :key="child.id"
      />
    </template>
  </i-vertical-navigation-item>
</template>
<script>
export default {
  name: 'inbox-folders-menu-item',
  props: {
    folder: { required: true, type: Object },
  },
  computed: {
    /**
     * Get the folder route
     *
     * @return {Object}
     */
    folderRoute() {
      // When the user first access the INBOX menu without any params
      // the account may be undefined till the inbox.vue redirects to the
      // messages using the default account
      // in this case, while all these actions are executed just return null
      // because it's throwing warning missing account params for name route 'inbox-messages'
      if (!this.$route.params.account_id) {
        return null
      }

      return {
        name: 'inbox-messages',
        params: {
          account_id: this.$route.params.account_id,
          folder_id: this.folder.id,
        },
      }
    },

    /**
     * Check whether the folder has children
     *
     * @return {Boolean}
     */
    hasChildren() {
      return this.folder.children && this.folder.children.length > 0
    },

    /**
     * Get the active menu class
     * @param  {Object} folder
     * @return {String}
     */
    activeClass() {
      return this.folder.id == this.$route.params.folder_id &&
        this.folder.email_account_id == this.$route.params.account_id
        ? 'active'
        : ''
    },
  },
}
</script>
