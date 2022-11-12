<template>
  <nav aria-label="Progress">
    <ol
      class="divide-y divide-neutral-300 rounded-md border border-neutral-300 dark:divide-neutral-600 dark:border-neutral-600 lg:flex lg:flex-wrap lg:divide-y-0"
    >
      <pipeline-stage
        :stage="stage"
        :deal="deal"
        @stage-updated="$emit('stage-updated', $event)"
        :index="index"
        :key="stage.id"
        v-for="(stage, index) in deal.pipeline.stages"
      />
    </ol>
  </nav>
</template>
<script>
import PipelineStage from './DealMiniPipelineStage'
export default {
  emits: ['stage-updated'],
  components: { PipelineStage },
  props: {
    deal: {
      type: Object,
      required: true,
    },
  },
}
</script>
