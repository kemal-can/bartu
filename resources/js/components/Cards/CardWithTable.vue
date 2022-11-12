<template>
  <card no-body :card="card" @retrieved="prepareComponent($event.card)">
    <i-table v-if="hasData" sticky max-height="450px" ref="table">
      <thead>
        <tr>
          <th
            v-for="field in fields"
            :class="[
              'text-left',
              {
                hidden: field.isStacked,
              },
            ]"
            :key="'th-' + field.key"
            :ref="'th-' + field.key"
            v-text="field.label"
          />
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="item in mutableCard.items"
          :key="item[mutableCard.primaryKey]"
        >
          <td
            v-for="field in fields"
            :key="'td-' + field.key"
            :class="{
              'whitespace-nowrap': !field.isStacked,
              hidden: field.isStacked,
            }"
          >
            <span v-if="field.key === fields[0].key && item.path">
              <router-link class="link" :to="item.path">{{
                item[field.key]
              }}</router-link>
            </span>
            <span v-else>
              {{
                field.formatter
                  ? field.formatter(item[field.key], field.key, item)
                  : item[field.key]
              }}
            </span>
            <template v-if="field.key === fields[0].key">
              <p
                v-for="stackedField in stackedFields"
                class="flex items-center font-normal"
                :key="'stacked-' + stackedField.key"
              >
                <span
                  class="mr-1 font-medium text-neutral-800 dark:text-neutral-100"
                  >{{ stackedField.label }}:</span
                >
                <span class="text-neutral-700 dark:text-neutral-300">
                  {{
                    stackedField.formatter
                      ? stackedField.formatter(
                          item[stackedField.key],
                          stackedField.key,
                          item
                        )
                      : item[stackedField.key]
                  }}
                </span>
              </p>
            </template>
          </td>
        </tr>
      </tbody>
    </i-table>
    <p
      v-else
      class="pb-16 pt-12 text-center text-neutral-500 dark:text-neutral-300"
      v-text="emptyText"
    />
  </card>
</template>
<script>
import get from 'lodash/get'
import { isISODate, isDate, isResponsiveTableColumnVisible } from '@/utils'
import { clearCache as clearIsVisibleCache } from '@/utils/isResponsiveTableColumnVisible'

export default {
  props: {
    card: Object,
    stackable: { type: Boolean, default: true },
  },
  data: () => ({
    mutableCard: {},
  }),
  computed: {
    /**
     * Get the stacked fields
     */
    stackedFields() {
      return this.mutableCard.fields.filter(field => field.isStacked)
    },

    /**
     * Get the fields for the table
     *
     * @return {Array}
     */
    fields() {
      return this.mutableCard.fields.map(field => {
        field.formatter = (value, key, item) => {
          if (isDate(value)) {
            return this.localizedDate(value)
          } else if (isISODate(value)) {
            return this.localizedDateTime(value)
          }

          // Dot notation formatting and getting values
          return get(item, key)
        }

        return field
      })
    },

    /**
     * Empty text
     *
     * @return {String}
     */
    emptyText() {
      return this.mutableCard.emptyText || this.$t('app.not_enough_data')
    },

    /**
     * Indicates whether the table has items
     *
     * @return {Boolean}
     */
    hasData() {
      return this.mutableCard.items.length > 0
    },
  },
  methods: {
    /**
     * Prepare component
     *
     * @param  {Object} card
     *
     * @return {Void}
     */
    prepareComponent(card) {
      this.mutableCard = card
    },

    /**
     * Stack the columns
     *
     * @return {Void}
     */
    stackColumns() {
      this.fields.forEach((field, idx) => {
        if (idx > 0 && this.$refs['th-' + field.key]) {
          const el = this.$refs['th-' + field.key][0]
          this.mutableCard.fields[idx].isStacked =
            !isResponsiveTableColumnVisible(el, this.$refs.table.$el)
        }
      })
    },
  },
  created() {
    this.prepareComponent(this.card)
  },
  mounted() {
    this.stackable && this.$nextTick(this.stackColumns)
    this.stackable && window.addEventListener('resize', this.stackColumns)
  },
  beforeUnmount() {
    clearIsVisibleCache()
    this.stackable && window.removeEventListener('resize', this.stackColumns)
  },
}
</script>
