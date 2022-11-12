<template>
  <i-card no-body :header="$t('deal.pipeline.edit')" :overlay="!componentReady">
    <template #actions>
      <i-button-minimal variant="info" @click="goBack">
        {{ $t('app.go_back') }}
      </i-button-minimal>
    </template>
    <form @keydown="form.onKeydown($event)" @submit.prevent="update">
      <i-card-body>
        <i-form-group
          label-for="name"
          :label="$t('deal.pipeline.name')"
          required
        >
          <i-form-input v-model="form.name" id="name" name="name" type="text" />
          <form-error :form="form" field="name" />
        </i-form-group>
        <i-form-group class="mt-4 flex items-center">
          <p
            class="text-gray-700 mr-4 text-sm font-medium dark:text-neutral-100"
            v-t="'app.visibility_group.visible_to'"
          />

          <div class="mt-0.5 flex items-center space-x-3">
            <i-form-radio
              v-model="form.visibility_group.type"
              @change="form.visibility_group.depends_on = []"
              :disabled="pipeline.is_primary"
              value="all"
              >{{ $t('app.visibility_group.all') }}</i-form-radio
            >
            <i-form-radio
              v-model="form.visibility_group.type"
              @change="form.visibility_group.depends_on = []"
              :disabled="pipeline.is_primary"
              value="teams"
              >{{ $t('team.teams') }}</i-form-radio
            >
            <i-form-radio
              v-model="form.visibility_group.type"
              @change="form.visibility_group.depends_on = []"
              :disabled="pipeline.is_primary"
              value="users"
              >{{ $t('user.users') }}</i-form-radio
            >
          </div>
        </i-form-group>
        <i-alert
          variant="info"
          :show="componentReady && pipeline.is_primary"
          class="mt-4"
        >
          {{ $t('deal.pipeline.visibility_group.primary_restrictions') }}
        </i-alert>
        <div v-show="form.visibility_group.type === 'users'" class="mt-4">
          <i-custom-select
            v-model="form.visibility_group.depends_on"
            :options="usersWithoutAdministrators"
            :placeholder="$t('user.select')"
            label="name"
            multiple
            :reduce="option => option.id"
          />
        </div>
        <div v-show="form.visibility_group.type === 'teams'" class="mt-4">
          <i-custom-select
            v-model="form.visibility_group.depends_on"
            :options="teams"
            :placeholder="$t('team.select')"
            label="name"
            multiple
            :reduce="option => option.id"
          />
        </div>
      </i-card-body>
      <i-table>
        <thead>
          <tr>
            <th class="text-left" v-t="'deal.stage.name'"></th>
            <th class="text-left" v-t="'deal.stage.win_probability'"></th>
          </tr>
        </thead>
        <draggable
          v-model="form.stages"
          tag="tbody"
          :item-key="(item, index) => index"
          v-bind="draggableOptions"
          handle=".draggable-handle"
        >
          <template #item="{ element, index }">
            <tr>
              <td class="w-full sm:w-auto">
                <div class="flex rounded-md shadow-sm">
                  <div
                    class="relative flex grow items-stretch focus-within:z-10"
                  >
                    <div
                      class="absolute inset-y-0 left-0 flex items-center pl-3"
                    >
                      <i-button-icon
                        icon="Selector"
                        class="draggable-handle cursor-move"
                      />
                    </div>
                    <div
                      class="absolute inset-y-0 left-11 hidden w-14 border-l border-r border-neutral-300 px-2 dark:border-neutral-500 sm:flex sm:items-center sm:justify-center"
                      v-if="element.id"
                    >
                      ID: {{ element.id }}
                    </div>
                    <i-form-input
                      class="rounded-l-md"
                      :rounded="false"
                      :class="[element.id ? 'pl-10 sm:pl-[6.7rem]' : 'pl-10']"
                      @keydown.enter="newStage"
                      v-model="form.stages[index].name"
                      :ref="'stage-' + (index + 1)"
                    />
                  </div>
                  <i-button
                    variant="white"
                    :rounded="false"
                    @click="deleteStage(index)"
                    class="relative -ml-px rounded-r-md"
                    >{{ $t('app.delete') }}</i-button
                  >
                </div>
                <form-error :form="form" :field="'stages.' + index + '.name'" />
              </td>
              <td>
                <div class="mt-sm-0 mt-4 flex items-center">
                  <div class="mr-4 grow">
                    <input
                      v-model="form.stages[index].win_probability"
                      type="range"
                      class="h-2 w-full appearance-none rounded-md border border-neutral-200 bg-neutral-200 dark:border-neutral-500 dark:bg-neutral-700"
                      :min="1"
                      :max="100"
                    />
                  </div>
                  <div>
                    {{ form.stages[index].win_probability }}
                  </div>
                </div>
                <form-error
                  :form="form"
                  :field="'stages.' + index + '.win_probability'"
                />
              </td>
            </tr>
          </template>
        </draggable>

        <tfoot>
          <tr>
            <td colspan="2" class="py-3 px-7">
              <i-button-minimal variant="primary" @click="newStage">
                {{ $t('deal.stage.add') }}
              </i-button-minimal>
            </td>
          </tr>
        </tfoot>
      </i-table>
    </form>
    <template #footer>
      <div class="flex justify-end">
        <i-button type="button" @click="update" :disabled="form.busy">{{
          $t('app.save')
        }}</i-button>
      </div>
    </template>
  </i-card>
