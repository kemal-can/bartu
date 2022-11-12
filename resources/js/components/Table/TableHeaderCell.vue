<template>
  <th
    scope="col"
    :class="[
      'whitespace-nowrap',
      column.thClass,
      {
        'table-sticky-column': column.primary === true,
        'text-left': isLeftAligned,
      },
    ]"
    :style="{
      width: width,
      'min-width': column.minWidth,
    }"
  >
    <div class="relative flex items-center">
      <div v-if="isSelectable && index === 0" class="mr-4">
        <i-form-checkbox
          @change="$emit('toggle-select-all')"
          :checked="allRowsSelected"
        />
      </div>
      <div
        v-show="showActionsList"
        v-if="index === 0"
        class="absolute left-8 z-50 ml-2 w-60 font-normal normal-case"
      >
        <actions
          type="select"
          :ids="actionIds"
          size="sm"
          view="index"
          :action-request-query-string="actionRequestQueryString"
          :actions="actions"
          :resource-name="resourceName"
        />
      </div>

      <div :class="['grow', { hidden: index === 0 && showActionsList }]">
        <a
          v-if="column.sortable"
          @click.prevent="$emit('sort-requested', column.attribute)"
          class="group inline-flex hover:text-neutral-700 dark:hover:text-neutral-400"
          href="#"
        >
          <slot :column="column">
            {{ column.label }}
          </slot>
          <span
            class="ml-2 flex-none rounded bg-neutral-200 text-sm text-neutral-900 group-hover:bg-neutral-300"
            v-show="isOrderedByComputed"
          >
            <icon
              :icon="isSortedAscending ? 'ChevronUp' : 'ChevronDown'"
              class="h-4 w-4"
            />
          </span>
          <span
            v-show="!isOrderedByComputed"
            class="invisible ml-2 flex-none rounded text-neutral-400 group-hover:visible group-focus:visible"
          >
            <icon
              icon="ChevronDown"
              class="invisible h-4 w-4 flex-none rounded text-neutral-400 group-hover:visible group-focus:visible"
            />
          </span>
        </a>
        <span v-else>
          <slot :column="column">
            {{ column.label }}
          </slot>
        </span>
      </div>
    </div>
  </th>
</template>
<script>
import Actions from '@/components/Actions'
export default {
  emits: ['toggle-select-all', 'sort-requested'],
  components: { Actions },
  props: {
    column: { type: Object, required: true },
    isSelectable: { type: Boolean, required: true },
    allRowsSelected: { type: Boolean, required: true },
    selectedRows: { type: Array, required: true },
    resourceName: { type: String, required: true },
    actionRequestQueryString: {
      type: Object,
      required: true,
      default() {
        return {}
      },
    },
    actions: { type: Array, required: true },
    index: { type: Number, required: true },
    isOrderedBy: { type: Function, required: true },
    isSorted: { type: Function, required: true },
  },
  computed: {
    /**
     * Indicates whether table is sorted ascending by the current column
     *
     * @return {Boolean}
     */
    isSortedAscending() {
      return this.isSorted('asc', this.column.attribute)
    },

    /**
     * Indicates whether table is ordered by the current column
     *
     * @return {Boolean}
     */
    isOrderedByComputed() {
      return this.isOrderedBy(this.column.attribute)
    },

    /**
     * Get the table header width
     *
     * @return {String}
     */
    width() {
      return this.column.width || 'auto'
    },

    /**
     * Indicates whether the action select should be shown
     *
     * @return {Boolean}
     */
    showActionsList() {
      return this.selectedRows.length > 0 && this.actions.length > 0
    },

    /**
     * Get the action ids array
     *
     * @return {Array}
     */
    actionIds() {
      return this.selectedRows.map(row => row.id)
    },

    /**
     * Indicates whether the table is left aligned
     *
     * @return {Boolean}
     */
    isLeftAligned() {
      return (
        !this.column.thClass ||
        !['text-center', 'text-left', 'text-right'].some(alignmentClass =>
          this.column.thClass.includes(alignmentClass)
        )
      )
    },
  },
}
</script>
