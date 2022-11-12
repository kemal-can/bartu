<template>
  <div>
    <i-form-checkbox
      :id="'form-folder' + index + '-' + folder.name"
      :key="'form-folder' + index + '-' + folder.name"
      v-model:checked="folders[index].syncable"
      class="mb-1"
      :disabled="!folder.selectable"
      :label="folder.display_name"
    />
    <template v-if="folder.children">
      <email-account-form-folder
        class="ml-4 mt-2"
        v-for="(child, childIndex) in folder.children"
        :folder="child"
        :index="childIndex"
        :folders="folder.children"
        :key="childIndex + child.name"
      />
    </template>
  </div>
</template>
<script>
export default {
  name: 'email-account-form-folder',
  props: {
    folder: {
      required: true,
      type: Object,
    },
    folders: {
      required: true,
      type: Array,
    },
    index: {
      required: true,
      type: Number,
    },
  },
}
</script>
