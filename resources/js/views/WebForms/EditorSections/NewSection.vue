<template>
  <i-card class="border border-primary-400">
    <template #header>
      <p
        class="font-semibold text-neutral-800 dark:text-neutral-200"
        v-t="'form.sections.new'"
      />
    </template>
    <template #actions>
      <i-button-icon icon="X" icon-class="h-4 w-4" @click="removeSection" />
    </template>
    <i-form-group :label="$t('form.sections.type')" label-for="section_type">
      <i-custom-select
        label="label"
        field-id="section_type"
        :options="sectionTypes"
        :clearable="false"
        :reduce="type => type.id"
        @option:selected="
          $event.id === 'file' ? (fieldLabel = 'Attachment') : null,
            $event.id !== 'field' ? (field = null) : ''
        "
        v-model="sectionType"
      />
    </i-form-group>
    <i-form-group
      :label="$t('form.sections.message.message')"
      v-if="sectionType === 'message'"
    >
      <editor :with-image="false" v-model="message" />
    </i-form-group>
    <div v-else>
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
      <i-form-group
        v-if="sectionType === 'field'"
        :label="$t('fields.field')"
        label-for="field"
      >
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
      <i-form-group
        :label="$t('fields.label')"
        v-show="field !== null || sectionType === 'file'"
      >
        <editor
          :with-image="false"
          default-tag="div"
          :toolbar="editorToolbarSections"
          v-model="fieldLabel"
        />
      </i-form-group>
      <i-form-group>
        <i-form-checkbox
          id="new-is-required"
          name="new-is-required"
          v-model:checked="isRequired"
          :disabled="fieldMustBeRequired"
          v-show="field !== null || sectionType === 'file'"
          :label="$t('fields.is_required')"
        />

        <i-form-checkbox
          id="new-file-multiple"
          name="new-file-multiple"
          v-model:checked="fileAcceptMultiple"
          v-show="sectionType === 'file'"
          :label="$t('form.sections.file.multiple')"
        />
      </i-form-group>
    </div>

    <div class="space-x-2 text-right">
      <i-button size="sm" @click="removeSection" variant="white">
        {{ $t('app.cancel') }}
      </i-button>
      <i-button
        size="sm"
        @click="newSection"
        :disabled="saveIsDisabled"
        variant="secondary"
      >
        {{ $t('app.save') }}
      </i-button>
    </div>
  </i-card>
</template>
<script>
import Editor from '@/components/Editor'
import Section from './Section'
import InteractsWithFieldSection from './InteractsWithFieldSection'
export default {
  mixins: [Section, InteractsWithFieldSection],
  components: { Editor },
  data: () => ({
    sectionType: 'field',
    resourceName: 'contacts',
    fileAcceptMultiple: false,
    message: null,
  }),
  computed: {
    /**
     * Indicate whether the save button is disabled
     *
     * @return {Boolean}
     */
    saveIsDisabled() {
      if (this.sectionType === 'field') {
        return (
          this.fieldLabel === null ||
          this.fieldLabel == '' ||
          this.field == null
        )
      } else if (this.sectionType === 'message') {
        return this.message === null || this.message == ''
      } else if (this.sectionType === 'file') {
        return this.fieldLabel === null || this.fieldLabel == ''
      }
    },

    /**
     * Available section types the user can choose
     *
     * @return {Array}
     */
    sectionTypes() {
      return [
        {
          id: 'field',
          label: this.$t('form.sections.types.input_field'),
        },
        {
          id: 'message',
          label: this.$t('form.sections.types.message'),
        },
        {
          id: 'file',
          label: this.$t('form.sections.types.file'),
        },
      ]
    },
  },
  methods: {
    /**
     * Create new message section
     *
     * @return {Void}
     */
    newMessageSection() {
      this.createSection({
        type: 'message-section',
        message: this.message,
      })
    },

    /**
     * Create new field section
     *
     * @return {Void}
     */
    newFieldSection() {
      this.createSection({
        type: 'field-section',
        isRequired: this.isRequired,
        label: this.fieldLabel,
        resourceName: this.resourceName,
        attribute: this.field.attribute,
        requestAttribute: this.generateRequestAttribute(),
      })
    },

    /**
     * Create new file section
     *
     * @return {Void}
     */
    newFileSection() {
      this.createSection({
        type: 'file-section',
        isRequired: this.isRequired,
        label: this.fieldLabel,
        resourceName: this.resourceName,
        multiple: this.fileAcceptMultiple,
        requestAttribute: this.generateRequestAttribute(),
      })
    },

    /**
     * Create new section
     *
     * @return {Void}
     */
    newSection() {
      if (this.sectionType === 'message') {
        this.newMessageSection()
      } else if (this.sectionType === 'file') {
        this.newFileSection()
      } else {
        this.newFieldSection()
      }

      this.removeSection()
    },
  },
}
</script>
