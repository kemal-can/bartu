<template>
  <div class="inline-block">
    <compose v-if="isComposing" :visible="isComposing" ref="compose" :to="to" />
    <i-dropdown no-caret @show="handleDropdownShownEvent" ref="dropdown">
      <template #toggle>
        <a
          class="link"
          @click.prevent=""
          :href="'mailto:' + row[column.attribute]"
          v-text="row[column.attribute]"
        />
      </template>
      <i-dropdown-item
        href="#"
        :disabled="!hasAccountsConfigured"
        v-i-tooltip="
          hasAccountsConfigured
            ? ''
            : $t('mail.account.integration_not_configured')
        "
        @click="compose(true)"
        :text="$t('mail.create')"
      />
      <copy-button
        :success-message="$t('fields.email_copied')"
        icon=""
        :clipboard-options="{
          text: function (trigger) {
            return row[column.attribute]
          },
        }"
        :with-tooltip="false"
        tag="i-dropdown-item"
      >
        {{ $t('app.copy') }}
      </copy-button>
      <i-dropdown-item
        :href="'mailto:' + row[column.attribute]"
        :text="$t('app.open_in_app')"
      />
    </i-dropdown>
  </div>
</template>
<script>
import TableData from './TableData'
import Compose from '@/views/Emails/Compose'
import { mapGetters } from 'vuex'
export default {
  mixins: [TableData],
  components: { Compose },
  data: () => ({
    isComposing: false,
  }),
  computed: {
    ...mapGetters({
      accounts: 'emailAccounts/accounts',
      hasAccountsConfigured: 'emailAccounts/hasConfigured',
    }),

    /**
     * Get the predefined TO property
     *
     * @return {Array}
     */
    to() {
      return [
        {
          address: this.row[this.column.attribute],
          name: this.row.display_name,
          resourceName: this.resourceName,
          id: this.row.id,
        },
      ]
    },
  },
  methods: {
    /**
     * Compose new email
     *
     * @param  {Boolean} state
     *
     * @return {Void}
     */
    compose(state = true) {
      this.isComposing = state
      this.$refs.dropdown.hide()
      this.$nextTick(() => this.$refs.compose.$refs.subject.focus())
    },

    /**
     * Handle the dropdown show event
     *
     * @return {Void}
     */
    handleDropdownShownEvent() {
      // Load the placeholders when the first time dropdown
      // is shown, helps when first time is clicked on the dropdown -> Create Email the
      // placeholders are not loaded as the editor is initialized before the request is finished

      this.$store.dispatch('fields/fetchPlaceholders')

      // We will check if the accounts are not fetched, if not
      // we will dispatch the store fetch function to retrieve the
      // accounts before the dropdown is shown so the Compose.vue won't need to
      // As if we use the Compose.vue, every row in the table will retireve the accounts
      // and there will be 20+ requests when the table loads
      if (!this.$store.state.emailAccounts.dataFetched) {
        this.$store.dispatch('emailAccounts/fetch')
      }
    },
  },
}
</script>
