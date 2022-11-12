<template>
  <i-card no-body class="mh-96">
    <template #header>
      <i-card-heading class="text-base">{{ heading }}</i-card-heading>
    </template>
    <template #actions>
      <div class="flex h-0.5 items-center">
        <i-button
          class="mr-2"
          size="sm"
          :loading="saving"
          :disabled="requestInProgress"
          v-show="fieldsVisible"
          @click="submit(true)"
        >
          {{ $t('app.save') }}
        </i-button>
        <i-button
          variant="white"
          @click="reset"
          class="mr-2"
          :loading="resetting"
          v-show="fieldsVisible"
          :disabled="requestInProgress"
          size="sm"
        >
          {{ $t('app.reset') }}
        </i-button>
        <i-button
          variant="white"
          size="sm"
          v-show="fieldsVisible"
          :loading="fetching"
          icon="ChevronUp"
          :disabled="requestInProgress"
          @click="toggle"
        />
      </div>
    </template>
    <div
      class="cursor-pointer border-b border-neutral-200 px-4 py-4 dark:border-neutral-800 sm:px-6"
    >
      <div class="lg:flex lg:items-center lg:justify-between">
        <p
          class="text-sm text-neutral-600 dark:text-white"
          @click="toggle"
          v-text="subHeading"
        />
        <i-button
          variant="white"
          v-show="!fieldsVisible"
          @click="toggle"
          size="sm"
          class="mt-4 shrink-0 lg:mt-0 lg:ml-5"
          >{{ $t('fields.manage') }}</i-button
        >
      </div>
      <div class="mb-0 mt-3" v-show="fieldsVisible">
        <input-search v-model="search" />
      </div>
    </div>

    <ul class="max-h-96 overflow-y-auto" v-show="fieldsVisible">
      <draggable
        v-bind="scrollableDraggableOptions"
        :list="filteredFields"
        :item-key="item => view + ' - ' + item.attribute"
        handle=".field-draggable-handle"
        :group="view"
      >
        <template #item="{ element }">
          <li
            class="border-b border-neutral-200 px-4 py-4 dark:border-neutral-700 sm:px-6"
            :class="{
              'bg-neutral-50 dark:bg-neutral-700/60': element.primary,
              'opacity-60': !element[visibilityKey],
            }"
          >
            <div class="flex items-center">
              <div class="grow">
                <div class="space-x-2">
                  <span
                    class="text-sm font-medium text-neutral-800 dark:text-white"
                  >
                    {{ element.label }}
                  </span>
                  <i-badge
                    variant="info"
                    v-show="element.customField"
                    v-t="'fields.custom.field'"
                  />
                  <i-badge
                    variant="warning"
                    v-show="element.readonly"
                    v-t="'fields.is_readonly'"
                  />
                </div>
                <span
                  v-show="element.helpText"
                  class="text-xs text-neutral-800 dark:text-white"
                  ><br />{{ element.helpText }}
                </span>
                <span
                  v-if="element.primary"
                  class="text-xs font-medium text-info-600 dark:text-white"
                  ><br />{{ $t('fields.primary') }}</span
                >
              </div>
              <div class="flex space-x-3">
                <i-button-icon
                  icon="PencilAlt"
                  v-if="element.customField || element.optionsViaResource"
                  @click="requestEdit(element)"
                />
                <i-button-icon
                  v-if="element.customField"
                  icon="Trash"
                  @click="requestDelete(element.customField.id)"
                />
                <i-button-icon
                  icon="Selector"
                  class="field-draggable-handle cursor-move"
                />
              </div>
            </div>
            <div v-if="!element.primary" class="mt-3">
              <i-form-checkbox
                :disabled="element.isRequired"
                v-model:checked="element[visibilityKey]"
                :label="$t('fields.visible')"
              />
              <i-form-checkbox
                v-if="collapseOption"
                v-model:checked="element.collapsed"
                :label="$t('fields.collapsed_by_default')"
              />
              <i-form-checkbox
                v-if="!element.isPrimary && !element.readonly"
                @change="$event ? (element[visibilityKey] = true) : ''"
                v-model:checked="element.isRequired"
                :label="$t('fields.is_required')"
              />
            </div>
          </li>
        </template>
      </draggable>
    </ul>
  </i-card>
</template>
<script>
import ProvidesDraggableOptions from '@/mixins/ProvidesDraggableOptions'
import draggable from 'vuedraggable'

