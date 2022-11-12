<template>
  <card
    :card="card"
    no-body
    :request-query-string="tableRequestLastQueryString"
    @retrieved="handleCardRetrievedEvent"
    :reload-on-query-string-change="false"
  >
    <template #actions>
      <slot name="actions"></slot>
    </template>
    <div class="mh-56 overflow-y-auto">
      <table-simple
        ref="table"
        stackable
        :table-id="card.uriKey"
        :table-props="{
          sticky: true,
          maxHeight: '450px',
        }"
        :fields="fields"
        :initial-data="card.items"
        :request-query-string="cardRequestLastQueryString"
        :request-uri="'cards/' + card.uriKey"
        @data-loaded="tableRequestLastQueryString = $event.requestQueryString"
      >
        <template v-for="(_, name) in $slots" v-slot:[name]="slotData"
          ><slot :name="name" v-bind="slotData"
        /></template>
      </table-simple>
    </div>
  </card>
</template>
<script>
import TableSimple from '@/components/Table/Simple/TableSimple'
import get from 'lodash/get'
import { isISODate, isDate } from '@/utils'
export default {
  components: { TableSimple },
  props: {
    card: Object,
  },
  data: () => ({
    tableRequestLastQueryString: {},
    cardRequestLastQueryString: {},
  }),
  computed: {
    /**
     * Get the fields for the table
     *
     * @return {Array}
     */
    fields() {
      return this.card.fields.map(field => {
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
  },
  methods: {
    /**
     * Card retrieve event
     *
     * @param  {Object} payload
     *
     * @return {Void}
     */
    handleCardRetrievedEvent(payload) {
      this.cardRequestLastQueryString = payload.requestQueryString
      // We must replace the actual table data as the card may have e.q. range
      // parameter which may cause the table data to change but because
      // the request is not performed via the table class, the data will remain the same as before the
      // request and this will make sure that the data is updated
      this.$refs.table.replaceCollection(payload.card.items)
    },

    /**
     * Reload the tbale
     *
     * @return {Void}
     */
    reload() {
      this.$refs.table.reload()
    },
  },
}
</script>
