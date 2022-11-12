<template>
  <span class="inline-block" v-i-tooltip="tooltipContent">
    <a href="#" :class="linkClasses" @click.prevent="changeState">
      <i-spinner
        v-if="requestInProgress"
        class="h-4 w-4"
        :class="{
          'text-success-500 dark:text-success-400': !activity.is_completed,
          'text-neutral-500 dark:text-neutral-300': activity.is_completed,
        }"
      />
      <span
        v-else
        class="flex h-4 w-4 items-center justify-center rounded-full border"
      >
        <icon icon="Check" v-if="activity.is_completed"></icon>
      </span>
    </a>
  </span>
</template>
<script>
export default {
  emits: ['state-changed'],
  props: {
    activity: { required: true, type: Object },
  },
  data: () => ({
    requestInProgress: false,
  }),
  computed: {
    /**
     * The wrapper link classes
     *
     * @return {Array}
     */
    linkClasses() {
      let classes = ['inline-block mr-0.5']

      if (this.activity.is_completed) {
        classes.push(
          'text-success-500 hover:text-neutral-500 dark:text-success-400 dark:hover:text-neutral-300'
        )
      } else {
        classes.push(
          'text-neutral-500 hover:text-success-600 dark:text-neutral-300 dark:hover:text-success-400'
        )
      }

      if (!this.activity.authorizations.changeState || this.requestInProgress) {
        classes.push('pointer-events-none opacity-60')
      }

      return classes
    },

    /**
     * The tooltip content for the state change
     *
     * @return {String}
     */
    tooltipContent() {
      if (!this.activity.authorizations.changeState) {
        return this.$t('user.not_authorized')
      }

      if (this.activity.is_completed) {
        return this.$t('activity.mark_as_incomplete')
      }

      return this.$t('activity.mark_as_completed')
    },
  },
  methods: {
    /**
     * Mark the activity as complete
     */
    complete() {
      return Innoclapps.request().post(
        `activities/${this.activity.id}/complete`
      )
    },

    /**
     * Mark the activity as incomplete
     */
    incomplete() {
      return Innoclapps.request().post(
        `activities/${this.activity.id}/incomplete`
      )
    },

    /**
     * Change state
     */
    changeState() {
      this.requestInProgress = true
      ;(this.activity.is_completed ? this.incomplete() : this.complete())
        .then(({ data: activity }) => this.$emit('state-changed', activity))
        .finally(() => (this.requestInProgress = false))
    },
  },
}
</script>
