<template>
  <div>
    <div class="board-top py-4 px-4 sm:px-6">
      <slot name="top"></slot>
    </div>
    <div
      class="board block space-x-4 overflow-x-scroll whitespace-nowrap px-4 pb-2 sm:px-6"
      :class="boardClass"
      :id="boardId"
    >
      <board-column
        v-for="column in columns"
        @updated="$emit('column-updated', $event)"
        @drag-start="$emit('drag-start', $event)"
        @drag-end="$emit('drag-end', $event)"
        :key="'column-' + column.name"
        :column="column"
        :board-id="boardId"
      >
        <template v-for="(_, name) in $slots" v-slot:[name]="slotData"
          ><slot :name="name" v-bind="slotData"
        /></template>
      </board-column>
    </div>
  </div>
</template>
<script>
import BoardColumn from './BoardColumn'
export default {
  emits: ['drag-start', 'drag-end', 'column-updated'],
  components: { BoardColumn },
  props: {
    columns: {
      type: Array,
      required: true,
    },
    boardClass: [String, Array, Object],
    boardId: {
      required: true,
      type: String,
    },
  },
}
</script>
<style>
:root {
  --board-top-height: 70px;
}

.board {
  height: calc(100vh - (var(--navbar-height) + var(--board-top-height)));
}

@media (min-width: 1024px) {
  .board-top {
    height: var(--board-top-height);
    max-height: var(--board-top-height);
  }
}
</style>
