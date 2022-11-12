<template>
  <i-popover
    :busy="disabled"
    :title="$t('app.associate_with_record')"
    :placement="placement"
  >
    <a
      href="#"
      @click.prevent=""
      class="link text-sm"
      v-text="associationsText"
    />
    <template #popper>
      <div class="w-60">
        <i-form-group class="relative">
          <i-form-input
            @input="search"
            v-model="searchValue"
            class="pr-8"
            :placeholder="searchPlaceholder"
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
          <div v-for="data in records" :key="data.resource">
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
              :disabled="
                resourceName === data.resource &&
                primaryRecord &&
                Number(primaryRecord.id) === Number(record.id) &&
                !associated
              "
              @change="onChange(record, data.resource, data.is_search)"
              v-model:checked="selected[data.resource]"
            >
              {{ record.display_name }}
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
import orderBy from 'lodash/orderBy'
import sortBy from 'lodash/sortBy'
import find from 'lodash/find'
import map from 'lodash/map'
import uniq from 'lodash/uniq'
import isObject from 'lodash/isObject'
import { CancelToken } from '@/services/HTTP'
import cloneDeep from 'lodash/cloneDeep'
export default {
  emits: ['update:modelValue', 'change'],
  data() {
    return {
      // Markup template for re-usage for the result
      template: {
        contacts: {
          title: this.$t('contact.contacts'),
          resource: 'contacts',
        },
        companies: {
          title: this.$t('company.companies'),
          resource: 'companies',
        },
        deals: {
          title: this.$t('deal.deals'),
          resource: 'deals',
        },
      },
      minimumAsyncCharacters: 2,
      totalAsyncSearchCharacters: 0,
      minimumAsyncCharactersRequirement: false,
      limitSearchResults: 5,
      searchValue: '',
      cancelTokens: {},
      loading: false,
      // The selected associations
      selected: {},
      // Associations selected from search results
      selectedFromSearch: {},
      searchResults: {},
    }
  },
  props: {
    // The actual v-model for the selected associations
    modelValue: {},

    // Indicates whether the popover is disabled
    disabled: { type: Boolean, default: false },

    // The popover placement
    placement: { type: String, default: 'bottom' },

    // Passed only when associatable is needed and
    // will be taken from the resource store
    resourceName: String,

    // The associateable, the record from the passed resourceName, only provide when resourceName is provided
    // The current record which is associtable e.q. when viewing contact the contact is associateable
    associateable: {
      type: Object,
      default() {
        return {}
      },
    },

    // Used to fill the associations in the popover
    // Is passed on EDIT where the component needs to get
    // the associated associations to show the records
    // As the associateable may be linked to multiple not somehow related
    // resources via the search function, we need the associated record also for update
    // the associated e.q. the contact activity that will be used to show all records from the associations
    associated: Object,

    // Custom selected records from outside not somehow related to the associateables
    customSelectedRecords: Object,
  },

  watch: {
    // emit for changes
    selected: {
      deep: true,
      handler: function (newVal, oldVal) {
        this.$emit('update:modelValue', newVal)
      },
    },
    customSelectedRecords: {
      deep: true,
      handler: function (newVal, oldVal) {
        this.setSelectedRecords()
      },
    },
    // Update the selected values when the associated associations are changed
    'associated.associations': function (newVal, oldVal) {
      this.setSelectedRecords()
    },
  },
  computed: {
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
     * Hold the primary record
     *
     * @return {Null|Object}
     */
    primaryRecord() {
      if (!this.hasAssociateble) {
        return null
      }

      let result = null
      this.resources.every(resource => {
        // The current resource is not the actual primary resource
        if (resource != this.resourceName) {
          // Continue every
          return true
        }

        result = find(this.records[resource].records, [
          'id',
          Number(this.associateable.id),
        ])

        return result ? false : true
      })

      return result
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
     * Check whether there is associateble record
     *
     * @return {Boolean}
     */
    hasAssociateble() {
      return Object.keys(this.associateable).length > 0
    },

    /**
     * The associations text
     *
     * @return {String}
     */
    associationsText() {
      let totalSelected = 0
      this.resources.forEach(resource => {
        totalSelected += this.selected[resource]
          ? this.selected[resource].length
          : 0
      })

      if (totalSelected === 0) {
        return this.$t('app.no_associations')
      }

      return this.$t('app.associated_with_total_records', {
        total: totalSelected,
      })
    },

    /**
     * The available associations resources
     *
     * They are sorted as the primary associtable is always first
     *
     * @return {Array}
     */
    resources() {
      return sortBy(Object.keys(this.template), resourceName => {
        return [resourceName !== this.resourceName, resourceName]
      })
    },

    /**
     * The records to be displayed
     *
     * @return {Object}
     */
    records() {
      if (this.hasSearchResults) {
        return this.searchResults
      }

      let records = {}
      let addRecord = (resourceName, record) => {
        if (
          findIndex(records[resourceName].records, [
            'id',
            Number(record.id),
          ]) === -1
        ) {
          records[resourceName].records.push(record)
        }
      }

      this.resources.forEach(resource => {
        records[resource] = Object.assign({}, this.template[resource], {
          records: [],
        })

        if (this.hasAssociateble) {
          // Push the primary associateable
          if (resource === this.resourceName) {
            addRecord(resource, this.associateable)
          }

          this.getParsedAssociateablesFromInitialData(resource).forEach(
            record => addRecord(resource, record)
          )
        }

        // Push any associations which are not directly related to the intial associateable
        if (
          this.associated &&
          this.associated.hasOwnProperty('associations') &&
          this.associated.associations.hasOwnProperty(resource)
        ) {
          this.associated.associations[resource].forEach(record =>
            addRecord(resource, record)
          )
        }

        // Push any custom associations passed
        if (
          this.customSelectedRecords &&
          this.customSelectedRecords[resource]
        ) {
          this.customSelectedRecords[resource].forEach(record =>
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

      return records
    },
  },
  methods: {
    /**
     * Get the parsed associtables from the initial data
     * which are intended to be shown as records
     *
     * @param  {String} resource
     *
     * @return {Array}
     */
    getParsedAssociateablesFromInitialData(resource) {
      return orderBy(
        (this.associateable[resource] || []).slice(0, 3),
        'created_at',
        'desc'
      )
    },

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
    setSelectedRecords() {
      let selected = {}
      this.resources.forEach(resource => {
        let resourceSelected = []

        if (this.modelValue && this.modelValue[resource]) {
          resourceSelected = cloneDeep(
            isObject(this.modelValue[resource][0])
              ? map(this.modelValue[resource], record => record.id)
              : this.modelValue[resource]
          )
        }

        if (
          this.customSelectedRecords &&
          this.customSelectedRecords[resource]
        ) {
          resourceSelected = cloneDeep(
            isObject(this.customSelectedRecords[resource][0])
              ? map(this.customSelectedRecords[resource], record => record.id)
              : this.customSelectedRecords[resource]
          )
        }

        // Is primary resource, has associateable to be handled as
        // primary and is not create view because hasAssociated prop passsed
        if (
          resource === this.resourceName &&
          this.hasAssociateble &&
          !this.associated
        ) {
          resourceSelected.push(this.associateable.id)
        }

        // Set the selected value via the associated
        if (
          this.associated &&
          this.associated.hasOwnProperty('associations') &&
          this.associated.associations.hasOwnProperty(resource)
        ) {
          resourceSelected = resourceSelected.concat(
            this.associated.associations[resource].map(record => record.id)
          )
        }
        selected[resource] = uniq(resourceSelected)
      })
      this.selected = selected
    },
  },
  mounted() {
    this.setSelectedRecords()
  },
}
</script>
