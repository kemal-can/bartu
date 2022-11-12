<template>
  <!-- We will add custom option.parent_id in the label as
              if the folder name is duplicated the i-custom-select addon won't work properly
              because i-custom-select determines uniquness via the label.
              In thi case, we will provide custom function for getOptionLabel and will format
              the actual labels separtely via slots -->
  <i-form-group :label="label" :required="required">
    <i-custom-select
      :clearable="false"
      :options="folders"
      @update:modelValue="$emit('update:modelValue', $event)"
      :option-label-provider="
        option => '--' + option.parent_id + '--' + option.display_name
      "
      :reduce="folder => folder.id"
      :model-value="modelValue"
    >
      <template #option="option">
        {{ option.display_name.replace('--' + option.parent_id + '--', '') }}
      </template>
      <template #selected-option="option">
        {{ option.display_name.replace('--' + option.parent_id + '--', '') }}
      </template>
    </i-custom-select>
    <form-error :form="form" :field="field" />
  </i-form-group>
</template>
<script>
export default {
  emits: ['update:modelValue'],
  props: ['modelValue', 'form', 'field', 'folders', 'label', 'required'],
}
</script>
