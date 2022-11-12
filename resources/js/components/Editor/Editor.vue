<template>
  <i-overlay :show="!visible">
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
import map from 'lodash/map'
import reject from 'lodash/reject'
import pick from 'lodash/pick'
import find from 'lodash/find'
import { randomString, getLocale } from '@/utils'
export default {
  emits: ['update:modelValue', 'input', 'init'],
  name: 'tinymce',
  components: {
    editor: Editor,
  },
  props: {
    modelValue: {},
    placeholder: { type: String, default: '' },
    disabled: { type: Boolean, default: false },
    defaultTag: { type: String, default: 'p' },
    withImage: { default: true, type: Boolean },
    withMention: { default: false, type: Boolean },
    toolbar: String,
    config: Object,
    plugins: [Array, String],
  },
  watch: {
    internalContent: function (newVal) {
      if (newVal != this.modelValue) {
        this.$emit('update:modelValue', newVal)
        this.$emit('input', newVal)
      }
    },
    modelValue: {
      handler: function (newVal) {
        if (newVal != this.internalContent) {
          // When the newVal is null and there is content in the editor, TinymCE won't trigger the update
          // because expect the value to be string in order to trigger reactivity to update the editor content
          this.internalContent = newVal || ''
        }
      },
      immediate: true,
    },
  },
  data() {
    return {
      isDarkMode: false,
      visible: false,
      imagesDraftId: null,
      timeoutClear: null,
      internalContent: null,
      editor: null,
      defaultConfig: {
        menubar: false,
        visual: false,
        statusbar: false,
        width: '100%',
        height: '200px',
        contextmenu: false,
        branding: false,
        forced_root_block: this.defaultTag,
        images_upload_handler: this.handleImageUpload,
        language: getLocale(),
        automatic_uploads: true,
        images_reuse_filename: true,
        paste_data_images: this.withImage,
        relative_urls: false,
        remove_script_host: false,
        placeholder: this.placeholder,
        plugins:
          this.plugins ||
          [
            'lists',
            'autolink',
            'link',
            'autoresize',
            this.withImage ? 'image' : '',
          ].filter(plugin => plugin !== ''),
        toolbar:
          this.toolbar ||
          `
                      blocks |
                      bold italic underline strikethrough |
                      forecolor backcolor |
                      link${this.withImage ? ' image' : ''} |
                      alignment | bullist numlist | removeformat
                  `,
        init_instance_callback: editor => {
          //
        },
        setup: editor => {
          if (this.withMention) {
            editor.concordCommands = {}
            this.initializeMentions(editor)
          }

          editor.on('init', e => {
            this.editor = e.target
            this.$emit('init')
          })

          // Not visible on mobile as the group toolbar buttons are supporting only on floating type toolbar
          editor.ui.registry.addGroupToolbarButton('alignment', {
            icon: 'align-left',
            tooltip: tinymce.util.I18n.translate('Alignment'),
            items: 'alignleft aligncenter alignright | alignjustify',
          })
        },
        content_style: `
                      .mention {
                          color: #212529;
                          background-color: #f4f5f7;
                          height: 24px;
                          width: 65px;
                          border-radius: 6px;
                          padding: 3px 3px;
                          margin-right: 2px;
                          -webkit-user-select: all;
                          -moz-user-select: all;
                          -ms-user-select: all;
                          user-select: all;
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
      let config = !this.config
        ? this.defaultConfig
        : Object.assign({}, this.defaultConfig, this.config)

      if (this.isDarkMode) {
        config.skin = 'oxide-dark'
        config.content_css = 'dark'
      }

      return config
    },

    /**
     * Users available for mentioning
     *
     * Excludes the logged in user as cannot mention himself
     *
     * @return {Array}
     */
    usersAvailableForMentioning() {
      return reject(
        map(this.$store.state.users.collection, user =>
          pick(user, ['id', 'name'])
        ),
        user => user.id == this.currentUser.id
      )
    },
  },
  methods: {
    /**
     * Initialize mentions for the editor
     *
     * @param  {TinyMCE} editor
     *
     * @return {Void}
     */
    initializeMentions(editor) {
      // Does not work with arrow based closure
      const self = this

      editor.concordCommands.insertMentionUser = function (id, name, rng) {
        // Insert in to the editor
        editor.selection.setRng(rng || 0)

        editor.insertContent(`<span class="mention"
                      data-mention-id="${id}"
                      contenteditable="false"
                      data-notified="false"><span data-mention-char>@</span><span data-mention-value>${name}</span>
                      </span> `)
      }

      editor.ui.registry.addAutocompleter('mentions', {
        ch: '@', // the trigger character to open the autocompleter
        minChars: 0, // 0 to open the dropdown immediately after the @ is typed
        columns: 1, // must be 1 for text-based results
        // Retrieve the available users
        fetch: function (pattern) {
          return new Promise(resolve =>
            resolve(
              map(self.usersAvailableForMentioning, user => ({
                value: user.id.toString(),
                text: user.name,
              }))
            )
          )
        },

        // Executed when user is selected from the dropdown
        onAction: function (autocompleteApi, rng, value) {
          // Find the selected user via the user id
          let user = find(self.usersAvailableForMentioning, [
            'id',
            parseInt(value),
          ])
          editor.concordCommands.insertMentionUser(value, user.name, rng)
          // Hide the autocompleter
          autocompleteApi.hide()
        },
      })
    },

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
     *
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
  created() {
    this.isDarkMode = document.documentElement.classList.contains('dark')
  },
  mounted() {
    // https://github.com/tinymce/tinymce-vue/issues/230
    this.timeoutClear = setTimeout(() => (this.visible = true), 250)
  },
  beforeMount() {
    this.imagesDraftId = randomString(10)
  },
  beforeUnmount() {
    this.timeoutClear && clearTimeout(this.timeoutClear)
  },
}
</script>
