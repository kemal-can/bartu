<template>
  <i-card
    class="group"
    :class="{
      'border border-primary-400': editing,
      'border border-transparent transition duration-75 hover:border-primary-400 dark:border dark:border-neutral-700':
        !editing,
    }"
  >
    <template #header>
      <p
        class="font-semibold text-neutral-800 dark:text-neutral-200"
        v-text="heading"
      />
    </template>
    <template #actions>
      <div class="inline-flex space-x-2">
        <i-button-icon
          icon="PencilAlt"
          class="block md:hidden md:group-hover:block"
          icon-class="h-4 w-4"
          v-show="canEditSection"
          @click="setEditingMode"
        />
        <i-button-icon
          icon="Trash"
          class="block md:hidden md:group-hover:block"
          icon-class="h-4 w-4"
          @click="removeSection"
        />
      </div>
    </template>
    <div
      v-show="!editing"
      class="text-sm text-neutral-900 dark:text-neutral-300"
    >
      <p v-html="section.label"></p>
    </div>
    <div v-if="editing">
      <i-form-group
        :label="$t('form.sections.field.resourceName')"
        label-for="resourceName"
      >
        <i-custom-select
          label="label"
          field-id="resourceName"
          :clearable="false"
          :options="availableResources"
          :reduce="resource => resource.id"
          v-model="resourceName"
        />
      </i-form-group>
      <i-form-group :label="$t('fields.label')">
        <editor
          :with-image="false"
          default-tag="div"
          :toolbar="editorToolbarSections"
          v-model="label"
        />
      </i-form-group>
      <i-form-group>
        <i-form-checkbox
          id="is-required"
          name="is-required"
          v-model:checked="isRequired"
          :label="$t('fields.is_required')"
        />
        <i-form-checkbox
          id="file-multiple"
          name="file-multiple"
          v-model:checked="multiple"
          :label="$t('form.sections.file.multiple')"
        />
      </i-form-group>

      <div class="space-x-2 text-right">
        <i-button size="sm" @click="editing = false" variant="white">
          {{ $t('app.cancel') }}
        </i-button>
        <i-button
          size="sm"
          @click="saveSection"
          :disabled="saveIsDisabled"
          variant="secondary"
        >
          {{ $t('app.save') }}
        </i-button>
      </div>
    </div>
  </i-card>
</template>
<script>
import Editor from '@/components/Editor'
import find from 'lodash/find'
import Section from './Section'
export default {
  mixins: [Section],
  components: { Editor },
  props: {
    availableResources: {
      required: true,
    },
  },
  data: () => ({
    label: null,
    isRequired: false,
    resourceName: null,
    multiple: false,
  }),
  computed: {
    /**
     * Indicates whether the user can edit the section
     *
     * @return {Boolean}
     */
    canEditSection() {
      return !this.editing
    },

    /**
     * Section heading
     *
     * @return {String}
     */
    heading() {
      return (
        this.resourceSingularLabel +
        ' | ' +
        (this.section.multiple
          ? this.$t('form.sections.file.files')
          : this.$t('form.sections.file.file')) +
        (!this.section.isRequired ? ' ' + this.$t('fields.optional') : '')
      )
    },

    /**
     * The current selected resource singular label
     *
     * @return {String}
     */
    resourceSingularLabel() {
      return find(this.availableResources, {
        id: this.section.resourceName,
      }).label
    },

    /**
     * Indicates whether the save button is disabled
     *
     * @return {Boolean}
     */
    saveIsDisabled() {
      return this.label === null || this.label == ''
    },
  },
  methods: {
    /**
     * Save the section information
     *
     * @return {Void}
     */
    saveSection() {
      this.updateSection({
        resourceName: this.resourceName,
        label: this.label,
        isRequired: this.isRequired,
        multiple: this.multiple,
      })

      this.editing = false
    },

    /**
     * Invoke editing mode
     */
    setEditingMode() {
      this.resourceName = this.section.resourceName
      this.label = this.section.label
      this.isRequired = this.section.isRequired
      this.multiple = this.section.multiple

      this.editing = true
    },
  },
}
</script>
