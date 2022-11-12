<template>
  <i-card :header="$t('deal.pipeline.pipelines')" no-body>
    <template #actions>
      <i-button :to="{ name: 'create-pipeline' }" icon="plus" size="sm">{{
        $t('deal.pipeline.create')
      }}</i-button>
    </template>
    <i-table class="-mt-px">
      <thead>
        <tr>
          <th class="text-left" v-t="'app.id'" width="5%"></th>
          <th class="text-left" v-t="'deal.pipeline.pipeline'"></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="pipeline in pipelines" :key="pipeline.id">
          <td v-text="pipeline.id"></td>
          <td>
            <router-link
              class="link"
              :to="{ name: 'edit-pipeline', params: { id: pipeline.id } }"
              >{{ pipeline.name }}</router-link
            >
          </td>
          <td class="flex justify-end">
            <i-minimal-dropdown>
              <i-dropdown-item
                :to="{ name: 'edit-pipeline', params: { id: pipeline.id } }"
              >
                {{ $t('app.edit') }}
              </i-dropdown-item>

              <i-dropdown-item @click="destroy(pipeline.id)">
                {{ $t('app.delete') }}
              </i-dropdown-item>
            </i-minimal-dropdown>
          </td>
        </tr>
      </tbody>
    </i-table>
  </i-card>
  <router-view></router-view>
</template>
<script>
import { mapState } from 'vuex'
export default {
  computed: {
    ...mapState({
      pipelines: state => state.pipelines.collection,
    }),
  },
  methods: {
    /**
     * Remove deal pipeline from storage
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    destroy(id) {
      this.$store
        .dispatch('pipelines/destroy', id)
        .then(() => Innoclapps.success(this.$t('deal.pipeline.deleted')))
    },
  },
}
</script>
