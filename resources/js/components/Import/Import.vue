<template>
  <i-card no-body>
    <template #header>
      <slot name="header">
        <i-card-heading>{{ header }}</i-card-heading>
      </slot>
    </template>
    <template #actions>
      <i-button
        variant="white"
        @click="downloadSample(`/${resourceName}/import/sample`)"
        size="sm"
        >{{ $t('import.download_sample') }}</i-button
      >
    </template>

    <import-steps :steps="steps" />

    <i-card-body>
      <media-upload
        ref="media"
        class="ml-5"
        :action-url="uploadUrl"
        extensions="csv"
        @file-uploaded="handleFileUploaded"
        :multiple="false"
        :show-output="false"
        :upload-text="$t('import.start')"
      />

      <i-alert
        show
        variant="danger"
        class="mt-5"
        v-for="(error, index) in errors"
        :key="index"
      >
        {{ error }}
      </i-alert>

      <div class="mt-5 text-sm" v-if="importInstance">
        <h3
          class="mb-3 text-lg font-medium text-neutral-700 dark:text-neutral-200"
          v-t="'import.spreadsheet_columns'"
        />
        <div class="flex">
          <div class="w-1/2">
            <div
              v-for="(column, index) in importInstance.mappings"
              :key="'mapping-' + index"
              :class="[
                {
                  'bg-neutral-100 dark:bg-neutral-800': !column.attribute,
                  'bg-white dark:bg-neutral-700': column.attribute,
                },
                'mb-2 mr-3 flex h-16 flex-col justify-center rounded border border-neutral-300 px-4 dark:border-neutral-500',
              ]"
            >
              <i-form-label :required="isColumnRequired(column)"
                >{{ column.original }}
                <span
                  v-if="column.skip && !isColumnRequired(column)"
                  class="text-xs"
                  >({{ $t('import.column_will_not_import') }})</span
                ></i-form-label
              >
              <p
                class="truncate text-neutral-500 dark:text-neutral-300"
                v-text="column.preview"
              ></p>
            </div>
          </div>

          <div class="w-1/2">
            <div
              v-for="(column, index) in importInstance.mappings"
              class="mb-2 flex h-16 items-center"
              :key="'field-' + index"
            >
              <icon icon="ChevronRight" class="mr-3 h-5 w-5" />

              <i-form-select
                :size="false"
                :rounded="false"
                class="h-16 rounded py-5 px-4"
                v-model="importInstance.mappings[index].attribute"
                @input="importInstance.mappings[index].skip = !$event"
              >
                <option value="" v-if="!isColumnRequired(column)">N/A</option>
                <option
                  :disabled="isfieldIsMapped(field.attribute)"
                  :value="field.attribute"
                  :key="'field-' + index + '-' + field.attribute"
                  v-for="field in importInstance.fields.all()"
                >
                  {{ field.label }}
                </option>
              </i-form-select>
            </div>
          </div>
        </div>
      </div>
    </i-card-body>
    <template v-if="importInstance" #footer>
      <div class="flex items-center justify-end space-x-2">
        <i-button
          @click="destroy(importInstance.id)"
          :disabled="importIsInProgress"
          variant="white"
          >{{ $t('app.cancel') }}</i-button
        >
        <i-button
          @click="performImport"
          :loading="importIsInProgress"
          :disabled="importIsInProgress"
          >{{
            importIsInProgress ? $t('app.please_wait') : $t('import.import')
          }}</i-button
        >
      </div>
    </template>
  </i-card>
  <i-card
    class="mt-5"
    :overlay="loadingImportHistory"
    header-class="relative overflow-hidden"
    no-body
  >
    <template #header>
      <i-card-heading>{{ $t('import.history') }}</i-card-heading>

      <card-header-grid-background
        class="inset-0 hidden h-44 md:block"
        style="transform: skewY(-8deg)"
      />
    </template>
    <i-table v-if="hasImportHistory" class="-mt-px">
      <thead>
        <tr>
          <th class="text-left" v-t="'import.date'"></th>
          <th class="text-left" v-t="'import.file_name'"></th>
          <th class="text-left" v-t="'import.user'"></th>
          <th class="text-center" v-t="'import.total_imported'"></th>
          <th class="text-center" v-t="'import.total_duplicates'"></th>
          <th class="text-center" v-t="'import.total_skipped'"></th>
          <th class="text-center" v-t="'import.status'"></th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(history, index) in computedImports" :key="history.id">
          <td class="text-left">{{ localizedDateTime(history.created_at) }}</td>
          <td class="text-left">{{ history.file_name }}</td>
          <td class="text-left">{{ history.user.name }}</td>
          <td class="text-center">{{ history.imported }}</td>
          <td class="text-center">{{ history.duplicates }}</td>
          <td class="text-center">{{ history.skipped }}</td>
          <td class="text-center">
            <span v-i-tooltip="history.status" class="inline-block">
              <icon
                icon="MenuAlt1"
                class="h-5 w-5 animate-pulse text-neutral-500 dark:text-neutral-400"
                v-if="history.status === 'mapping'"
              />
              <icon
                icon="CheckCircle"
                class="h-5 w-5 text-success-500 dark:text-success-400"
                v-else-if="history.status === 'finished'"
              />
              <icon
                icon="DotsHorizontal"
                class="h-5 w-5 animate-bounce text-neutral-500 dark:text-neutral-400"
                v-else-if="history.status === 'in-progress'"
              />
            </span>
          </td>
          <td>
            <div class="flex justify-end space-x-2">
              <a
                href="#"
                class="link"
                v-if="
                  history.status === 'mapping' &&
                  (!importInstance ||
                    (importInstance && importInstance.id != history.id))
                "
                @click.prevent="continueMapping(history.id)"
                v-t="'app.continue'"
              />
              <i-button-icon
                icon="Trash"
                v-if="history.authorizations.delete"
                @click="destroy(history.id)"
              />
            </div>
          </td>
        </tr>
      </tbody>
    </i-table>
    <i-card-body v-else v-show="!loadingImportHistory" class="text-center">
      <icon icon="EmojiSad" class="mx-auto h-12 w-12 text-neutral-400" />
      <h3
        class="mt-2 text-sm font-medium text-neutral-800 dark:text-white"
        v-t="'import.no_history'"
      />
    </i-card-body>
  </i-card>
