<template>
  <record-tab
    :badge="resourceRecord.unread_emails_for_user_count"
    badge-variant="info"
    @activated-first-time="loadData"
    :section-id="associateable"
    :title="$t('mail.emails')"
    :classes="{ 'opacity-70': !hasEmails }"
    icon="Mail"
  >
    <div
      class="mb-4 block"
      v-if="
        (!hasAccountsConfigured ||
          !hasEmails ||
          (hasEmails && !hasAccountsConfigured)) &&
        dataLoadedFirstTime
      "
    >
      <div
        class="rounded-md border border-neutral-200 bg-neutral-50 px-6 py-5 shadow-sm dark:border-neutral-900 dark:bg-neutral-900 sm:flex sm:items-start sm:justify-between"
      >
        <div class="sm:flex sm:items-center">
          <span
            class="hidden rounded border border-neutral-200 bg-neutral-100 px-3 py-1.5 dark:border-neutral-600 dark:bg-neutral-700/60 sm:inline-flex sm:self-start"
          >
            <icon
              icon="Mail"
              class="h-5 w-5 text-neutral-700 dark:text-neutral-200"
            />
          </span>

          <div class="sm:ml-4">
            <div
              class="text-sm font-medium text-neutral-900 dark:text-neutral-100"
              v-t="'mail.info'"
            />
            <div class="text-sm sm:flex sm:items-center">
              <router-link
                :to="{ name: 'email-accounts-index' }"
                class="link"
                v-t="'mail.account.connect'"
              />
            </div>
          </div>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-6 sm:flex-shrink-0">
          <div
            v-i-tooltip="
              !hasAccountsConfigured
                ? $t('mail.account.integration_not_configured')
                : null
            "
          >
            <i-button
              @click="compose(true)"
              size="sm"
              icon="Plus"
              :disabled="!hasAccountsConfigured"
            >
              {{ $t('mail.create') }}
            </i-button>
          </div>
        </div>
      </div>
    </div>

    <div v-else-if="hasEmails && dataLoadedFirstTime" class="mb-8 text-right">
      <i-button @click="compose(true)" size="sm" icon="Plus">
        {{ $t('mail.create') }}
      </i-button>
    </div>

    <compose
      :visible="isComposing"
      :resource-name="resourceName"
      :resource-record="resourceRecord"
      :to="to"
      ref="compose"
      @modal-hidden="compose(false)"
    />
    <div class="my-3">
      <input-search
        v-model="search"
        v-show="hasEmails || search"
        @input="performSearch($event, associateable)"
      />
    </div>

    <card-placeholder v-if="!dataLoadedFirstTime && !hasEmails" pulse />

    <emails :emails="emails" :resource-name="resourceName" />

    <div
      class="mt-6 text-center text-neutral-800 dark:text-neutral-200"
      v-show="isPerformingSearch && !hasSearchResults"
      v-t="'app.no_search_results'"
    />

    <infinity-loader
      @handle="infiniteHandler($event, associateable)"
      :scroll-element="scrollElement"
      ref="infinity"
    />
  </record-tab>
</template>
<script>
import Recordable from '@/components/RecordTabs/Recordable'
import RecordTab from '@/components/RecordTabs/RecordTab'
import Compose from '@/views/Emails/Compose'
import Emails from './Index'
import orderBy from 'lodash/orderBy'
import { mapGetters } from 'vuex'
import CardPlaceholder from '@/components/Loaders/CardPlaceholder'

export default {
  mixins: [Recordable],
  components: {
    RecordTab,
    Emails,
    Compose,
    CardPlaceholder,
  },
  data: () => ({
    isComposing: false,
    associateable: 'emails',
  }),
  computed: {
    ...mapGetters({
      accounts: 'emailAccounts/accounts',
      hasAccountsConfigured: 'emailAccounts/hasConfigured',
    }),
    /**
     * Provides the record tab emails
     *
     * @return {Array}
     */
    emails() {
      return orderBy(
        this.searchResults || this.resourceRecord.emails,
        'date',
        'desc'
      )
    },

    /**
     * Check whether the record has emails
     *
     * @return {Boolean}
     */
    hasEmails() {
      return this.emails.length > 0
    },

    /**
     * Get the TO addresses
     *
     * @return {Array}
     */
    to() {
      // First check if there is an email property in the resource record data
      if (this.resourceRecord.email) {
        return this.createToArrayFromRecord(
          this.resourceRecord,
          this.resourceName
        )
      }

      // Vue 3, before navigating, it's hitting this computed but there is no data
      // TODO, research more
      if (Object.keys(this.resourceRecord).length === 0) {
        return []
      }

      // Next we will try to provide associations and email to send email from
      // the related resources, e.q. when viewing contact and the contact has no email
      // we will try to provide an email from the contact latest company and so on.
      if (this.resourceName === 'contacts') {
        if (this.resourceRecord.companies[0]) {
          return this.createToArrayFromRecord(
            this.resourceRecord.companies[0],
            'companies'
          )
        }
      } else if (this.resourceName === 'companies') {
        if (this.resourceRecord.contacts[0]) {
          return this.createToArrayFromRecord(
            this.resourceRecord.contacts[0],
            'contacts'
          )
        }
      } else if (this.resourceName === 'deals') {
        if (this.resourceRecord.contacts[0]) {
          return this.createToArrayFromRecord(
            this.resourceRecord.contacts[0],
            'contacts'
          )
        } else if (this.resourceRecord.companies[0]) {
          return this.createToArrayFromRecord(
            this.resourceRecord.companies[0],
            'companies'
          )
        }
      }

      return []
    },
  },
  methods: {
    /**
     * Create to array from record
     *
     * @param  {Object} record
     *
     * @return {Array}
     */
    createToArrayFromRecord(record, resourceName) {
      return record.email
        ? [
            {
              address: record.email,
              name: record.display_name,
              resourceName: resourceName,
              id: record.id,
            },
          ]
        : []
    },

    /**
     * Compose new email
     *
     * @param  {Boolean} state
     *
     * @return {Void}
     */
    compose(state = true) {
      this.isComposing = state
      if (state) {
        this.$nextTick(() => this.$refs.compose.$refs.subject.focus())
      }
    },

    /**
     * Handle email sent
     *
     * @param  {Object} email
     *
     * @return {Void}
     */
    handleSent(email) {
      this.$store.commit(
        this.resourceName + '/ADD_RECORD_HAS_MANY_RELATIONSHIP',
        {
          relation: this.associateable,
          item: email,
        }
      )
    },
  },

  /**
   * Component created
   *
   * @return {Void}
   */
  created() {
    Innoclapps.$on('email-accounts-sync-finished', this.refresh)
    Innoclapps.$on('email-sent', this.handleSent)
  },

  /**
   * Mounted event lifecycle
   *
   * @return {Void}
   */
  mounted() {
    if (
      this.$route.query.resourceId &&
      this.$route.query.section === this.associateable
    ) {
      // Wait till the data is loaded for the first time and the
      // elements are added to the document so we can have a proper scroll
      const unwatcher = this.$watch('dataLoadedFirstTime', () => {
        this.focusToAssociateableElement(
          this.associateable,
          this.$route.query.resourceId
        )
        unwatcher()
      })
    }
  },

  /**
   * Before destroy
   *
   * @return {Void}
   */
  unmounted() {
    Innoclapps.$off('email-accounts-sync-finished', this.refresh)
    Innoclapps.$off('email-sent', this.handleSent)
  },
}
</script>
