<template>
  <presentation-chart
    :card="card"
    ref="chart"
    :request-query-string="requestQueryString"
  >
    <template #actions>
      <dropdown-select
        :items="pipelines"
        toggle-class="md:mr-3"
        label-key="name"
        value-key="id"
        placement="bottom-end"
        v-model="pipeline"
      />
    </template>
  </presentation-chart>
</template>
<script>
import { mapState } from 'vuex'

export default {
  props: {
    card: Object,
  },
  data() {
    return {
      pipeline: null, // active selected pipeline
    }
  },
  computed: {
    ...mapState({
      pipelines: state => state.pipelines.collection,
    }),

    /**
     * Extra request params for the card range
     *
     * @return {Object}
     */
    requestQueryString() {
      return {
        pipeline_id: this.pipeline.id,
      }
    },
  },
  created() {
    this.pipeline = this.pipelines[0]
  },
}
</script>
