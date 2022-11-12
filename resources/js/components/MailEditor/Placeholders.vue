<template>
  <div v-show="visible" class="relative">
    <i-button-icon
      icon="X"
      @click="$emit('update:visible', false)"
      class="absolute right-0 top-5 hidden sm:block"
    />

    <i-tabs>
      <i-tab
        :key="groupName"
        :title="group.label"
        v-for="(group, groupName) in placeholders"
      >
        <placeholders-list
          :group-name="groupName"
          :placeholders="group.placeholders"
          @insert-requested="insertPlaceholder($event, groupName)"
        >
          <i-button-icon
            icon="X"
            @click="$emit('update:visible', false)"
            class="text-right sm:hidden"
          />
        </placeholders-list>
      </i-tab>
    </i-tabs>
  </div>
</template>
<script>
import PlaceholdersList from './PlaceholdersList'
export default {
  emits: ['update:visible', 'inserted'],
  components: { PlaceholdersList },
  props: {
    placeholders: {
      type: Object,
    },
    visible: {
      type: Boolean,
      default: true,
    },
  },
  methods: {
    /**
     * Insert the given placeholder
     *
     * @param  {Object} placeholder
     * @param {String} groupName
     *
     * @return {Void}
     */
    insertPlaceholder(placeholder, groupName) {
      tinymce.activeEditor.execCommand(
        'mceInsertContent',
        false,
        `<input type="text"
                        class="_placeholder"
                        data-group="${groupName}"
                        data-tag="${placeholder.tag}"
                        placeholder="${placeholder.description}" />`
      )

      // Emits before the editor content is changed
      // in this case, add timeout
      setTimeout(() => this.$emit('inserted'), 500)
    },
  },
}
</script>