</template>
<script>
import MediaUpload from '@/components/Media/MediaUpload'
import FieldsCollection from '@/services/FieldsCollection'
import ImportSteps from './ImportSteps'
import orderBy from 'lodash/orderBy'
import findIndex from 'lodash/findIndex'
import find from 'lodash/find'
import FileDownload from 'js-file-download'
import CardHeaderGridBackground from '@/components/Cards/HeaderGridBackground'
export default {
  components: {
    MediaUpload,
    ImportSteps,
    CardHeaderGridBackground,
  },
  props: {
    header: String,
    resourceName: { required: true, type: String },

    requestQueryString: {
      type: Object,
      default() {
        return {}
      },
    },
  },
  data() {
    return {
      imports: [],
      errors: [],
      importInstance: null,
      loadingImportHistory: false,
      importIsInProgress: false,
      steps: [
        {
          id: '01',
          name: this.$t('import.steps.step_1.name'),
          description: this.$t('import.steps.step_1.description'),
          status: 'current',
        },
        {
          id: '02',
          name: this.$t('import.steps.step_2.name'),
          description: this.$t('import.steps.step_2.description'),
          status: 'upcoming',
        },
        {
          id: '03',
          name: this.$t('import.steps.step_3.name'),
          description: this.$t('import.steps.step_3.description'),
          status: 'upcoming',
        },
        {
          id: '04',
          name: this.$t('import.steps.step_4.name'),
          description: this.$t('import.steps.step_4.description'),
          status: 'upcoming',
        },
      ],
    }
  },
  computed: {
    /**
     * Indicates whether the resource has import history
     *
     * @return {Boolean}
     */
    hasImportHistory() {
      return this.computedImports.length > 0
    },

    /**
     * Get the computed imports ordered by date
     *
     * @return {Array}
     */
    computedImports() {
      return orderBy(this.imports, ['created_at'], ['desc'])
    },

    /**
     * Get the URL for upload
     *
     * @return {String}
     */
    uploadUrl() {
      let url = `${this.$store.state.apiURL}/${this.resourceName}/import/upload`

      return this.appendQueryString(url)
    },

    /**
     * Get the import URL
     *
     * @return {String}
     */
    importUrl() {
      let url = `/${this.resourceName}/import/${this.importInstance.id}`

      return this.appendQueryString(url)
    },
  },

  methods: {
    /**
     * Change the current import step to
     *
     * @param  {String} id
     * @param  {String} status
     *
     * @return {Void}
     */
    changeCurrentStep(id, status) {
      // When changing to "complete" or "current" we will
      // update all other steps below this step to complete
      if (status === 'complete' || status === 'current') {
        let stepsBelowStep = this.steps.filter(
          step => parseInt(step.id) < parseInt(id)
        )
        stepsBelowStep.forEach(step => (step.status = 'complete'))
      }

      if (status === 'current') {
        // When changing to current, all steps above this step will be upcoming
        let stepsAboveStep = this.steps.filter(
          step => parseInt(step.id) > parseInt(id)
        )
        stepsAboveStep.forEach(step => (step.status = 'upcoming'))
      }

      this.steps[findIndex(this.steps, ['id', id])].status = status
    },

    /**
     * Append query string to the given url
     *
     * @param  {string} url
     *
     * @return {string}
     */
    appendQueryString(url) {
      if (Object.keys(this.requestQueryString).length > 0) {
        var str = []
        for (var p in this.requestQueryString)
          if (this.requestQueryString.hasOwnProperty(p)) {
            str.push(
              encodeURIComponent(p) +
                '=' +
                encodeURIComponent(this.requestQueryString[p])
            )
          }
        url += '?' + str.join('&')
      }
      return url
    },

    /**
     * Download sample import file
     * @param { String} route
     * @return {Void}
     */
    downloadSample(route) {
      Innoclapps.request()
        .get(route)
        .then(({ data }) => {
          FileDownload(data, 'sample.csv')
          if (
            this.steps[0].status === 'current' ||
            this.steps[3].status === 'complete'
          ) {
            this.changeCurrentStep('02', 'current')
          }
        })
    },

    /**
     * Check whether the field is mapped in a column
     *
     * @param  {String} attribute
     *
     * @return {Boolean}
     */
    isfieldIsMapped(attribute) {
      return Boolean(
        find(this.importInstance.mappings, ['attribute', attribute])
      )
    },

    /**
     * Delete the given history
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    async destroy(id) {
      await this.$dialog.confirm()

      Innoclapps.request()
        .delete(`/${this.resourceName}/import/${id}`)
        .then(() => {
          this.imports.splice(findIndex(this.imports, ['id', id]), 1)
          if (this.importInstance && id == this.importInstance.id) {
            this.importInstance = null
          }
        })
    },

    /**
     * Continue mapping the given import
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    continueMapping(id) {
      this.setImportForMapping(find(this.imports, ['id', Number(id)]))
      this.changeCurrentStep('03', 'current')
    },

    /**
     * Set the import instance for mapping
     *
     * @param {Object} instance
     */
    setImportForMapping(instance) {
      instance.fields = new FieldsCollection(instance.fields)
      this.importInstance = instance
    },

    /**
     * Retrieve the current resource imports
     *
     * @return {Void}
     */
    retrieveImports() {
      this.loadingImportHistory = true

      Innoclapps.request()
        .get(`${this.$store.state.apiURL}/${this.resourceName}/import`)
        .then(({ data }) => (this.imports = data))
        .finally(() => (this.loadingImportHistory = false))
    },

    /**
     * Check whether the given import column is requored
     *
     * @param  {Object}  column
     *
     * @return {Boolean}
     */
    isColumnRequired(column) {
      if (
        !column.detected_attribute ||
        !this.importInstance.fields.has(column.detected_attribute)
      ) {
        return false
      }

      return this.importInstance.fields.find(column.detected_attribute)
        .isRequired
    },

    /**
     * Handle file uplaoded
     *
     * @return {Void}
     */
    handleFileUploaded(importInstance) {
      this.setImportForMapping(importInstance)
      this.imports.push(importInstance)
      this.changeCurrentStep('03', 'current')
      this.errors = []
    },

    /**
     * Perform the import for the current import instance
     *
     * @return {Void}
     */
    performImport() {
      this.importIsInProgress = true
      Innoclapps.request()
        .post(this.importUrl, {
          mappings: this.importInstance.mappings,
        })
        .then(({ data }) => {
          Innoclapps.success(this.$t('import.imported'))
          this.importInstance = null
          let index = findIndex(this.imports, ['id', Number(data.id)])

          if (index !== -1) {
            this.imports[index] = data
          } else {
            this.imports.push(data)
          }
          this.errors = []

          this.changeCurrentStep('04', 'complete')

          // In case of any custom options created, reset the
          // store state for the cached fields
          this.resetStoreState()
        })
        .catch(error => {
          this.changeCurrentStep('04', 'current')
          this.errors = error.response.data.errors
          document.getElementById('main').scrollTop = 0
        })
        .finally(() => (this.importIsInProgress = false))
    },
  },
  created() {
    this.retrieveImports()
  },
}
</script>
