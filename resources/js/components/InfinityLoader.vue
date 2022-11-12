<template>
  <div>
    <slot />
    <div class="mt-3 flex justify-center">
      <i-spinner
        class="h-4 w-4 text-primary-500"
        v-show="isLoading"
      ></i-spinner>
    </div>
  </div>
</template>
<script>
import debounce from 'lodash/debounce'
import { randomString, isVisible, passiveEventArg } from '@/utils'
const STATUS = {
  COMPLETE: 'complete',
  LOADING: 'loading',
  READY: 'ready',
}

export default {
  emits: [
    'handle',
    '$InfinityLoader:loaded',
    '$InfinityLoader:complete',
    '$InfinityLoader:reset',
  ],
  props: {
    scrollElement: String,
    debounce: {
      type: Number,
      default: 200,
    },
    offset: {
      type: Number,
      default: 0,
    },
    loadWhenMounted: {
      type: Boolean,
      default: false,
    },
    identifier: {
      default() {
        return randomString(5)
      },
    },
  },
  data() {
    return {
      handleOnScroll: undefined,
      scrollNode: null,
      status: STATUS.READY,
      state: null,
    }
  },
  computed: {
    /**
     * Indicates whether loading is performed
     *
     * @return {Boolean}
     */
    isLoading() {
      return this.status === STATUS.LOADING
    },
  },

  methods: {
    /**
     * Handle scroll
     *
     * @param  {Object} e
     *
     * @return {Void}
     */
    _handleOnScroll(e) {
      if (this.status === STATUS.READY && isVisible(this.$el)) {
        let scrollNode = e.target

        if (e.target === document) {
          scrollNode = scrollNode.scrollingElement || scrollNode.documentElement
        }

        if (
          scrollNode.scrollHeight -
            this.offset -
            scrollNode.scrollTop -
            scrollNode.clientHeight <
          1
        ) {
          this.attemptLoad()
        }
      }
    },

    /**
     * Attempt to load data
     *
     * @param {Boolean} force
     *
     * @return {Void}
     */
    attemptLoad(force = false) {
      if (this.status === STATUS.READY || force === true) {
        this.status = STATUS.LOADING
        this.$emit('handle', this.state)
      }
    },

    /**
     * Remove binded events
     *
     * @return {Void}
     */
    removeEvents() {
      this.scrollNode.removeEventListener(
        'scroll',
        this.handleOnScroll,
        passiveEventArg()
      )
    },

    /**
     * Bind the events
     */
    bindEvents() {
      this.scrollNode.addEventListener(
        'scroll',
        this.handleOnScroll,
        passiveEventArg()
      )
    },
  },
  /**
   * Handle the component created lifecycle
   *
   * @return {Void}
   */
  created() {
    if (this.debounce) {
      this.handleOnScroll = debounce(
        this._handleOnScroll.bind(this),
        this.debounce,
        {
          trailing: true,
        }
      )
    } else {
      this.handleOnScroll = this._handleOnScroll.bind(this)
    }
  },
  /**
   * Handle the component mounted lifecycle
   *
   * @return {Void}
   */
  mounted() {
    this.state = {
      loaded: () => {
        this.$emit('$InfinityLoader:loaded', {
          target: this,
        })
        this.status = STATUS.READY
      },
      complete: () => {
        this.$emit('$InfinityLoader:complete', {
          target: this,
        })
        this.status = STATUS.COMPLETE
        this.removeEvents()
      },
      reset: () => {
        this.$emit('$InfinityLoader:reset', {
          target: this,
        })
        this.status = STATUS.READY
        this.bindEvents()
      },
    }

    // Wait till scroll element rendered
    this.$nextTick(() => {
      this.scrollNode = this.scrollElement
        ? document.querySelector(this.scrollElement)
        : window

      if (this.loadWhenMounted) {
        this.attemptLoad()
      }

      this.bindEvents()
    })
  },
  /**
   * Handle the component destroyed lifecycle
   *
   * @return {Void}
   */
  unmounted() {
    this.removeEvents()
  },
}
</script>
