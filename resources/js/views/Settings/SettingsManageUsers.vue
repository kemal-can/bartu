<template>
  <i-tabs v-model="tabId">
    <i-tab
      tab-id="users"
      :title="$t('user.users')"
      icon="User"
      @activated="handleTabActivated"
    >
      <users-index />
    </i-tab>
    <i-tab
      tab-id="roles"
      :title="$t('role.roles')"
      icon="ShieldExclamation"
      @activated="handleTabActivated"
    >
      <router-view name="roles" />
    </i-tab>
    <i-tab
      tab-id="teams"
      :title="$t('team.teams')"
      icon="UserGroup"
      @activated="handleTabActivated"
    >
      <router-view name="teams" />
    </i-tab>
  </i-tabs>
</template>
<script>
import UsersIndex from '@/views/Users/Index'
export default {
  components: {
    UsersIndex,
  },
  data: () => ({
    tabId: 'users',
  }),
  methods: {
    handleTabActivated(tabId) {
      // Direct access support
      if (
        tabId === 'users' &&
        !['create-user', 'edit-user', 'invite-user'].includes(this.$route.name)
      ) {
        this.$router.push({ name: 'users-index' })
      } else if (
        tabId === 'roles' &&
        !['create-role', 'edit-role'].includes(this.$route.name)
      ) {
        this.$router.push({ name: 'role-index' })
      } else if (tabId === 'teams') {
        this.$router.push({ name: 'manage-teams' })
      }
    },
  },
  beforeRouteUpdate(to, from, next) {
    // When clicking directly on the settings menu Users item
    if (to.name === 'users-index') {
      this.tabId = 'users'
    }

    next()
  },
  created() {
    // Direct access support
    if (['role-index', 'create-role', 'edit-role'].includes(this.$route.name)) {
      this.tabId = 'roles'
    } else if (this.$route.name === 'manage-teams') {
      this.tabId = 'teams'
    }
  },
}
</script>
