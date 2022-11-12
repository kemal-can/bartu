<template>
  <i-vertical-navigation>
    <inbox-folders-menu-item
      v-for="folder in folders"
      :key="folder.id"
      :folder="folder"
    />
  </i-vertical-navigation>

  <!-- Creates a placeholders when not active folders are found -->
  <div v-if="!hasFolders">
    <div
      :class="[
        'mb-2 rounded-md bg-neutral-200',
        placeholder === 2 || placeholder === 3 ? 'p-3' : 'p-4',
      ]"
      v-for="placeholder in [1, 2, 3, 4]"
      :key="'p-' + placeholder"
    />
  </div>
</template>
<script>
import InboxFoldersMenuItem from './InboxFoldersMenuItem'
export default {
  components: { InboxFoldersMenuItem },
  props: {
    folders: { type: Array, required: true },
  },
  computed: {
    hasFolders() {
      return this.folders.length > 0
    },
  },
}
</script>
