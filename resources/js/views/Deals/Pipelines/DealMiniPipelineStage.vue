<template>
  <li class="relative lg:flex lg:flex-1">
    <deal-mini-pipeline-stage-complete
      :request-in-progress="requestInProgress"
      @click="updateStage"
      v-i-tooltip="tooltipLabel"
      :stage="stage"
      :deal="deal"
      v-if="
        dealStageIsBeforeCurrentStage ||
        deal.status === 'lost' ||
        deal.status === 'won'
      "
    >
      <span class="truncate">{{ stage.name }}</span>
    </deal-mini-pipeline-stage-complete>

    <deal-mini-pipeline-stage-current
      :request-in-progress="requestInProgress"
      @click="updateStage"
      v-i-tooltip="tooltipLabel"
      v-else-if="dealStageIsCurrentStage"
    >
      <span class="truncate">{{ stage.name }}</span>
    </deal-mini-pipeline-stage-current>

    <deal-mini-pipeline-stage-future
      :request-in-progress="requestInProgress"
      @click="updateStage"
      v-i-tooltip="tooltipLabel"
      v-else
    >
      <span class="truncate">{{ stage.name }}</span>
    </deal-mini-pipeline-stage-future>

    <template v-if="index !== deal.pipeline.stages.length - 1">
      <!-- Arrow separator for lg screens and up -->
      <div
        class="absolute top-0 right-0 hidden h-full w-5 lg:block"
        aria-hidden="true"
      >
        <svg
          class="h-full w-full text-neutral-300 dark:text-neutral-600"
          viewBox="0 0 22 80"
          fill="none"
          preserveAspectRatio="none"
        >
          <path
            d="M0 -2L20 40L0 82"
            vector-effect="non-scaling-stroke"
            stroke="currentcolor"
            stroke-linejoin="round"
          />
        </svg>
      </div>
    </template>
  </li>
</template>
<script>
import findIndex from 'lodash/findIndex'
import DealMiniPipelineStageCurrent from './DealMiniPipelineStageCurrent'
import DealMiniPipelineStageComplete from './DealMiniPipelineStageComplete'
import DealMiniPipelineStageFuture from './DealMiniPipelineStageFuture'
export default {
  emits: ['stage-updated'],
  components: {
    DealMiniPipelineStageFuture,
    DealMiniPipelineStageComplete,
    DealMiniPipelineStageCurrent,
  },
  props: {
    index: {
      required: true,
      type: Number,
    },
    stage: {
      type: Object,
      required: true,
    },
    deal: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    requestInProgress: false,
  }),
  computed: {
    /**
     * Get the current stage index
     *
     * @return {Number}
     */
    currentStageIndex() {
      return findIndex(this.deal.pipeline.stages, ['id', this.stage.id])
    },

    /**
     * Get the deal stage index
     *
     * @return {Number}
     */
    dealStageIndex() {
      return findIndex(this.deal.pipeline.stages, ['id', this.deal.stage_id])
    },

    /**
     * Indicates whether the deal stage is before the current stage
     *
     * @return {Boolean}
     */
    dealStageIsBeforeCurrentStage() {
      return this.dealStageIndex > this.currentStageIndex
    },

    /**
     * Indicates whether the deal stage is the current stage
     *
     * @return {Void}
     */
    dealStageIsCurrentStage() {
      return this.dealStageIndex === this.currentStageIndex
    },

    /**
     * Indicates whether the deal has been in the current stage
     *
     * @return {Boolean}
     */
    beenInStage() {
      return this.deal.time_in_stages[this.stage.id] != undefined
    },

    /**
     * Get the moment instance duration
     *
     * @return {Moment}
     */
    duration() {
      return moment.duration({
        seconds: this.beenInStage ? this.deal.time_in_stages[this.stage.id] : 0,
      })
    },

    /**
     * Get the stage tooltip label
     *
     * @return {String}
     */
    tooltipLabel() {
      if (!this.beenInStage) {
        return this.$t('deal.hasnt_been_in_stage')
      }

      return this.$t('deal.been_in_stage_time', {
        time: this.duration.humanize(),
      })
    },
  },
  methods: {
    /**
     * Update the deal stage
     *
     * @return {Void}
     */
    updateStage() {
      if (this.stage.id === this.deal.stage_id) {
        return
      }

      this.requestInProgress = true
      Innoclapps.request()
        .put('/deals/' + this.deal.id, {
          stage_id: this.stage.id,
        })
        .then(({ data }) => {
          this.$emit('stage-updated', data)
          Innoclapps.$emit('deals-record-updated', data)
        })
        .finally(() => (this.requestInProgress = false))
    },
  },
}
</script>
