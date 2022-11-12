<template>
  <i-layout>
    <div class="mx-auto max-w-5xl">
      <i-card :header="$t('app.oauth.connected_accounts')" :overlay="loading">
        <div v-if="hasAccounts" class="space-y-3">
          <account-row
            :account="account"
            v-for="account in accounts"
            :key="account.id"
          >
            <i-button size="sm" @click="reAuthenticate(account)">{{
              $t('app.oauth.re_authenticate')
            }}</i-button>
            <i-button
              size="sm"
              class="ml-1"
              v-if="account.authorizations.delete"
              variant="white"
              @click="destroy(account.id)"
            >
              <icon icon="Trash" class="h-4 w-4" />
            </i-button>
          </account-row>
        </div>
        <div v-else v-show="!loading" class="text-center">
          <icon icon="EmojiSad" class="mx-auto h-12 w-12 text-neutral-400" />
          <h3
            class="mt-2 text-sm font-medium text-neutral-800 dark:text-white"
            v-t="'app.oauth.no_accounts'"
          ></h3>
        </div>
      </i-card>
    </div>
    <router-view></router-view>
  </i-layout>
</template>
<script>
import findIndex from 'lodash/findIndex'
import AccountRow from './AccountRow'
export default {
  components: { AccountRow },
  data: () => ({
    loading: false,
    accounts: [],
  }),
  computed: {
    hasAccounts() {
      return this.accounts.length > 0
    },
  },
  methods: {
    /**
     * Re-authenticate account
     *
     * @param  {Object} account
     *
     * @return {Void}
     */
    reAuthenticate(account) {
      window.location.href = `${Innoclapps.config.url}/${account.type}/connect`
    },

    /**
     * Remove account from storage
     *
     * @param  {Number} account
     *
     * @return {Void}
     */
    destroy(id) {
      this.$dialog
        .confirm(this.$t('app.oauth.delete_warning'), {
          okText: this.$t('app.confirm'),
        })
        .then(() => {
          Innoclapps.request()
            .delete('oauth/accounts/' + id)
            .then(({ data }) => {
              this.accounts.splice(
                findIndex(this.accounts, ['id', Number(id)]),
                1
              )
              this.$store.commit('emailAccounts/RESET')
              Innoclapps.success(this.$t('app.oauth.deleted'))
            })
        })
    },

    /**
     * Fetch the user OAuth accounts
     *
     * @return {Void}
     */
    fetch() {
      this.loading = true
      Innoclapps.request()
        .get('oauth/accounts')
        .then(({ data }) => {
          this.accounts = data

          if (this.$route.query.reconnect) {
            this.reAuthenticate(
              data.find(account => account.id == this.$route.query.reconnect)
            )
          }
        })
        .finally(() => (this.loading = false))
    },
  },
  created() {
    this.fetch()
  },
}
</script>
