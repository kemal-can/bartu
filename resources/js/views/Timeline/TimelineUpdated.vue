<template>
  <timeline-entry :resource-name="resourceName" :log="log" icon="PencilAlt">
    <template #heading>
      <i18n-t
        v-if="log.causer_name"
        scope="global"
        :keypath="resourceSingular + '.timeline.updated'"
        v-once
      >
        <template #causer>
          <span class="font-medium" v-text="log.causer_name"></span>
        </template>
      </i18n-t>
      <span v-else v-once v-text="$t('timeline.updated')"></span>
    </template>
    <div class="mt-2">
      <div v-if="changesVisible">
        <p class="text-sm font-semibold text-neutral-700 dark:text-neutral-300">
          {{ $t('fields.updated') }} ({{ totalUpdatedAttributes }})
        </p>
        <div class="mt-2" v-once>
          <i-table class="overflow-hidden rounded-lg" bordered>
            <thead>
              <tr>
                <th class="text-left" v-t="'fields.updated_field'"></th>
                <th class="text-left" v-t="'fields.new_value'"></th>
                <th class="text-left" v-t="'fields.old_value'"></th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="(attribute, key) in updatedAttributes"
                :key="resourceName + key"
              >
                <td>
                  <!-- Check if the attributes has label key, usually used in custom fields where
                                    label and value is stored separately e.q. {label: 'Label', value:'Value'} -->
                  {{
                    attribute && attribute.label
                      ? attribute.label
                      : $t('fields.' + resourceName + '.' + key)
                  }}
                </td>
                <td>
                  <!-- For custom fields -->
                  {{ determineChangedFieldValue(attribute, key) }}
                </td>
                <td>
                  <!-- For custom fields -->
                  {{ determineChangedFieldValue(log.properties.old[key], key) }}
                </td>
              </tr>
            </tbody>
          </i-table>
        </div>
      </div>
      <a
        href="#"
        @click.prevent="changesVisible = !changesVisible"
        class="link mt-2 block text-sm"
        v-text="updatedFieldsText"
      ></a>
    </div>
  </timeline-entry>
</template>
<script>
import pickBy from 'lodash/pickBy'
import TimelineEntry from './TimelineEntry'

export default {
  mixins: [TimelineEntry],
  data: () => ({
    changesVisible: false,
  }),
  computed: {
    /**
     * Updates fields toggle text
     *
     * @return {String}
     */
    updatedFieldsText() {
      return this.changesVisible
        ? this.$t('fields.hide_updated')
        : this.$t('fields.view_updated') +
            ' (' +
            this.totalUpdatedAttributes +
            ')'
    },

    /**
     * Get the updated attributes
     *
     * Excluded the one that ends with _id because they are probably relation ID,
     * We are tracking the relations display name as well so we can display new and old value
     * in proper format not the actual ID.
     *
     * @return {Array}
     */
    updatedAttributes() {
      return pickBy(
        this.log.properties.attributes,
        (attribute, field) => field.indexOf('_id') === -1
      )
    },

    /**
     * Total updated attributes
     *
     * @return {Number}
     */
    totalUpdatedAttributes() {
      return Object.keys(this.updatedAttributes).length
    },
  },
  methods: {
    /**
     * Determine the changed value
     *
     * @param  {Object} data
     * @param  {String} key
     *
     * @return {mixed}
     */
    determineChangedFieldValue(data, key) {
      return data && data.hasOwnProperty('value')
        ? this.maybeFormatDateValue(data.value)
        : this.$te('timeline.' + key + '.' + data)
        ? this.$t('timeline.' + key + '.' + data)
        : this.maybeFormatDateValue(data)
    },
  },
}
</script>
