<template>
  <div
    class="flex items-center space-x-1 text-sm text-neutral-600 dark:text-white"
  >
    <span v-t="'table.length_menu.show'"></span>

    <dropdown-select
      v-model="collection.perPage"
      @change="$emit('change', $event)"
      :items="collection.perPageOptions"
      :disabled="disabled"
    />

    <span v-t="'table.length_menu.entries'"></span>
  </div>
</template>
<script>
export default {
  emits: ['change'],
  props: {
    disabled: { default: false, type: Boolean },
    collection: { required: true, type: Object },
  },
}
</script>
