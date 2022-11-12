<template>
  <i-popover
    :busy="disabled"
    :title="$t('activity.guests')"
    :placement="placement"
  >
    <a href="#" @click.prevent="" class="link text-sm">{{ totalGuestsText }}</a>
    <template #popper>
      <div class="w-60">
        <i-form-group class="relative">
          <i-form-input
            @input="search"
            v-model="searchValue"
            :placeholder="searchPlaceholder"
            class="pr-8"
          />
          <a
            href="#"
            @click.prevent="cancelSearch"
            v-show="searchValue"
            class="absolute right-3 top-2.5"
          >
            <icon icon="X" class="h-5 w-5 text-neutral-400" />
          </a>
        </i-form-group>
        <i-overlay :show="loading">
          <p
            class="text-center text-sm text-neutral-600 dark:text-neutral-300"
            v-show="
              isSearch &&
              !hasSearchResults &&
              !loading &&
              !minimumAsyncCharactersRequirement
            "
            v-t="'app.no_search_results'"
          ></p>
          <p
            class="text-center text-sm text-neutral-600 dark:text-neutral-300"
            v-show="isSearch && minimumAsyncCharactersRequirement"
            v-text="
              $t('app.type_more_to_search', {
                characters: totalCharactersLeftToPerformSearch,
              })
            "
          ></p>
          <div v-for="data in guestables" :key="data.resource">
            <p
              class="mt-3 mb-2 text-sm font-medium text-neutral-700 dark:text-neutral-100"
              v-show="data.records.length > 0"
              v-text="data.title"
            />
            <i-form-checkbox
              v-for="record in data.records"
              :id="data.resource + '-' + record.id"
              :key="data.resource + '-' + record.id"
              :value="record.id"
              @change="onChange(record, data.resource, data.is_search)"
              v-model:checked="selected[data.resource]"
            >
              {{ record.guest_display_name }}
              {{ record.guest_email ? `(${record.guest_email})` : '' }}
            </i-form-checkbox>
          </div>
        </i-overlay>
      </div>
    </template>
  </i-popover>
