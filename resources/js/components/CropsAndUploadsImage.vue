<template>
  <div>
    <div v-show="!edit" class="flex items-center space-x-4">
      <div v-if="hasImage">
        <slot name="image" :src="activeImageSrc">
          <i-avatar size="lg" class="mb-2" :src="activeImageSrc" />
        </slot>
      </div>
      <!-- NOTE, drop is set to false as it's causing memory leaks -->
      <!-- https://github.com/lian-yue/vue-upload-component/issues/294 -->
      <div class="flex space-x-2">
        <file-upload
          extensions="jpg,jpeg,png"
          accept="image/png,image/jpeg"
          :name="name"
          :input-id="name"
          :headers="{
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': CSRFToken,
          }"
          class="!flex cursor-pointer items-center rounded-full bg-primary-50 px-5 py-2 text-sm font-medium text-primary-800 hover:bg-primary-100 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2 focus:ring-offset-primary-50"
          :post-action="uploadUrl"
          :drop="false"
          v-model="tmpFile"
          @input-filter="inputFilter"
          @input-file="inputFile"
          ref="upload"
        >
          <icon
            icon="CursorClick"
            class="mr-2 h-4 w-4 text-current"
            v-if="!($refs.upload && $refs.upload.active)"
          />
          <i-spinner
            class="-mt-1 mr-2 inline-flex h-4 w-4 text-current"
            v-if="$refs.upload && $refs.upload.active"
          ></i-spinner>
          {{ chooseText }}
        </file-upload>
        <button
          v-if="hasImage && showDelete"
          @click="remove"
          type="button"
          class="inline-flex items-center rounded-full bg-danger-50 px-5 py-2 text-sm font-medium text-danger-800 hover:bg-danger-100 focus:outline-none focus:ring-2 focus:ring-danger-600 focus:ring-offset-2 focus:ring-offset-danger-50"
          v-t="'app.remove'"
        ></button>
      </div>
    </div>
    <div v-show="hasTemporaryFile && edit">
      <div class="flex space-x-1">
        <i-button type="submit" variant="secondary" @click="editSave">
          {{ saveText }}
        </i-button>
        <i-button variant="white" @click="() => $refs.upload.clear()">
          {{ cancelText }}
        </i-button>
      </div>
      <div
        class="mt-2 w-full overflow-hidden rounded-md"
        v-if="hasTemporaryFile"
      >
        <img ref="editedFile" :src="tmpFile[0].url" />
      </div>
    </div>
  </div>
</template>
<script>
import Cropper from 'cropperjs'
import 'cropperjs/dist/cropper.css'
import FileUpload from 'vue-upload-component'
import i18n from '@/i18n'
export default {
  emits: ['success', 'cleared'],
  components: { FileUpload },
  props: {
    cropperOptions: {
      type: Object,
      default: function () {
        return {}
      },
    },
    showDelete: {
      type: Boolean,
      default: true,
    },
    chooseText: {
      type: String,
      default: i18n.t('app.choose_image'),
    },
    saveText: {
      type: String,
      default: i18n.t('app.upload'),
    },
    cancelText: {
      type: String,
      default: i18n.t('app.cancel'),
    },
    image: String,
    uploadUrl: {
      type: String,
      required: true,
    },
    name: {
      type: String,
      default: 'image',
    },
  },
  data: () => ({
    defaultCropperOptions: {
      aspectRatio: 1 / 1,
      viewMode: 1,
    },
    tmpFile: [],
    CSRFToken: Innoclapps.csrfToken(),
    cropper: false,
    edit: false,
  }),
  watch: {
    edit(value) {
      if (value) {
        this.$nextTick(function () {
          if (!this.$refs.editedFile) {
            return
          }

          let options = Object.assign(
            {},
            this.defaultCropperOptions,
            this.cropperOptions
          )

          let cropper = new Cropper(this.$refs.editedFile, options)

          this.cropper = cropper
        })
      } else {
        if (this.cropper) {
          this.cropper.destroy()
          this.cropper = false
        }
      }
    },
  },
  computed: {
    hasImage() {
      return this.hasTemporaryFile || this.image
    },
    activeImageSrc() {
      return this.hasTemporaryFile ? this.tmpFile[0].url : this.image
    },
    hasTemporaryFile() {
      return this.tmpFile.length
    },
  },
  methods: {
    remove() {
      this.edit = false
      this.tmpFile = []
      this.$emit('cleared')
    },
    editSave() {
      this.edit = false
      let oldFile = this.tmpFile[0]
      let binStr = atob(
        this.cropper.getCroppedCanvas().toDataURL(oldFile.type).split(',')[1]
      )
      let arr = new Uint8Array(binStr.length)
      for (let i = 0; i < binStr.length; i++) {
        arr[i] = binStr.charCodeAt(i)
      }
      let file = new File([arr], oldFile.name, {
        type: oldFile.type,
      })
      this.$refs.upload.update(oldFile.id, {
        file,
        type: file.type,
        size: file.size,
        active: true,
      })
    },
    inputFile(newFile, oldFile, prevent) {
      if (newFile && !oldFile) {
        this.$nextTick(function () {
          this.edit = true
        })
      }
      if (!newFile && oldFile) {
        this.edit = false
      }

      if (newFile && oldFile) {
        // Uploaded
        if (newFile.success !== oldFile.success) {
          this.$emit('success', newFile.response)
        }

        // Error
        if (newFile.error !== oldFile.error) {
          // Nginx 413 Request Entity Too Large
          if (newFile.xhr.status === 413) {
            Innoclapps.error(this.$t('app.file_too_large'))
            this.tmpFile = []

            return
          }

          let response = JSON.parse(newFile.xhr.response)
          Innoclapps.error(response.message)
        }
      }
    },
    inputFilter(newFile, oldFile, prevent) {
      if (newFile && !oldFile) {
        if (!/\.(jpeg|png|jpg|gif|svg)$/i.test(newFile.name)) {
          Innoclapps.error(
            this.$t('validation.image', {
              attribute: newFile.name,
            })
          )

          return prevent()
        }
      }
      if (newFile && (!oldFile || newFile.file !== oldFile.file)) {
        newFile.url = ''
        let URL = window.URL || window.webkitURL
        if (URL && URL.createObjectURL) {
          newFile.url = URL.createObjectURL(newFile.file)
        }
      }
    },
  },
  beforeUnmount() {
    this.cropper && this.cropper.destroy()
  },
}
</script>
