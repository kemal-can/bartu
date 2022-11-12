<template>
  <!-- ref="wrapper" is used by FocusableFieldsGenerator -->
  <div
    v-bind="$attrs"
    :class="{ 'resize-y': isResizeable }"
    :style="{
      height: fieldsHeight ? `${fieldsHeight}px` : null,
    }"
    ref="wrapper"
  >
    <div class="grid grid-cols-12 gap-x-4">
      <component
        v-for="field in iterableFields"
        :key="field.attribute"
        :field="field"
        :view="view"
        :via-resource="viaResource"
        :via-resource-id="viaResourceId"
        :is-floating="isFloating"
        :form="form"
        :is="field.component"
      />
    </div>
  </div>
  <slot :fields="iterableFields" name="after"></slot>
</template>
<script>
import castArray from 'lodash/castArray'
import reject from 'lodash/reject'
import debounce from 'lodash/debounce'
const elementResizeEvent = require('element-resize-event')
const unbindElementResizeEvent = require('element-resize-event').unbind
import { singularize } from '@/utils'

export default {
  inheritAttrs: false,
  name: 'fields-generator',
  props: {
    resizeable: Boolean,
    // Required when resizeable
    resourceName: String,

    viaResource: String,
    viaResourceId: Number,

    except: [Array, String],
    only: [Array, String],

    view: { required: true, type: String },
    form: { required: true, type: Object },
    isFloating: { default: false, type: Boolean },
    fields: {
      required: true,
      type: [Array, Object],
      default() {
        return []
      },
    },
  },
  data: () => ({
    fieldsHeight: null,
  }),
  computed: {
    /**
     * Indicates whether the fields can be resized
     *
     * @return {Boolean}
     */
    isResizeable() {
      return this.resizeable && this.$gate.isSuperAdmin()
    },

    /**
     * Get the resource singular name
     *
     * @return {String}
     */
    resourceSingular() {
      return singularize(this.resourceName)
    },

    /**
     * Get the iterable fields
     *
     * @return {Array}
     */
    iterableFields() {
      let fields = this.isCollection ? this.fields.all() : this.fields

      if (this.only) {
        return reject(
          fields,
          field => castArray(this.only).indexOf(field.attribute) === -1
        )
      } else if (this.except) {
        return reject(
          fields,
          field => castArray(this.except).indexOf(field.attribute) > -1
        )
      }

      return fields
    },

    /**
     * Indicates whether the provided fields are in a collection
     *
     * @return {Boolean}
     */
    isCollection() {
      return !Array.isArray(this.fields)
    },
  },
  methods: {
    updateResourceFieldsHeight: debounce(function () {
      Innoclapps.request()
        .post('/settings', {
          [this.resourceSingular + '_fields_height']:
            this.$refs.wrapper.offsetHeight,
        })
        .then(() => (this.fieldsHeight = this.$refs.wrapper.offsetHeight))
    }, 500),
  },
  created() {
    if (this.isResizeable) {
      this.fieldsHeight = Innoclapps.config.fields.height[this.resourceSingular]
    }
  },
  mounted() {
    if (this.isResizeable) {
      this.$nextTick(() => {
        elementResizeEvent(this.$refs.wrapper, this.updateResourceFieldsHeight)
      })
    }
  },
  beforeUnmount() {
    if (this.isResizeable) {
      unbindElementResizeEvent(this.$refs.wrapper)
    }
  },
}
</script>