export default {
  emits: ['delete-requested', 'update-requested'],
  mixins: [ProvidesDraggableOptions],
  components: { draggable },
  props: {
    group: { required: true, type: String },
    view: { required: true, type: String },
    heading: { type: String, required: true },
    subHeading: { type: String, required: true },
    collapseOption: { default: true, type: Boolean },
    lazy: { default: true, type: Boolean },
  },
  watch: {
    group: function (newVal, oldVal) {
      this.fetch()
    },
  },
  data() {
    return {
      search: null,
      fieldsLoaded: false,
      fieldsVisible: false,
      fields: [],
      saving: false,
      resetting: false,
      fetching: false,
    }
  },
  computed: {
    /**
     * Indicates whether there is a request in progress
     *
     * @return {Boolean}
     */
    requestInProgress() {
      return this.saving || this.resetting || this.fetching
    },

    /**
     * Get the total filtered fields
     *
     * @return {Number}
     */
    totalFilteredFields() {
      return this.filteredFields.length
    },

    /**
     * Filtered fields
     *
     * @type {Array}
     */
    filteredFields: {
      set(value) {
        this.fields = value.map(field => {
          if (field.isRequired) {
            field[this.visibilityKey] = true
          }

          return field
        })
      },
      get() {
        if (this.search) {
          return this.fields.filter(field =>
            field.label.toLowerCase().includes(this.search.toLowerCase())
          )
        }

        return this.fields
      },
    },

    /**
     * Get the save request uri
     *
     * @return {String}
     */
    requestUri() {
      return '/fields/settings/' + this.group + '/' + this.view
    },

    /**
     * Get the request data for save
     *
     * @return {Object}
     */
    requestData() {
      let data = {}
      this.fields.forEach((field, index) => {
        let fieldObject = {
          order: index + 1,
          [this.visibilityKey]: field.isRequired
            ? true
            : field[this.visibilityKey],
          isRequired: field.isRequired,
        }

        if (this.collapseOption) {
          fieldObject.collapsed = field.collapsed
        }

        data[field.attribute] = fieldObject
      })

      return data
    },

    /**
     * Get the fields visibility key
     *
     * @return {String}
     */
    visibilityKey() {
      if (this.view === config.fields.views.create) {
        return 'showOnCreation'
      } else if (this.view === config.fields.views.update) {
        return 'showOnUpdate'
      } else {
        return 'showOnDetail'
      }
    },
  },
  methods: {
    /**
     * Request field edit
     *
     * @param  {Object} field
     *
     * @return {Void}
     */
    requestEdit(field) {
      this.$emit('update-requested', field)
    },

    /**
     * Request delete for custom field
     *
     * @param  {Int} id
     *
     * @return {Void}
     */
    requestDelete(id) {
      this.$emit('delete-requested', id)
    },

    /**
     * Save the field settings
     *
     * @param {Boolean} userAction
     *
     * @return {Void}
     */
    submit(userAction) {
      this.saving = true
      Innoclapps.request()
        .post(this.requestUri, this.requestData, {
          params: {
            intent: this.view,
          },
        })
        .then(({ data }) => {
          this.resetStoreState()

          if (userAction) {
            Innoclapps.success(this.$t('fields.configured'))
          }
        })
        .finally(() => (this.saving = false))
    },

    /**
     * The the fields group visibility
     *
     * @return {Void}
     */
    toggle() {
      if (this.lazy && this.fieldsLoaded === false) {
        this.fetch()
      }

      this.fieldsVisible = !this.fieldsVisible
    },

    /**
     * Reset the fields group
     *
     * @return {Void}
     */
    reset() {
      this.resetting = true
      Innoclapps.request()
        .delete(this.requestUri + '/reset', {
          params: {
            intent: this.view,
          },
        })
        .then(({ data }) => {
          this.filteredFields = data.settings
          this.resetStoreState()
          Innoclapps.success(this.$t('fields.reseted'))
        })
        .finally(() => (this.resetting = false))
    },

    /**
     * Fetch the fields group
     *
     * @return {Promise}
     */
    async fetch() {
      this.fetching = true
      let { data } = await Innoclapps.request().get(this.requestUri, {
        params: {
          intent: this.view,
        },
      })

      this.fetching = false
      this.filteredFields = data
      this.fieldsLoaded = true
    },
  },
  created() {
    if (!this.lazy) {
      this.fetch()
    }

    if (this.$route.query.view && this.$route.query.view === this.view) {
      this.toggle()
    }
  },
}
</script>
