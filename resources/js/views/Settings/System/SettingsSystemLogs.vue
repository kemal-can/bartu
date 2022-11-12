<template>
  <i-card header="Logs" no-body>
    <template #actions>
      <div class="inline-flex">
        <i-form-select
          :option="logTypes"
          v-model="logType"
          class="mr-2"
          @input="$router.replace({ query: { type: $event, date: date } })"
        >
          <option :value="type" v-for="type in logTypes" :key="type">
            {{ type }}
          </option>
        </i-form-select>
        <i-form-select
          v-model="date"
          @input="$router.replace({ query: { date: $event, type: logType } })"
        >
          <option :value="date" v-for="date in log.log_dates" :key="date">
            {{ date }}
          </option>
        </i-form-select>
      </div>
    </template>
    <i-table class="-mt-px">
      <thead>
        <tr>
          <th class="text-left" width="20%">Date</th>
          <th class="text-left" width="5%">Env</th>
          <th class="text-left" width="5%">Type</th>
          <th class="text-left" width="60%">Message</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(line, index) in filteredLogs" :key="index">
          <td width="20%" v-text="line.timestamp"></td>
          <td width="5%" v-text="line.env"></td>
          <td width="5%">
            <i-badge
              v-text="line.type"
              :variant="logTypesClassMaps[line.type]"
            />
          </td>
          <td width="60%" class="break-all">
            {{ line.message }}
          </td>
        </tr>
        <td
          colspan="4"
          class="p-4 text-center text-sm text-neutral-500 dark:text-neutral-300"
          v-show="!hasLogs"
        >
          {{ log.message || 'No logs to show.' }}
        </td>
      </tbody>
    </i-table>
  </i-card>
</template>
<script>
export default {
  data: () => ({
    log: {},
    date: moment.utc().format('YYYY-MM-DD'),
    logType: 'ALL',
    logTypes: [
      'ALL',
      'INFO',
      'EMERGENCY',
      'CRITICAL',
      'ALERT',
      'ERROR',
      'WARNING',
      'NOTICE',
      'DEBUG',
    ],
    logTypesClassMaps: {
      INFO: 'info',
      DEBUG: 'neutral',
      EMERGENCY: 'danger',
      CRITICAL: 'danger',
      NOTICE: 'neutral',
      WARNING: 'warning',
      ERROR: 'danger',
      ALERT: 'warning',
    },
  }),
  watch: {
    date: function () {
      this.retrieve()
    },
  },
  computed: {
    /**
     * Filtered logs
     *
     * @return {Array}
     */
    filteredLogs() {
      if (!this.log.logs) {
        return []
      }

      if (!this.logType || this.logType === 'ALL') {
        return this.sortLogsByDate(this.log.logs)
      }

      return this.sortLogsByDate(
        this.log.logs.filter(log => log.type === this.logType)
      )
    },

    /**
     * Indicates whether there are logs
     *
     * @return {Boolean}
     */
    hasLogs() {
      return this.filteredLogs && this.filteredLogs.length > 0
    },
  },
  methods: {
    /**
     * Sort the given logs by date
     *
     * @param  {Array} logs
     *
     * @return {Array}
     */
    sortLogsByDate(logs) {
      return logs.sort(function compare(a, b) {
        var dateA = new Date(a.timestamp)
        var dateB = new Date(b.timestamp)
        return dateB - dateA
      })
    },

    /**
     * Retrieve the system logs
     *
     * @return {Void}
     */
    retrieve() {
      Innoclapps.request()
        .get('/system/logs', {
          params: {
            date: this.date,
          },
        })
        .then(({ data }) => {
          this.log = data

          if (data.log_dates.indexOf(this.date) === -1) {
            this.log.log_dates.push(this.date)
          }
        })
    },
  },
  created() {
    this.retrieve()

    if (this.$route.query.date) {
      this.date = this.$route.query.date
    }

    if (this.$route.query.type) {
      this.logType = this.$route.query.type
    }
  },
}
</script>
