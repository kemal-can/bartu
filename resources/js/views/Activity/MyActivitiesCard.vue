<template>
  <card-with-async-table :card="card" ref="table">
    <template #actions>
      <i-button-minimal
        variant="primary"
        @click="activityBeingCreated = true"
        class="hide-when-editing !py-0.5 !px-2.5"
        >{{ $t('activity.create') }}</i-button-minimal
      >
      <activity-create
        :visible="activityBeingCreated"
        @created="handleActivityCreatedEvent"
        @hidden="activityBeingCreated = false"
      />
    </template>
    <template #empty="slotProps">
      <div
        class="flex flex-col justify-center"
        :class="{
          'items-center py-4': !slotProps.search && !slotProps.loading,
        }"
      >
        <icon
          icon="Check"
          class="h-9 w-9 text-success-500"
          v-show="!slotProps.search && !slotProps.loading"
        />

        <p
          class="text-neutral-500 dark:text-neutral-300"
          :class="{ 'mt-2 text-lg': !slotProps.search && !slotProps.loading }"
        >
          <span v-show="slotProps.loading" v-text="slotProps.text"></span>

          <span v-show="!slotProps.loading">
            {{ slotProps.search ? slotProps.text : $t('app.all_caught_up') }}
          </span>
        </p>
      </div>
    </template>
    <template #title="{ row, formatted }">
      <div class="flex items-center">
        <div class="mr-1.5 mt-1">
          <state-change :activity="row" @state-changed="reloadTable" />
        </div>
        <router-link
          class="link"
          :to="{ name: 'view-activity', params: { id: row.id } }"
          >{{ formatted }}</router-link
        >
      </div>
    </template>
    <template #type.name="{ row, formatted }">
      <text-background
        :color="row.type.swatch_color"
        class="inline-flex items-center self-start rounded-full font-normal leading-5 dark:!text-white"
      >
        <span class="flex items-center px-2.5 text-sm">
          <icon :icon="row.type.icon" class="mr-1 h-4 w-4" />
          {{ formatted }}
        </span>
      </text-background>
    </template>
  </card-with-async-table>
</template>
<script>
import StateChange from './StateChange'
import ActivityCreate from '@/views/Activity/CreateSimple'
import TextBackground from '@/components/TextBackground'
export default {
  components: {
    StateChange,
    ActivityCreate,
    TextBackground,
  },
  props: {
    card: Object,
  },
  data: () => ({
    activityBeingCreated: false,
  }),
  methods: {
    handleActivityCreatedEvent() {
      this.reloadTable()
      this.activityBeingCreated = false
    },

    /**
     * Reload the table
     */
    reloadTable() {
      this.$refs.table.reload()
    },
  },
}
</script>
<style scoped>
:deep(tr > td) {
  position: relative;
}

:deep(tr > td:first-child:after),
:deep(tr > td:first-child:before) {
  content: '';
  position: absolute;
  left: 0;
  width: 100%;
}

:deep(td.due:first-child:before),
:deep(td.due:first-child:after) {
  width: auto;
  height: 100%;
  top: 0;
  border-left: 3px solid rgba(var(--color-danger-500), 1);
}

:deep(td.not-due:first-child:before),
:deep(td.not-due:first-child:after) {
  width: auto;
  height: 100%;
  top: 0;
  border-left: 3px solid transparent;
}
</style>
