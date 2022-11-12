<template>
  <i-form-group
    :label="$t('fields.custom.type')"
    label-for="field_type"
    required
  >
    <i-custom-select
      :options="fieldsTypes"
      :clearable="false"
      :disabled="edit"
      v-model="form.field_type"
    />
    <form-error :form="form" field="field_type" />
  </i-form-group>
  <i-form-group label-for="label" :label="$t('fields.label')" required>
    <i-form-input v-model="form.label" id="label" />
    <form-error :form="form" field="label" />
  </i-form-group>
  <field-options v-if="isOptionableField" :form="form" />

  <i-form-group :description="$t('fields.custom.id_info')">
    <template #label>
      <div class="flex">
        <i-form-label
          required
          class="mb-1 grow"
          for="field_id"
          :label="$t('fields.custom.id')"
        />

        <copy-button
          v-if="edit"
          :text="form.field_id"
          :with-tooltip="false"
          tabindex="-1"
          class="link cursor-pointer text-sm"
          tag="a"
        >
          {{ $t('app.copy') }}
        </copy-button>
      </div>
    </template>

    <div class="relative">
      <div
        v-if="!edit"
        class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm dark:text-white"
      >
        {{ idPrefix }}
      </div>

      <i-form-input
        v-model="fieldId"
        :disabled="edit"
        id="field_id"
        :class="{ 'pl-8': !edit }"
      />
    </div>

    <form-error :form="form" field="field_id" />
  </i-form-group>
</template>
<script>
import FieldOptions from './CustomFieldOptions'
import debounce from 'lodash/debounce'
export default {
  components: { FieldOptions },
  props: {
    form: {
      required: true,
      type: Object,
    },
    edit: {
      default: false,
      type: Boolean,
    },
  },
  data: () => ({
    optionableFields: Innoclapps.config.fields.optionables,
    fieldsTypes: Innoclapps.config.fields.custom_fields_types,
    idPrefix: Innoclapps.config.fields.custom_field_prefix,
    fieldId: null,
  }),
  watch: {
    fieldId: debounce(function (newVal) {
      if (!this.edit) {
        this.form.fill('field_id', newVal ? `${this.idPrefix}${newVal}` : null)
      }
    }, 250),
  },
  computed: {
    /**
     * Indicates whether the current custom field
     * is field with options
     *
     * @return {Boolean}
     */
    isOptionableField() {
      return this.optionableFields.indexOf(this.form.field_type) > -1
    },
  },
  mounted() {
    if (this.form.field_id) {
      this.fieldId = this.form.field_id
    }
  },
}
</script>
