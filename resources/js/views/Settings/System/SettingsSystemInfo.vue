<template>
  <i-card :header="$t('app.system_info')" no-body>
    <template #actions>
      <i-button
        @click="download"
        size="sm"
        icon="DocumentDownload"
        variant="white"
      />
    </template>
    <i-table>
      <tbody>
        <tr v-for="(value, variableName) in info" :key="variableName">
          <td>
            <span class="font-medium" v-text="variableName"></span>
          </td>
          <td>
            <span
              class="text-neutral-500 dark:text-neutral-300"
              v-text="value"
            ></span>
          </td>
        </tr>
      </tbody>
    </i-table>
  </i-card>
</template>
<script>
import FileDownload from 'js-file-download'
export default {
  data: () => ({
    info: {},
  }),
  methods: {
    /**
     * Retrieve the system info
     *
     * @return {Void}
     */
    retrieve() {
      Innoclapps.request()
        .get('/system/info')
        .then(({ data }) => (this.info = data))
    },

    /**
     * Download the system info
     *
     * @return {Void}
     */
    download() {
      Innoclapps.request()
        .post(
          '/system/info',
          {},
          {
            responseType: 'blob',
          }
        )
        .then(response => {
          FileDownload(response.data, 'system-info.xlsx')
        })
    },
  },
  created() {
    this.retrieve()
  },
}
</script>
