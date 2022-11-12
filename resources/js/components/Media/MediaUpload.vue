<template>
  <div>
    <i-alert
      show
      variant="danger"
      class="mb-4"
      v-for="(error, index) in errors"
      :key="index"
    >
      {{ error }}
    </i-alert>

    <slot></slot>

    <media-upload-output-list
      v-if="showOutput"
      :files="files"
      @remove-requested="remove"
    />

    <div :class="[wrapperClasses, 'relative']">
      <slot name="drop-placeholder" :upload="$refs.upload">
        <div
          v-show="$refs.upload && $refs.upload.dropActive"
          class="absolute inset-0 z-10 flex items-center justify-center rounded-md bg-neutral-200"
          v-t="'app.drop_files'"
        ></div>
      </slot>

      <file-upload
        ref="upload"
        :headers="{
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': CSRFToken,
        }"
        :class="styleClasses"
        :disabled="$refs.upload && $refs.upload.active"
        :name="name"
        :multiple="multiple"
        :extensions="extensions"
        :accept="accept"
        :data="requestData"
        v-model="files"
        :drop="drop"
        :post-action="actionUrl"
        :input-id="inputId"
        @update:model-value="$emit('update:modelValue', $event)"
        @input-file="inputFile"
        @input-filter="inputFilter"
      >
        <icon
          :icon="icon"
          class="mr-2 h-4 w-4 text-current"
          v-if="icon && !($refs.upload && $refs.upload.active)"
        />
        <i-spinner
          class="-mt-1 mr-2 inline-flex h-4 w-4 text-current"
          v-if="$refs.upload && $refs.upload.active"
        ></i-spinner>
        <slot name="upload-text">
          {{ selectButtonUploadText }}
        </slot>
      </file-upload>
      <div class="ml-2 flex items-center space-x-2">
        <slot name="upload-button" :upload="$refs.upload">
          <button
            v-if="
              showUploadButton &&
              (!$refs.upload || (!$refs.upload.active && !automaticUpload))
            "
            @click="$refs.upload.active = true"
            :disabled="files.length === 0"
            type="button"
            class="inline-flex cursor-pointer items-center rounded-full bg-primary-50 px-5 py-2 text-sm font-medium text-primary-800 hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50"
          >
            <icon icon="CloudUpload" class="mr-2 h-4 w-4 text-current" />
            {{ uploadButtonText }}
          </button>
        </slot>
        <button
          v-show="allowCancel && $refs.upload && $refs.upload.active"
          @click="$refs.upload.active = false"
          type="button"
          class="inline-flex items-center rounded-full bg-danger-50 px-5 py-2 text-sm font-medium text-danger-800 hover:bg-danger-100 focus:outline-none focus:ring-2 focus:ring-danger-600 focus:ring-offset-2 focus:ring-offset-danger-50"
          v-t="'app.cancel'"
        ></button>
        <button
          v-show="files.length > 0 && (!$refs.upload || !$refs.upload.active)"
          @click="clear"
          type="button"
          class="inline-flex items-center rounded-full bg-neutral-50 px-5 py-2 text-sm font-medium text-neutral-800 hover:bg-neutral-100 focus:outline-none focus:ring-2 focus:ring-neutral-600 focus:ring-offset-2 focus:ring-offset-neutral-50"
          v-t="'app.clear'"
        ></button>
      </div>
    </div>
  </div>
