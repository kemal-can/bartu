<template>
  <div class="flex items-center">
    <div v-if="deal.authorizations.update" class="relative z-20">
      <i-popover
        :auto-hide="false"
        ref="popover"
        @show="stagePopoverShowEvent"
        v-if="deal.status !== 'lost'"
      >
        <a
          href="#"
          @click.prevent=""
          class="flex flex-wrap items-center justify-center md:flex-nowrap md:justify-start"
        >
          <span>{{ deal.pipeline.name }}</span>
          <icon icon="ChevronRight" class="h-4 w-4" /> {{ deal.stage.name }}
          <icon icon="ChevronDown" class="ml-1.5 hidden h-4 w-4 md:block" />
        </a>

        <template #popper>
          <div class="w-72 max-w-full p-2">
            <i-custom-select
              :options="pipelines"
              v-model="selectPipeline"
              @option:selected="handlePipelineChangedEvent"
              :clearable="false"
              class="mb-2"
              label="name"
            />
            <i-custom-select
              :options="selectPipeline ? selectPipeline.stages : []"
              :clearable="false"
              v-model="selectPipelineStage"
              label="name"
            />
            <div
              class="-mx-6 -mb-5 mt-4 flex justify-end space-x-1 bg-neutral-100 px-6 py-3 dark:bg-neutral-900"
            >
              <i-button
                size="sm"
                variant="white"
                :disabled="requestInProgress"
                @click="() => $refs.popover.hide()"
                >{{ $t('app.cancel') }}</i-button
              >
              <i-button
                size="sm"
                variant="primary"
                :loading="requestInProgress"
                :disabled="requestInProgress"
                @click="saveStageChange"
                >{{ $t('app.save') }}</i-button
              >
            </div>
          </div>
        </template>
      </i-popover>
      <div
        v-else
        class="flex items-center text-neutral-800 dark:text-neutral-200"
      >
        {{ deal.pipeline.name }} <icon icon="ChevronRight" class="h-4 w-4" />
        {{ deal.stage.name }}
      </div>
    </div>
    <div
      v-else
      class="flex items-center text-neutral-800 dark:text-neutral-200"
    >
      {{ deal.pipeline.name }} <icon icon="ChevronRight" class="h-4 w-4" />
      {{ deal.stage.name }}
    </div>
    <slot></slot>
  </div>
</template>
<script>
import { mapState } from 'vuex'
export default {
  props: {
    isFloating: {
      type: Boolean,
      default: false,
    },
    deal: {
      type: Object,
      required: true,
    },
  },
  data: () => ({
    selectPipeline: null,
    selectPipelineStage: null,
    requestInProgress: false,
  }),
  computed: {
    ...mapState({
      pipelines: state => state.pipelines.collection,
    }),
  },
  methods: {
    /**
     * Save the stage/pipeline change
     *
     * @return {Void}
     */
    saveStageChange() {
      this.requestInProgress = true
      Innoclapps.request()
        .put(`/deals/${this.deal.id}`, {
          pipeline_id: this.selectPipeline.id,
          stage_id: this.selectPipelineStage.id,
        })
        .then(({ data }) =>
          this.$store.dispatch('deals/updateRecordWhenViewing', {
            deal: data,
            isFloating: this.isFloating,
          })
        )
        .finally(() => {
          this.$refs.popover.hide()
          this.requestInProgress = false
        })
    },
    /**
     * Handle stage popover show event
     *
     * @return {VOid}
     */
    stagePopoverShowEvent() {
      this.selectPipeline = this.deal.pipeline
      this.selectPipelineStage = this.deal.stage
    },

    /**
     * Handle the pipeline changed event
     *
     * @param  {Object} value
     *
     * @return {Void}
     */
    handlePipelineChangedEvent(value) {
      if (value.id != this.deal.pipeline_id) {
        // Use the first stage selected from the new pipeline
        this.selectPipelineStage = value.stages[0] || null
      } else if (value.id === this.deal.pipeline_id) {
        // revent back to the original stage after the user select new stage
        // and goes back to the original without saving
        this.selectPipelineStage = this.deal.stage
      }
    },
  },
}
</script>
