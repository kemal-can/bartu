<template>
  <i-card :header="$t('form.forms')" no-body :overlay="!componentReady">
    <template #actions>
      <i-button @click="create" size="sm" icon="Plus" v-show="hasForms">{{
        $t('form.create')
      }}</i-button>
    </template>
    <div v-if="componentReady">
      <transition-group
        name="flip-list"
        tag="ul"
        class="divide-y divide-neutral-200 dark:divide-neutral-700"
        v-if="hasForms"
      >
        <li v-for="form in listForms" :key="form.id">
          <div :class="{ 'opacity-70': form.status === 'inactive' }">
            <div class="flex items-center px-4 py-4 sm:px-6">
              <div
                class="min-w-0 flex-1 sm:flex sm:items-center sm:justify-between"
              >
                <div class="truncate">
                  <div class="flex">
                    <a
                      href="#"
                      class="link truncate text-sm font-medium"
                      @click.prevent="edit(form.id)"
                      v-text="form.title"
                    >
                    </a>
                  </div>
                  <div class="mt-1 flex">
                    <div class="flex items-center space-x-4 text-sm">
                      <a
                        :href="form.public_url"
                        class="link"
                        target="_blank"
                        rel="noopener noreferrer"
                        v-t="'app.preview'"
                      />
                      <p class="text-neutral-800 dark:text-neutral-300">
                        {{
                          $t('form.total_submissions', {
                            total: form.total_submissions,
                          })
                        }}
                      </p>
                      <p
                        class="flex items-center text-sm text-neutral-800 dark:text-neutral-300"
                      >
                        {{ pipeline(form.submit_data.pipeline_id).name }}
                        <icon icon="ChevronRight" class="mx-1 mt-px h-3 w-3" />
                        {{
                          stage(
                            form.submit_data.pipeline_id,
                            form.submit_data.stage_id
                          ).name
                        }}
                      </p>
                    </div>
                  </div>
                </div>
                <div class="mt-4 shrink-0 sm:mt-0 sm:ml-5">
                  <div class="flex -space-x-1 overflow-hidden">
                    <i-form-toggle
                      class="mr-4"
                      @change="toggleStatus(form)"
                      :label="$t('form.active')"
                      value="active"
                      unchecked-value="inactive"
                      :model-value="form.status"
                    />
                  </div>
                </div>
              </div>
              <div class="ml-5 shrink-0">
                <i-minimal-dropdown>
                  <i-dropdown-item
                    @click="edit(form.id)"
                    :text="$t('app.edit')"
                  />

                  <i-dropdown-item
                    @click="destroy(form.id)"
                    :text="$t('app.delete')"
                  />
                </i-minimal-dropdown>
              </div>
            </div>
          </div>
        </li>
      </transition-group>
      <div v-else class="p-7">
        <button
          type="button"
          @click="create"
          class="relative flex w-full flex-col items-center rounded-lg border-2 border-dashed border-neutral-300 p-6 hover:border-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:p-12"
        >
          <icon
            icon="MenuAlt2"
            class="mx-auto h-12 w-12 text-primary-500 dark:text-primary-400"
          />

          <span class="mt-2 block font-medium text-neutral-900 dark:text-white">
            {{ $t('form.create') }}
          </span>

          <p
            v-t="'form.info'"
            class="mt-2 block max-w-2xl text-sm text-neutral-600 dark:text-neutral-300"
          />
        </button>
      </div>
    </div>
  </i-card>
  <!-- Create -->
  <router-view></router-view>
</template>
<script>
import Form from '@/components/Form/Form'
import { mapState } from 'vuex'
import orderBy from 'lodash/orderBy'

export default {
  data: () => ({
    componentReady: false,
  }),
  computed: {
    ...mapState({
      forms: state => state.webForms.collection,
      pipelines: state => state.pipelines.collection,
    }),
    /**
     * Indicates whether there are forms created
     *
     * @return {Boolean}
     */
    hasForms() {
      return this.forms.length > 0
    },

    /**
     * Get the forms for the list
     *
     * @return {Array}
     */
    listForms() {
      return orderBy(this.forms, ['status', 'title'], ['asc', 'asc'])
    },
  },
  methods: {
    /**
     * Get stage from the given pipeline and stage id
     */
    stage(pipelineId, id) {
      const pipeline = this.pipeline(pipelineId)

      return pipeline.stages.filter(stage => Number(id) === Number(stage.id))[0]
    },

    /**
     * Get pipeline from the given id
     */
    pipeline(id) {
      return this.pipelines.filter(
        pipeline => Number(id) === Number(pipeline.id)
      )[0]
    },

    /**
     * Delete the given form
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    destroy(id) {
      this.$store
        .dispatch('webForms/destroy', id)
        .then(() => Innoclapps.success(this.$t('form.deleted')))
    },

    /**
     * Create new form
     *
     * @return {Void}
     */
    create() {
      this.$router.push({
        name: 'web-form-create',
      })
    },

    /**
     * Edit form
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    edit(id) {
      this.$router.push({
        name: 'web-form-edit',
        params: {
          id: id,
        },
      })
    },

    /**
     * Toggle the form status
     *
     * @param  {Object} form
     *
     * @return {Void}
     */
    toggleStatus(form) {
      let status = form.status === 'active' ? 'inactive' : 'active'

      this.$store.dispatch('webForms/update', {
        form: new Form({
          status: status,
        }),
        id: form.id,
      })
    },
  },
  created() {
    this.$store
      .dispatch('webForms/fetch')
      .finally(() => (this.componentReady = true))
  },
}
</script>