</template>
<script>
import FileUpload from 'vue-upload-component'
import findIndex from 'lodash/findIndex'
import filter from 'lodash/filter'
import MediaUploadOutputList from './MediaUploadOutputList'
export default {
  emits: ['update:modelValue', 'file-accepted', 'file-uploaded', 'clear'],
  components: {
    FileUpload,
    MediaUploadOutputList,
  },
  props: {
    modelValue: {},
    icon: {
      type: [String, Boolean],
      default: 'CursorClick',
    },
    inputId: {
      default: 'media',
      type: String,
    },
    requestData: {
      type: Object,
      default() {
        return {}
      },
    },
    actionUrl: String,
    // NOTE, drop is set to false as it's causing memory leaks
    // https://github.com/lian-yue/vue-upload-component/issues/294
    drop: {
      type: Boolean,
      default: false,
    },
    name: {
      default: 'file',
      type: String,
    },
    extensions: [Array, String],
    accept: {
      type: String,
      default: undefined,
    },
    uploadText: String,
    selectFileText: String,
    allowCancel: {
      type: Boolean,
      default: true,
    },
    showUploadButton: {
      type: Boolean,
      default: true,
    },
    showOutput: {
      default: true,
      type: Boolean,
    },
    automaticUpload: {
      default: true,
      type: Boolean,
    },
    multiple: {
      default: true,
      type: Boolean,
    },
    styleClasses: {
      type: [Object, Array, String],
      default:
        '!flex items-center rounded-full px-5 py-2 text-sm font-medium bg-primary-50 text-primary-800 hover:bg-primary-100 focus:ring-offset-primary-50 focus:ring-primary-600 focus:outline-none focus:ring-2 focus:ring-offset-2 cursor-pointer',
    },
    wrapperClasses: {
      type: [Object, Array, String],
      default: 'flex items-center',
    },
  },
  data: () => ({
    files: [],
    errors: [],
    CSRFToken: Innoclapps.csrfToken(),
  }),
  computed: {
    uploadButtonText() {
      return this.uploadText || this.$t('app.upload')
    },
    selectButtonUploadText() {
      return this.selectFileText || this.$t('app.select_file')
    },
    totalFilesReadyForUpload() {
      return filter(this.files, file => {
        return !file.error
      }).length
    },
  },
  methods: {
    /**
     * Handle the response
     *
     * @param  {Object} xhr
     *
     * @return {Void}
     */
    handleResponse(xhr) {
      // Nginx 413 Request Entity Too Large
      if (xhr.status === 413) {
        Innoclapps.error(this.$t('app.file_too_large'))

        return
      }

      let response = JSON.parse(xhr.response)
      let isSuccess = xhr.status < 400

      if (response.message) {
        if (isSuccess) {
          Innoclapps.success(response.message)
        } else {
          Innoclapps.error(response.message)
        }
      }

      if (xhr.status === 422) {
        this.errors = response.errors
      }

      return response
    },

    /**
     * Remove file from the queue
     *
     * @param  {Int} index
     *
     * @return {Void}
     */
    remove(index) {
      this.files.splice(index, 1)
      this.$emit('update:modelValue', this.files)
    },

    /**
     * Clear the upload component
     * @return {Void}
     */
    clear() {
      this.$refs.upload.clear()
      this.errors = []
      this.$emit('clear')
    },

    /**
     * Validate the provided extensions
     *
     * @param  {object} file
     *
     * @return {Boolean}
     */
    validateExtensions(file) {
      if (!this.extensions) {
        return true
      }

      let validateExtensions = this.extensions

      if (typeof validateExtensions == 'array') {
        validateExtensions = validateExtensions.join('|')
      } else if (typeof validateExtensions == 'string') {
        validateExtensions = validateExtensions.replace(',', '|')
      }

      var regex = RegExp('.(' + validateExtensions + ')', 'i')

      if (!regex.test(file.name)) {
        Innoclapps.error(
          this.$t('validation.mimes', {
            attribute: this.$t('app.file').toLowerCase(),
            values: [validateExtensions],
          })
        )

        return false
      }

      return true
    },
    isNewFile(newFile, oldFile) {
      return newFile && !oldFile
    },
    isUpdatedFile(newFile, oldFile) {
      return newFile && oldFile
    },
    shouldStartUpload(newFile, oldFile) {
      return newFile.active !== oldFile.active
    },
    /**
     * A file change detected
     * @param  {Object} newFile
     * @param  {Object} oldFile
     * @return {Void}
     */
    inputFile(newFile, oldFile) {
      if (this.isNewFile(newFile, oldFile)) {
        // console.log('add file')
        // Add file
      }

      if (this.isUpdatedFile(newFile, oldFile)) {
        // Update file
        // console.log('update file')
        // Start upload
        if (this.shouldStartUpload(newFile, oldFile)) {
          // console.log('Start upload', newFile.active, newFile)
        }

        // Upload progress
        if (newFile.progress !== oldFile.progress) {
          // console.log('progress', newFile.progress, newFile)
        }

        // Upload error
        if (newFile.error !== oldFile.error) {
          if (newFile.xhr.response /* perhaps canceled */) {
            this.handleResponse(newFile.xhr)
          }
        }

        // Uploaded successfully
        if (newFile.success !== oldFile.success) {
          // console.log('success', newFile.success, newFile)
          this.$emit('file-uploaded', this.handleResponse(newFile.xhr))
          this.remove(findIndex(this.files, ['name', newFile.name]))
        }
      }

      if (!this.automaticUpload) {
        return
      }

      if (
        Boolean(newFile) !== Boolean(oldFile) ||
        oldFile.error !== newFile.error
      ) {
        if (!this.$refs.upload.active && newFile && !newFile.xhr) {
          // console.log('Automatic upload')
          this.$refs.upload.active = true
        }
      }
    },

    /**
     * Pretreatment
     * @param  Object|undefined   newFile   Read and write
     * @param  Object|undefined   oldFile   Read only
     * @param  Function           prevent   Prevent changing
     * @return undefined
     */
    inputFilter: function (newFile, oldFile, prevent) {
      if (newFile && !oldFile) {
        // Extentesion validator
        if (!this.validateExtensions(newFile)) {
          return prevent()
        }

        // File size validator
        if (
          newFile.size >= 0 &&
          newFile.size > Innoclapps.config.max_upload_size
        ) {
          Innoclapps.error('File too big')
          return prevent()
        }

        newFile.blob = ''
        let URL = window.URL || window.webkitURL
        if (URL && URL.createObjectURL) {
          newFile.blob = URL.createObjectURL(newFile.file)
        }

        // this.file = newFile
        this.$nextTick(() => this.$emit('file-accepted', newFile))
      }
    },
  },
}
</script>
