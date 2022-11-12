<template>
  <i-card :no-body="noBody" :overlay="isLoading">
    <div class="flex flex-col items-center text-lg md:flex-row">
      <div class="group truncate md:grow">
        <div class="flex items-center px-7 pt-4 md:pb-4">
          <div class="truncate">
            <i-card-heading class="truncate">{{ card.name }}</i-card-heading>
          </div>
          <div>
            <i-popover v-if="card.help" :triggers="['hover', 'focus']">
              <span class="mt-px ml-2 block md:hidden md:group-hover:block">
                <icon
                  icon="QuestionMarkCircle"
                  class="h-4 w-4 text-neutral-500 hover:text-neutral-700 dark:text-white dark:hover:text-neutral-300"
                />
              </span>
              <template #popper>
                <p
                  class="text-sm text-neutral-700 dark:text-neutral-200"
                  v-text="card.help"
                />
              </template>
            </i-popover>
          </div>
        </div>
      </div>
      <div class="flex shrink-0 space-x-2 py-2 px-3 sm:py-4 sm:px-7">
        <slot name="actions"></slot>
        <dropdown-select
          v-if="card.withUserSelection"
          :items="usersForSelection"
          label-key="name"
          value-key="id"
          placement="bottom-end"
          @change="fetch"
          v-model="user"
        />
        <dropdown-select
          v-model="selectedRange"
          v-if="hasRanges"
          placement="bottom-end"
          :items="card.ranges"
          @change="fetch"
        />
      </div>
    </div>
    <slot></slot>
  </i-card>
</template>
<script>
import find from 'lodash/find'
const qs = require('qs')
import { mapState } from 'vuex'
export default {
  emits: ['retrieved'],
  props: {
    card: Object,
    loading: Boolean,
    noBody: { type: Boolean, default: false },
    reloadOnQueryStringChange: { type: Boolean, default: true },
    requestQueryString: Object,
  },
  data() {
    return {
      cardIsLoading: false,
      user: null, // default all
      selectedRange:
        find(this.card.ranges, range => range.value === this.card.range) ||
        this.card.ranges[0],
    }
  },
  watch: {
    /**
     * Watch the requst query string for changes
     * When changed, refresh the card
     */
    requestQueryString: {
      handler: function () {
        if (this.reloadOnQueryStringChange === true) {
          this.fetch()
        }
      },
      deep: true,
    },
  },
  computed: {
    ...mapState({
      users: state => state.users.collection,
    }),

    /**
     * Get the users for the selection dropdown
     *
     * @return {Array}
     */
    usersForSelection() {
      return [
        {
          id: null,
          name: this.$t('app.all'),
        },
        ...this.users,
      ]
    },

    /**
     * Check whether loading is in progress
     *
     * @return {Boolean}
     */
    isLoading() {
      return this.loading || this.cardIsLoading
    },

    /**
     * Indicates whether the card has ranges
     *
     * @return {Boolean}
     */
    hasRanges() {
      return this.card.ranges.length > 0
    },
  },
  methods: {
    /**
     * Fetch the card
     *
     * @return {Void}
     */
    fetch() {
      this.cardIsLoading = true

      let queryString = {
        range: this.selectedRange.value,
        ...(this.requestQueryString || {}),
      }

      if (this.card.withUserSelection) {
        queryString.user_id = this.user ? this.user.id : null
      }

      Innoclapps.request()
        .get(`/cards/${this.card.uriKey}?${qs.stringify(queryString)}`)
        .then(({ data: card }) =>
          this.$emit('retrieved', {
            card: card,
            requestQueryString: queryString,
          })
        )
        .finally(() => (this.cardIsLoading = false))
    },

    /**
     * Handle range change
     *
     * @param  {Object} range
     *
     * @return {Void}
     */
    handleRangeSelected(range) {
      this.fetch()
    },
  },
  created() {
    if (
      this.card.withUserSelection !== false &&
      typeof this.card.withUserSelection === 'number'
    ) {
      this.user = find(this.users, ['id', this.card.withUserSelection])
    }

    if (this.card.refreshOnActionExecuted) {
      Innoclapps.$on('action-executed', this.fetch)
    }

    Innoclapps.$on('refresh-cards', this.fetch)
  },
  unmounted() {
    Innoclapps.$off('action-executed', this.fetch)
    Innoclapps.$off('refresh-cards', this.fetch)
  },
}
</script>
