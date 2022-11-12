<template>
  <i-overlay :show="!visible">
    <placeholders
      :placeholders="placeholders"
      v-model:visible="placeholdersVisible"
      @inserted="$emit('placeholder-inserted')"
      v-if="placeholders && componentReady"
    />
    <editor
      v-if="visible"
      v-model="internalContent"
      :disabled="disabled"
      :init="editorConfig"
    />
  </i-overlay>
</template>
<script>
import Editor from '@tinymce/tinymce-vue'
import Placeholders from './Placeholders'
import debounce from 'lodash/debounce'
import { randomString, getLocale } from '@/utils'
export default {
  emits: ['update:modelValue', 'init', 'placeholder-inserted'],
  name: 'mail-editor',
  components: {
    editor: Editor,
    Placeholders,
  },
  props: {
    modelValue: {},
    placeholders: Object,
    placeholdersDisabled: Boolean,
    placeholder: {
      type: String,
      default: '',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
    withDrop: {
      default: false,
      type: Boolean,
    },
  },
  watch: {
    internalContent: function (newVal) {
      if (newVal != this.modelValue) {
        this.$emit('update:modelValue', newVal)
      }
    },
    modelValue: {
      handler: function (newVal) {
        if (newVal != this.internalContent) {
          this.internalContent = newVal
        }
      },
      immediate: true,
    },
  },
  data() {
    return {
      visible: false,
      imagesDraftId: null,
      placeholdersVisible: false,
      componentReady: false,
      clearTimeout: null,
      internalContent: null,
      editor: null,
      defaultConfig: {
        menubar: false,
        body_class: this.placeholdersDisabled ? 'placeholders-disabled' : '',
        height: '200px',
        visual: false,
        statusbar: false,
        contextmenu: false,
        branding: false,
        // images_upload_handler: this.handleImageUpload,
        language: getLocale(),
        automatic_uploads: true,
        images_reuse_filename: true,
        paste_data_images: this.withDrop,
        relative_urls: false,
        remove_script_host: false,
        placeholder: this.placeholder,
        browser_spellcheck: true,

        content_style: `
                    ._placeholder {
                        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
                        border: 0.0625rem solid #cad1d7;
                        min-width: 120px;
                        display: inline-block;
                        height: 29px;
                        border-radius: 3px;
                        line-height: 25px;
                        padding-right: 0.7rem;
                        padding-left: 0.7rem;
                    }

                    ._placeholder:focus {
                        border: 0.0625rem solid #93b3cf;
                        outline:0;
                    }

                    ._placeholder[data-autofilled] {
                        background-color: #f2f9ff;
                    }

                    .placeholders-disabled ._placeholder {
                        pointer-events: none !important;
                        cursor:not-allowed !important;
                        background-color: #f4f5f7 !important;
                        opacity:0.8;
                    }
                    `,

        block_formats: `${tinymce.util.I18n.translate(
          'Paragraph'
        )}=p; ${tinymce.util.I18n.translate(
          'Heading 1'
        )}=h1; ${tinymce.util.I18n.translate(
          'Heading 2'
        )}=h2; ${tinymce.util.I18n.translate(
          'Heading 3'
        )}=h3;  ${tinymce.util.I18n.translate('Heading 4')}=h4`,

        setup: editor => {
          const self = this

          // Not visible on mobile as the group toolbar buttons are supporting only on floating type toolbar
          editor.ui.registry.addGroupToolbarButton('alignment', {
            icon: 'align-left',
            tooltip: tinymce.util.I18n.translate('Alignment'),
            items: 'alignleft aligncenter alignright | alignjustify',
          })

          if (this.placeholders) {
            editor.ui.registry.addButton('fields', {
              text: this.$t('fields.fields'),
              onAction: _ => (self.placeholdersVisible = true),
            })
          }

          editor.on('init', e => {
            self.editor = e.target
            self.componentReady = true
            self.$emit('init')
          })

          editor.on(
            'input',
            debounce(function (e) {
              // When first time user tries to add data on the field
              // check if the placeholders are disabled, if yes, clear the value
              // and add the attribute disabled, as we are not able to add the disabled attribute before this action
              // if the disabled attribute is not added, the user will be able still to type regardless if
              // the cursor and pointer events are not allowed with CSS because of how tinymce works probably
              if (
                e.target.classList.contains('_placeholder') &&
                self.placeholdersDisabled
              ) {
                e.target.disabled = true
                e.target.value = ''
              }

              e.currentTarget &&
                e.currentTarget
                  .querySelectorAll('._placeholder')
                  .forEach(input => input.setAttribute('value', input.value))
            }),
            1000
          )
        },
        plugins: [
          'advlist',
          'lists',
          'autolink',
          'link',
          'image',
          'media',
          'table',
          'autoresize',
        ],
        toolbar: `fields | blocks |
                              bold italic underline strikethrough |
                              forecolor backcolor |
                              link table |
                              alignment |
                              bullist numlist |
                              blockquote removeformat undo redo`,
      },
    }
  },
  computed: {
    /**
     * Get the editor configuration
     *
     * @return {Object}
     */
    editorConfig() {
      let config = this.defaultConfig

      if (document.documentElement.classList.contains('dark')) {
        config.skin = 'oxide-dark'
        config.content_css = 'dark'
      }

      return config
    },
  },
  methods: {
    /**
     * Handle image upload
     */
    handleImageUpload(blobInfo, progress) {
      const file = blobInfo.blob()

      // file type is only image.
      if (!/^image\//.test(file.type)) {
        failure(
          this.$t('validation.image', {
            attribute: file.name,
          }),
          {
            remove: true,
          }
        )
        return
      }

      return new Promise((resolve, reject) => {
        const fd = new FormData()
        fd.append('file', file)

        Innoclapps.request()
          .post('/media/pending/' + this.imagesDraftId, fd)
          .then(({ data }) => resolve(data.preview_url))
          .catch(error => {
            // Nginx 413 Request Entity Too Large
            let message =
              error.message && error.message.includes('413')
                ? this.$t('app.file_too_large')
                : error.response.data.message
            reject({ message: message, remove: true })
          })
      })
    },

    /**
     * Set editor content
     * Usage: for form reset
     *
     * @param {String} content
     */
    setContent(content) {
      this.internalContent = content
    },

    /**
     * Clear the editor content
     *
     * @return {Void}
     */
    clearContent() {
      this.setContent('')
    },

    /**
     * Focus the editor
     *
     * @return {Void}
     */
    focus() {
      this.editor.focus()
    },
  },
  mounted() {
    // https://github.com/tinymce/tinymce-vue/issues/230
    this.clearTimeout = setTimeout(() => (this.visible = true), 250)
  },
  beforeMount() {
    this.imagesDraftId = randomString(10)
  },
  beforeUnmount() {
    this.clearTimeout && clearTimeout(this.clearTimeout)
  },
}
</script>
