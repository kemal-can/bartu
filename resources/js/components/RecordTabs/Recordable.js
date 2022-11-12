/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import InteractsWithResource from '@/mixins/InteractsWithResource'
import InfinityLoader from '@/components/InfinityLoader'
import { singularize } from '@/utils'
import findIndex from 'lodash/findIndex'

export default {
  mixins: [InteractsWithResource],
  components: { InfinityLoader },
  props: {
    scrollElement: {
      type: String,
    },
  },
  data: () => ({
    page: 1,
    search: null,
    searchResults: null,
    dataLoadedFirstTime: false,
    perPage: 15,
  }),
  computed: {
    /**
     * Indicates whether there are search results
     *
     * @return {Boolean}
     */
    hasSearchResults() {
      return this.searchResults && this.searchResults.length > 0
    },

    /**
     * Indicates whether the user is performing search
     *
     * @return {Boolean}
     */
    isPerformingSearch() {
      return this.search !== null
    },
  },
  methods: {
    /**
     * Perform search
     *
     * @param  {String|null} value
     * @param  {string} associateable
     *
     * @return {Void}
     */
    performSearch(value, associateable) {
      // Reset the state in case complete so the infinity
      // loading can be performed again
      this.$refs.infinity.state.reset()

      // Reset the page as for each search, the page must be
      // resetted to start from zero, additional pages results
      // are again handle by infinity loader when user scrolling to bottom
      // This also helps when user remove the search value so the infinity
      // loader can load the actual data from page 1 again
      this.page = 1

      if (!value) {
        this.loadData()
        this.search = null
        this.searchResults = null
        return
      }

      this.search = value
      this.loadData(true)
    },

    /**
     * Attempt to load data
     *
     * @param {Boolean} force
     *
     * @return {Void}
     */
    loadData(force = false) {
      this.$refs.infinity.attemptLoad(force)
    },

    /**
     * Handle the infinity load response
     *
     * @param  {Object} data
     * @param  {String} associateable
     *
     * @return {Void}
     */
    handleInfinityResult(data, associateable) {
      data.data.forEach(record => {
        let existsInStore =
          findIndex(this.resourceRecord[associateable], [
            'id',
            Number(record.id),
          ]) !== -1

        if (!existsInStore) {
          this.addResourceRecordHasManyRelationship(record, associateable)

          return
        }

        this.updateResourceRecordHasManyRelationship(record, associateable)
      })
    },

    /**
     * Make the request for data
     *
     * @param  {string} associateable
     * @param  {int} page
     * @param  {int|null} perPage
     *
     * @return {Promise}
     */
    makeRequestForData(associateable, page, perPage) {
      return Innoclapps.request().get(
        `${this.resourceRecord.path}/${associateable}`,
        {
          params: {
            page: page,
            q: this.search,
            timeline: 1,
            per_page: perPage || this.perPage,
          },
        }
      )
    },

    /**
     * Infinity load handler
     *
     * @param  {Object} $state
     * @param  {String} associateable
     *
     * @return {Void}
     */
    async infiniteHandler($state, associateable) {
      // We must check if the user has the permissions to view the record
      // in order to load the recorable resource
      // Can happen when user creates e.q. contact and assign this contact
      // to another user but the user who created the contact has only permissions
      // to view his own contacts, in this case, we will still show the contact profile
      // but there will be a message tha this user will be unable to view the contact
      if (this.$gate.denies('view', this.resourceRecord)) {
        $state.complete()
        return
      }

      let { data } = await this.makeRequestForData(associateable, this.page)

      if (data.data.length === 0) {
        if (this.isPerformingSearch) {
          // No search results and page is equal to 1?
          // In this case, just set the search results to empty
          if (this.page === 1) {
            this.searchResults = []
          }
        }

        $state.complete()
        this.dataLoadedFirstTime = true
        return
      }

      this.page += 1

      if (this.isPerformingSearch) {
        this.searchResults = !this.hasSearchResults
          ? data.data
          : this.searchResults.concat(...data.data)
      } else {
        this.handleInfinityResult(data, associateable)
        this.$nextTick(() => (this.dataLoadedFirstTime = true))
      }

      $state.loaded()
    },

    /**
     * Refresh the current recordable
     */
    refresh(associateable) {
      this.makeRequestForData(associateable, 1, this.perPage * this.page).then(
        ({ data }) => {
          if (data.data.length === 0) {
            this.$store.commit(
              this.resourceName + '/RESET_RECORD_HAS_MANY_RELATIONSHIP',
              associateable
            )
            return
          }

          this.handleInfinityResult(data, associateable)
        }
      )
    },
    /**
     * Retrieve the given associateble resource and scroll the container to the node
     *
     * @param  {String} associateable
     * @param  {Number} id
     *
     * @return {Void}
     */
    async focusToAssociateableElement(associateable, id) {
      // We will first retrieve the associatebale record and add to the resource record
      // relationship object, as it may be old record and the associatables record are paginated
      // in this case, if we query the document directly the record may no exists in the document
      let { data: record } = await Innoclapps.request().get(
        `/${associateable}/${id}`
      )

      this.addResourceRecordHasManyRelationship(record, associateable)

      this.$nextTick(() => {
        const sectionNode = document.getElementById('section-' + associateable)

        const recordNode = sectionNode.querySelector(
          `.${singularize(associateable)}-${record.id}`
        )

        const scrollNode = this.scrollElement
          ? document.querySelector(this.scrollElement)
          : window

        if (recordNode) {
          scrollNode.scrollTo({
            top: recordNode.getBoundingClientRect().top,
            behavior: 'smooth',
          })
        }
      })
    },
  },
}