</template>
<script>
import findIndex from 'lodash/findIndex'
import debounce from 'lodash/debounce'
import map from 'lodash/map'
import filter from 'lodash/filter'
import uniq from 'lodash/uniq'
import isObject from 'lodash/isObject'
import sortBy from 'lodash/sortBy'
import { CancelToken } from '@/services/HTTP'
export default {
  emits: ['update:modelValue', 'change'],
  props: {
    /**
     * The actual v-model for the selected guests
     *
     * @type {Object}
     */
    modelValue: {},

    /**
     * Available contacts for selection
     *
     * @type {Object}
     */
    contacts: {},

    // All guests of the record, use only on EDIT
    // We need all the guests in case there are guests not directly associated with the resource
    guests: {},

    /**
     * Indicates whether the popover is disabled
     *
     * @type {Boolean}
     */
    disabled: {
      type: Boolean,
      default: false,
    },

    /**
     * The popover placement
     *
     * @type {String}
     */
    placement: {
      type: String,
      default: 'bottom',
    },
  },

  data() {
    return {
      minimumAsyncCharacters: 2,
      totalAsyncSearchCharacters: 0,
      minimumAsyncCharactersRequirement: false,
      limitSearchResults: 5,
      searchValue: '',
      cancelTokens: {},
      loading: false,
      // The selected guests
      selected: {},
      // Guests selected from search results
      selectedFromSearch: {},
      searchResults: {},
      template: {
        contacts: {
          title: this.$t('contact.contacts'),
          resource: 'contacts',
        },
        users: {
          title: this.$t('user.users'),
          resource: 'users',
        },
      },
    }
  },
  watch: {
    // emit for changes
    selected: {
      deep: true,
      handler: function (newVal, oldVal) {
        this.$emit('update:modelValue', newVal)
      },
    },
    guests: {
      deep: true,
      handler: function (newVal, oldVal) {
        this.setSelectedGuests()
      },
    },
  },
  computed: {
    /**
     * Get the available users
     *
     * @return {Array}
     */
    users() {
      return this.$store.state.users.collection
    },

    /**
     * The available guestable resources
     *
     * @return {Array}
     */
    resources() {
      return Object.keys(this.template)
    },

    /**
     * Indicates the total characters left so the request can
     * be performed
     *
     * @return {Number}
     */
    totalCharactersLeftToPerformSearch() {
      return this.minimumAsyncCharacters - this.totalAsyncSearchCharacters
    },

    /**
     * The search input placeholder
     *
     * @return {String}
     */
    searchPlaceholder() {
      return this.$t('app.search_records')
    },

    /**
     * Check whether there are search results
     *
     * @return {Boolean}
     */
    hasSearchResults() {
      let result = false
      this.resources.every(resource => {
        result = this.searchResults[resource]
          ? this.searchResults[resource].records.length > 0
          : false
        return result ? false : true
      })

      return result
    },

    /**
     * Checks whether the view is search
     *
     * @return {Boolean}
     */
    isSearch() {
      return this.searchValue != ''
    },

    /**
     * The guests text
     *
     * @return {String}
     */
    totalGuestsText() {
      let totalSelected = 0
      this.resources.forEach(resource => {
        totalSelected += this.selected[resource]
          ? this.selected[resource].length
          : 0
      })

      return this.$tc('activity.count_guests', totalSelected)
    },

    /**
     * The guestables to be display in the popover
     *
     * @return {Object}
     */
    guestables() {
      if (this.hasSearchResults) {
        return this.searchResults
      }

      let guestables = {}

      let addRecord = (resourceName, record) => {
        if (
          findIndex(guestables[resourceName].records, [
            'id',
            Number(record.id),
          ]) === -1
        ) {
          guestables[resourceName].records.push(record)
        }
      }

      this.resources.forEach(resource => {
        guestables[resource] = Object.assign({}, this.template[resource], {
          records: [],
        })

        if (this.guests) {
          filter(this.guests, ['resource_name', resource]).forEach(record =>
            addRecord(resource, record)
          )
        }

        // Check for any selected from search
        if (this.selectedFromSearch[resource]) {
          this.selectedFromSearch[resource].records.forEach(record =>
            addRecord(resource, record)
          )
        }
      })

      this.users.forEach(user => addRecord('users', user))
      this.contacts.forEach(contact => addRecord('contacts', contact))

      return map(guestables, data => {
        data.records = sortBy(data.records, 'guest_display_name')

        return data
      })
    },
  },
  methods: {
    /**
     * Create search requests for the Promise
     *
     * @param  {String} q
     *
     * @return {Array}
     */
    createResolveableRequests(q) {
      // The order of the promises must be the same
      // like in the order of the template keys data property
      // Create promises array
      let promises = []
      this.resources.forEach(resource => {
        promises.push(
          Innoclapps.request().get(`/${resource}/search`, {
            params: {
              q: q,
              take: this.limitSearchResults,
            },
            cancelToken: new CancelToken(
              token => (this.cancelTokens[resource] = token)
            ),
          })
        )
      })

      return promises
    },

    /**
     * Cancel any previous requests via the cancel token
     *
     * @return {Void}
     */
    cancelPreviousRequests() {
      Object.keys(this.cancelTokens).forEach(resource => {
        if (this.cancelTokens[resource]) {
          this.cancelTokens[resource]()
        }
      })
    },

    /**
     * On checkbox change
     *
     * @param  {Object} record
     * @param  {String} resource
     * @param  {Boolean} fromSearch
     *
     * @return {Void}
     */
    onChange(record, resource, fromSearch) {
      if (!this.selectedFromSearch[resource] && fromSearch) {
        this.selectedFromSearch[resource] = {
          records: [],
          is_search: fromSearch,
        }
      }

      this.$nextTick(() => {
        // User checked record selected from search
        if (this.selected[resource].includes(record.id) && fromSearch) {
          this.selectedFromSearch[resource].records.push(record)
        } else if (this.selectedFromSearch[resource]) {
          // Unchecked, now remove it it from the selectedFromSearch
          let selectedIndex = findIndex(
            this.selectedFromSearch[resource].records,
            ['id', Number(record.id)]
          )
          if (selectedIndex != -1) {
            this.selectedFromSearch[resource].records.splice(selectedIndex, 1)
          }
        }

        this.$emit('change', this.selected)
      })
    },

    /**
     * Cancel the search view
     *
     * @return {Void}
     */
    cancelSearch() {
      this.searchValue = ''
      this.search('')
    },

    /**
     * Search records ASYNC
     *
     * @param  {Array}  q
     *
     * @return {Void}
     */
    search: debounce(function (q) {
      const totalCharacters = q.length

      if (totalCharacters === 0) {
        this.searchResults = {}
        return
      }

      this.totalAsyncSearchCharacters = totalCharacters

      if (totalCharacters < this.minimumAsyncCharacters) {
        this.minimumAsyncCharactersRequirement = true

        return q
      }

      this.minimumAsyncCharactersRequirement = false
      this.cancelPreviousRequests()
      this.loading = true

      Promise.all(this.createResolveableRequests(q)).then(values => {
        this.resources.forEach((resource, key) => {
          this.searchResults[resource] = Object.assign(
            {},
            this.template[resource],
            {
              records: map(values[key].data, record => {
                record.from_search = true
                return record
              }),
              is_search: true,
            }
          )
        })

        this.loading = false
      })
    }, 650),

    /**
     * Reset selected
     *
     * @return {Void}
     */
    setSelectedGuests() {
      let selected = {}
      this.resources.forEach(resource => {
        let resourceSelected = []
        if (this.modelValue && this.modelValue[resource]) {
          resourceSelected = isObject(this.modelValue[resource][0])
            ? map(this.modelValue[resource], record => record.id)
            : this.modelValue[resource]
        }

        // Set the selected value via the guests
        if (this.guests) {
          resourceSelected = resourceSelected.concat(
            filter(this.guests, ['resource_name', resource]).map(
              record => record.id
            )
          )
        }
        selected[resource] = uniq(resourceSelected)
      })

      this.selected = selected
    },
  },
  mounted() {
    this.$nextTick(() => this.setSelectedGuests())
  },
}
</script>
