<template>
  <i-card no-body :header="$t('workflow.workflows')" :overlay="!componentReady">
    <template #actions>
      <i-button
        @click="add"
        size="sm"
        icon="plus"
        :disabled="isWorkflowBeingCreated"
        v-show="hasDefinedWorkflows"
        >{{ $t('workflow.create') }}</i-button
      >
    </template>
    <div v-if="componentReady">
      <transition-group
        name="flip-list"
        tag="ul"
        class="divide-y divide-neutral-200 dark:divide-neutral-700"
        v-if="hasDefinedWorkflows"
      >
        <li
          v-for="(workflow, index) in listWorkflows"
          :key="workflow.id || workflow.key"
        >
          <workflow
            v-model:workflow="workflows[index]"
            @delete-requested="destroy"
            :index="index"
            :triggers="availableTriggers"
          />
        </li>
      </transition-group>

      <div v-else class="p-7">
        <button
          type="button"
          @click="add"
          class="relative flex w-full flex-col items-center rounded-lg border-2 border-dashed border-neutral-300 p-6 hover:border-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 sm:p-12"
        >
          <icon
            icon="Adjustments"
            class="mx-auto h-12 w-12 text-primary-500 dark:text-primary-400"
          />

          <span class="mt-2 block font-medium text-neutral-900 dark:text-white">
            {{ $t('workflow.create') }}
          </span>

          <p
            v-t="'workflow.info'"
            class="mt-2 block max-w-2xl text-sm text-neutral-600 dark:text-neutral-300"
          />
        </button>
      </div>
    </div>
  </i-card>
</template>
<script>
import Workflow from './Workflow'
import findIndex from 'lodash/findIndex'
import { randomString } from '@/utils'
export default {
  components: { Workflow },
  data: () => ({
    availableTriggers: [],
    workflows: [],
    componentReady: false,
  }),
  computed: {
    /**
     * Get the workflows for the list
     *
     * @return {Array}
     */
    listWorkflows() {
      return this.workflows.sort(
        (a, b) => +b.is_active - +a.is_active || a.title.localeCompare(b.title)
      )
    },

    /**
     * Indicates whether new workflow creation is in progress
     *
     * @return {Boolean}
     */
    isWorkflowBeingCreated() {
      return this.workflows.filter(workflow => workflow.key).length > 0
    },

    /**
     * Indicates whether there are workflows defined
     *
     * @return {Boolean}
     */
    hasDefinedWorkflows() {
      return this.workflows.length > 0
    },
  },
  methods: {
    /**
     * Add new workflow
     */
    add() {
      this.workflows.unshift({
        key: randomString(10),
        title: null,
        description: null,
        is_active: false,
        trigger_type: null,
        action_type: null,
      })
    },

    /**
     * Delete the given workflow
     *
     * @param  {Object} workflow
     *
     * @return {Void}
     */
    destroy(workflow) {
      if (!workflow.id) {
        this.workflows.splice(
          findIndex(this.workflows, ['key', workflow.key]),
          1
        )
      } else {
        this.$dialog.confirm().then(() => {
          Innoclapps.request()
            .delete('/workflows/' + workflow.id)
            .then(({ data }) => {
              this.workflows.splice(
                findIndex(this.workflows, ['id', Number(workflow.id)]),
                1
              )
              Innoclapps.success(this.$t('workflow.deleted'))
            })
        })
      }
    },
  },
  created() {
    Promise.all([
      Innoclapps.request().get('/workflows'),
      Innoclapps.request().get('/workflows/triggers'),
    ]).then(responses => {
      this.workflows.push(...responses[0].data)
      this.availableTriggers = responses[1].data
      this.componentReady = true
    })
  },
}
</script>
