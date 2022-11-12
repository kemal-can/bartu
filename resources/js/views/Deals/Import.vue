<template>
  <i-layout>
    <import
      resource-name="deals"
      :request-query-string="{ pipeline_id: pipeline.id }"
    >
      <template #header>
        <i-card-heading class="flex items-center">
          <i18n-t scope="global" keypath="deal.import_in">
            <template #pipeline>
              <div class="ml-3 sm:w-1/5">
                <i-custom-select
                  :clearable="false"
                  label="name"
                  v-model="pipeline"
                  :options="pipelines"
                />
              </div>
            </template>
          </i18n-t>
        </i-card-heading>
      </template>
    </import>
  </i-layout>
</template>
<script>
import Import from '@/components/Import'
import { mapState } from 'vuex'
export default {
  components: { Import },
  data: () => ({
    pipeline: null,
  }),
  computed: {
    ...mapState({
      pipelines: state => state.pipelines.collection,
    }),
  },
  created() {
    this.pipeline = this.pipelines[0]
  },
}
</script>
