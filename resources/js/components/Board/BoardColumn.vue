<template>
  <div
    class="inline-flex h-full w-80 flex-col overflow-y-hidden rounded bg-neutral-200 align-top shadow-sm dark:bg-neutral-900"
  >
    <div class="px-3 py-2">
      <div class="flex items-center">
        <slot name="columnHeader" :column="column">
          <h5
            class="mr-auto truncate font-medium text-neutral-800 dark:text-neutral-100"
          >
            {{ column.name }}
          </h5>
          <div>
            <slot name="topRight" :column="column" :total="total"></slot>
          </div>
        </slot>
      </div>
      <slot name="afterColumnHeader" :column="column" :total="total"></slot>
    </div>
    <div class="h-auto overflow-y-auto overflow-x-hidden">
      <draggable
        v-model="column.cards"
        :move="onMoveCallback"
        :item-key="item => item.id"
        :emptyInsertThreshold="100"
        @start="onDragStart"
        @end="onDragEnd"
        @change="onUpdateEvent"
        v-bind="columnCardsDraggableOptions"
        :group="{ name: boardId }"
      >
        <template #item="{ element }">
          <div
            class="m-2 overflow-hidden whitespace-normal rounded-md bg-white shadow-sm dark:bg-neutral-800"
          >
            <slot name="card" :column="column" :card="element">
              <div class="px-4 py-5 sm:p-6">
                {{ element.display_name }}
              </div>
            </slot>
          </div>
        </template>
      </draggable>
    </div>
    <div class="flex items-center p-3"></div>
  </div>
</template>
<script>
import draggable from 'vuedraggable'
import ProvidesDraggableOptions from '@/mixins/ProvidesDraggableOptions'
export default {
  emits: ['drag-start', 'drag-end', 'updated'],
  mixins: [ProvidesDraggableOptions],
  components: { draggable },
  props: {
    column: {
      required: true,
      type: Object,
    },
    boardId: {
      required: true,
      type: String,
    },
  },
  computed: {
    total() {
      return this.column.cards.length
    },
    columnCardsDraggableOptions() {
      return {
        ...this.scrollableDraggableOptions,
        ...{ filter: 'a, button', delay: 50, preventOnFilter: false },
      }
    },
  },
  methods: {
    onDragStart(e) {
      this.$emit('drag-start', e)
    },
    onDragEnd(e) {
      this.$emit('drag-end', e)
    },
    onMoveCallback(evt, originalEvent) {
      if (this.$gate.denies('update', evt.draggedContext.element)) {
        return false
      }
    },
    onUpdateEvent(e) {
      if (e.moved) {
        this.$emit('updated', {
          column: this.column,
          element: e.moved.element,
        })
      } else if (e.added) {
        this.$emit('updated', {
          column: this.column,
          element: e.added.element,
        })
      }
    },
  },
}
</script>
<!-- <style lang="scss">
@import '~/_variables.scss';

.board-column {
  display: inline-flex;
  flex-direction: column;
  width: 20rem;
  height: 100%;
  vertical-align: top;
  background-color: $board-body-background-color;
  box-shadow: 0 0 0 1px rgba(20, 20, 31, 0.05),
    0 1px 3px 0 rgba(20, 20, 31, 0.15);
  margin-right: 15px;
  border-radius: 0.25rem;
}

.board-column:last-child {
  margin-right: 0px;
}

.board-header {
  padding: 0.5rem;
}

.board-title {
  font-size: 1rem;
  margin: 0;
}

.board-body {
  padding: 0.5rem;
  min-height: 2rem;
  flex: 1;
  overflow-y: auto;
}

.board-card .card-body {
  padding: 1rem;
}
</style>
 -->
