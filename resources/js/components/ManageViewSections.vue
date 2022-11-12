<template>
  <div v-if="show">
    <div class="mb-4">
      <p
        class="font-medium text-neutral-700 dark:text-white"
        v-t="'app.record_view.sections.edit_heading'"
      />
      <p
        class="text-sm text-neutral-500 dark:text-neutral-300"
        v-t="'app.record_view.sections.edit_subheading'"
      />
    </div>
    <draggable
      v-model="sections"
      item-key="id"
      class="space-y-3"
      handle=".section-reorder-handle"
      v-bind="draggableOptions"
    >
      <template #item="{ element }">
        <div
          class="flex rounded-md border border-neutral-200 bg-white p-4 dark:border-neutral-700 dark:bg-neutral-900"
        >
          <div class="grow">
            <i-form-checkbox
              v-model:checked="checked[element.id]"
              :name="'section-' + element.id"
              :id="'section-' + element.id"
            >
              {{ element.heading || element.id }}
            </i-form-checkbox>
          </div>
          <div>
            <i-button-icon
              icon="Selector"
              class="section-reorder-handle cursor-move"
            />
          </div>
        </div>
      </template>
    </draggable>
    <div class="mt-3 flex items-center justify-end space-x-2">
      <i-button variant="white" size="sm" @click="$emit('update:show', false)">
        {{ $t('app.cancel') }}
      </i-button>
      <i-button
        variant="primary"
        size="sm"
        @click="save"
        :disabled="sectionsAreBeingSaved"
        :loading="sectionsAreBeingSaved"
      >
        {{ $t('app.save') }}
      </i-button>
    </div>
  </div>
</template>

<script>
import map from 'lodash/map'
import draggable from 'vuedraggable'
import ProvidesDraggableOptions from '@/mixins/ProvidesDraggableOptions'

export default {
  emits: ['saved', 'update:show', 'update:sections'],
  mixins: [ProvidesDraggableOptions],
  components: { draggable },
  props: {
    sections: { type: Array, required: true },
    show: { type: Boolean, default: false },
    identifier: { type: String, required: true },
  },
  data: () => ({
    checked: {},
    sectionsAreBeingSaved: false,
  }),
  methods: {
    save() {
      this.sectionsAreBeingSaved = true
      Innoclapps.request()
        .post('/settings', {
          [this.identifier + '_view_sections']: map(
            this.sections,
            (section, index) => ({
              id: section.id,
              order: index + 1,
              enabled: this.checked[section.id],
            })
          ),
        })
        .then(() => {
          this.$emit('saved')

          const newValue = map(this.sections, (section, index) =>
            Object.assign({}, section, {
              order: index + 1,
              enabled: this.checked[section.id],
            })
          )

          this.$emit('update:sections', newValue)
        })
        .finally(() => (this.sectionsAreBeingSaved = false))
    },
  },
  mounted() {
    this.sections.forEach(section => {
      this.checked[section.id] = section.enabled
    })
  },
}
</script>
