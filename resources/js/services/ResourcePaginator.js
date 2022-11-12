/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import Paginator from './Paginator'
/**
 *   When to paginate:
 *
 *   watch: {
            'collection.currentPage': function(newVal, oldVal) {
                this.$emit('paginate', newVal)
            }
        }
 */
const DefaultCollection = {
  data: [],
  meta: {
    current_page: 1,
    from: 1,
    last_page: 1,
    per_page: 25,
    total: 0,
    to: 1,
  },
  per_page_options: [25, 50, 100],
}

class ResourcePaginator extends Paginator {
  /**
   * Class Constructor
   * @param state @type {{}|DefaultCollection}
   */
  constructor(state = {}) {
    super()
    this.state = Object.assign({}, DefaultCollection, state)
  }

  /**
   * (Method) Get attribute from the pagination
   * @param attribute @type {String}
   * @return {*}
   */
  getPaginationAttribute(attribute) {
    return this.state.meta[attribute]
  }

  /**
   * (Method) Set Field
   * @param attribute @type {String}
   * @param value @type {*}
   * @return {this}
   */
  setPaginationAttribute(attribute, value) {
    if (this.state.meta) {
      this.state.meta[attribute] = value
    }

    return this
  }

  /**
   * (Method) flush State
   * @return void
   */
  flush() {
    this.state = DefaultCollection
  }
}

export default ResourcePaginator
