<template>
  <i-form-group>
    <media-upload
      :drop="true"
      :multiple="section.multiple"
      v-model="files"
      :show-upload-button="false"
      :automatic-upload="false"
      :icon="false"
      :name="section.requestAttribute"
      :input-id="section.requestAttribute"
      style-classes="group block rounded-md border border-dashed border-neutral-300 dark:border-neutral-400 w-full py-4 sm:py-5 hover:border-neutral-400 cursor-pointer hover:bg-neutral-50 dark:hover:bg-neutral-700/60 font-medium"
      wrapper-classes=""
      @update:model-value="hydrateFormWithFiles"
    >
      <template #upload-text>
        <div class="flex flex-col items-center">
          <icon
            icon="CloudUpload"
            class="h-6 w-6 text-neutral-600 dark:text-neutral-300 dark:group-hover:text-white"
          />
          <p
            class="mt-1 text-sm text-neutral-700 dark:text-neutral-100 dark:group-hover:text-white"
            v-html="section.label"
          />
        </div>
      </template>
    </media-upload>
    <form-error :form="form" :field="section.requestAttribute" />
  </i-form-group>
</template>
<script>
import Section from './Section'
import MediaUpload from '@/components/Media/MediaUpload'
import each from 'lodash/each'
export default {
  components: { MediaUpload },
  mixins: [Section],
  data: () => ({
    files: [],
  }),
  methods: {
    /**
     * Hydrate the form with files
     *
     * @param  {Object} files
     *
     * @return {Void}
     */
    hydrateFormWithFiles(files) {
      if (files.length === 0) {
        this.form.fill(
          this.section.requestAttribute,
          this.section.multiple ? [] : null
        )
        return
      }

      if (this.section.multiple) {
        let fileInstances = []
        each(files, file => fileInstances.push(file.file))
        this.form.fill(this.section.requestAttribute, fileInstances)
      } else {
        this.form.fill(this.section.requestAttribute, files[0].file)
      }
    },
  },
  created() {
    this.form.set(
      this.section.requestAttribute,
      this.section.multiple ? [] : null
    )
  },
}
</script>