</template>
<script>
import ProvidesDraggableOptions from '@/mixins/ProvidesDraggableOptions'
import Form from '@/components/Form/Form'
import draggable from 'vuedraggable'
import map from 'lodash/map'
import { mapState } from 'vuex'

export default {
  mixins: [ProvidesDraggableOptions],
  components: { draggable },
  data: () => ({
    form: new Form({
      name: null,
      stages: [],
      visibility_group: {
        type: 'all',
        depends_on: [],
      },
    }),
    teams: [],
    pipeline: {},
    componentReady: false,
  }),
  computed: {
    ...mapState({
      users: state => state.users.collection,
    }),
    usersWithoutAdministrators() {
      return this.users.filter(user => !user.super_admin)
    },
  },
  methods: {
    /**
     * Upate pipeline
     *
     * @return {Void}
     */
    update() {
      this.form.stages = map(this.form.stages, (stage, index) => {
        stage.display_order = index
        return stage
      })

      this.$store
        .dispatch('pipelines/update', {
          form: this.form,
          id: this.$route.params.id,
          queryString: {
            with: 'visibilityGroup.users;visibilityGroup.teams',
          },
        })
        // Update the stages in case new stages are created so we can have the ID's
        .then(pipeline => {
          this.resetStoreState()
          this.form.stages = this.cleanObject(pipeline.stages)
          Innoclapps.success(this.$t('deal.pipeline.updated'))
        })
    },

    /**
     * Prepare component
     *
     * @return {Void}
     */
    prepareComponent() {
      Promise.all([
        this.$store.dispatch('pipelines/get', {
          id: this.$route.params.id,
          queryString: {
            with: 'visibilityGroup.users;visibilityGroup.teams',
          },
        }),
        Innoclapps.request().get('/teams'),
      ])
        .then(values => {
          this.pipeline = values[0]
          this.form.fill('name', values[0].name)
          if (values[0].visibility_group) {
            this.form.fill('visibility_group', values[0].visibility_group)
          } else {
            this.form.fill('visibility_group', {
              type: 'all',
              depends_on: [],
            })
          }

          this.form.fill('stages', values[0].stages)

          if (this.form.stages.length === 0) {
            this.newStage()
          }
          this.teams = values[1].data
        })
        .finally(() => (this.componentReady = true))
    },

    /**
     * Add new pipeline stage
     *
     * @return {Void}
     */
    newStage() {
      this.form.stages.push({
        name: '',
        win_probability: 1,
      })

      this.$nextTick(() => {
        this.$refs['stage-' + this.form.stages.length].focus()
      })
    },

    /**
     * Remove stage from form
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    removeStageFromForm(index) {
      this.form.stages.splice(index, 1)
    },

    /**
     * Delete stage from storage
     *
     * @param  {Number} index
     *
     * @return {Void}
     */
    deleteStage(index) {
      let stageId = this.form.stages[index].id

      // Form not yet saved, e.q. user added new stage then want to
      // delete before saving the form
      if (!stageId) {
        this.removeStageFromForm(index)
        return
      }

      this.$dialog.confirm().then(() => {
        Innoclapps.request()
          .delete(`/pipeline-stages/${stageId}`)
          .then(() => {
            this.resetStoreState()
            this.removeStageFromForm(index)
          })
      })
    },
  },
  created() {
    this.prepareComponent()
  },
}
</script>
