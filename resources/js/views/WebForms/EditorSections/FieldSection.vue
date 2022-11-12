<template>
  <i-card
    class="group"
    :class="{
      'border border-primary-400': editing,
      'border border-danger-500': !originalField,
      'border border-transparent transition duration-75 hover:border-primary-400 dark:border dark:border-neutral-700':
        !editing && originalField,
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
          @option:selected="field = null"
          :reduce="resource => resource.id"
          v-model="resourceName"
        />
      </i-form-group>
      <i-form-group :label="$t('fields.field')" label-for="field">
        <i-custom-select
          label="label"
          field-id="field"
          :clearable="false"
          :selectable="field => field.disabled"
          :options="availableFields"
          @option:selected="handleFieldChanged"
          v-model="field"
        />
      </i-form-group>
      <i-form-group :label="$t('fields.label')" v-show="field !== null">
        <editor
          :with-image="false"
          default-tag="div"
          :toolbar="editorToolbarSections"
          v-model="fieldLabel"
        />
      </i-form-group>
      <div class="text-right">
        <div class="flex items-center justify-between">
          <div>
            <i-form-checkbox
              id="is-required"
              name="is-required"
              v-model:checked="isRequired"
              v-show="field !== null"
              :disabled="fieldMustBeRequired"
              :label="$t('fields.is_required')"
            />
          </div>
          <div class="space-x-2">
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
      </div>
    </div>
  </i-card>
</template>
<script>
import Editor from '@/components/Editor'
import find from 'lodash/find'
import Section from './Section'
import InteractsWithFieldSection from './InteractsWithFieldSection'
export default {
  mixins: [Section, InteractsWithFieldSection],
  components: { Editor },
  computed: {
    /**
     * Indicates whether the user can edit the section
     * Returns false if the field is deleted as well, when no original field found
     *
     * @return {Boolean}
     */
    canEditSection() {
      return !this.editing && this.originalField
    },

    /**
     * Section heading
     *
     * @return {String}
     */
    heading() {
      if (!this.originalField) {
        return this.$t('fields.no_longer_available')
      }

      return (
        this.resourceSingularLabel +
        ' | ' +
        this.originalField.label +
        (!this.section.isRequired ? ' ' + this.$t('fields.optional') : '')
      )
    },

    /**
     * The current resource singular label
     *
     * @return {String}
     */
    resourceSingularLabel() {
      return find(this.availableResources, {
        id: this.section.resourceName,
      }).label
    },

    /**
     * Original field before edit
     *
     * @return {Object|undefined}
     */
    originalField() {
      return find(this[this.section.resourceName + 'Fields'], {
        attribute: this.section.attribute,
      })
    },

    /**
     * Indicates whether the save button is disabled
     *
     * @return {Boolean}
     */
    saveIsDisabled() {
      return (
        this.fieldLabel === null || this.fieldLabel == '' || this.field == null
      )
    },
  },
  methods: {
    /**
     * Save the section information
     *
     * @return {Void}
     */
    saveSection() {
      let data = {
        isRequired: this.isRequired,
        label: this.fieldLabel,
        resourceName: this.resourceName,
        attribute: this.field.attribute,
      }

      // Field changed, re-generate request attribute data
      if (
        !this.originalField ||
        this.resourceName != this.section.resourceName ||
        this.field.attribute != this.originalField.attribute
      ) {
        data.requestAttribute = this.generateRequestAttribute()
      }

      this.updateSection(data)

      this.editing = false
    },

    /**
     * Invoke editing mode
     */
    setEditingMode() {
      this.field = this.originalField
      this.resourceName = this.section.resourceName
      this.fieldLabel = this.section.label
      this.isRequired = this.section.isRequired

      this.editing = true
    },
  },
}
</script>
