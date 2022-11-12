<template>
  <i-layout>
    <template #actions>
      <navbar-separator class="hidden lg:block" />
      <div class="inline-flex items-center">
        <div class="mr-3 lg:mr-6">
          <i-minimal-dropdown type="horizontal">
            <i-dropdown-item
              icon="DocumentAdd"
              :to="{
                name: 'import-resource',
                params: { resourceName: 'contacts' },
              }"
              :text="$t('import.import')"
            />
            <i-dropdown-item
              icon="DocumentDownload"
              v-i-modal="'export-modal'"
              >{{ $t('app.export.export') }}</i-dropdown-item
            >
            <i-dropdown-item
              icon="Trash"
              :to="{
                name: 'trashed-resource-records',
                params: { resourceName: 'contacts' },
              }"
              >{{ $t('app.soft_deletes.trashed') }}</i-dropdown-item
            >
            <i-dropdown-item
              icon="Cog"
              @click="() => $refs.table.customize()"
              >{{ $t('table.list_settings') }}</i-dropdown-item
            >
          </i-minimal-dropdown>
        </div>
        <i-button :to="{ name: 'create-contact' }" icon="Plus" size="sm">{{
          $t('contact.create')
        }}</i-button>
      </div>
    </template>

    <cards resource-name="contacts" v-if="showCards" />

    <contact-table
      ref="table"
      :initialize="shouldInitializeIndex"
      @loaded="tableEmpty = $event.empty"
      @deleted="refreshIndex"
    />

    <contact-export
      url-path="/contacts/export"
      resource-name="contacts"
      :filters-view="tableId"
      :title="$t('contact.export')"
    />
    <!-- Create -->
    <router-view name="create" @created="refreshIndex"></router-view>
  </i-layout>
</template>
<script>
import Cards from '@/components/Cards/Cards'
import ContactTable from './ContactTable'
import ContactExport from '@/components/Export'

export default {
  components: {
    Cards,
    ContactTable,
    ContactExport,
  },
  data: () => ({
    shouldInitializeIndex: false,
    tableId: 'contacts',
    tableEmpty: true,
  }),
  computed: {
    /**
     * Indicates whether the cards should be shown
     *
     * @return {Boolean}
     */
    showCards() {
      return this.shouldInitializeIndex && !this.tableEmpty
    },
  },
  beforeRouteEnter(to, from, next) {
    next(vm => {
      /**
       * Check whether the accessed route is the index one
       * Can be created, etc... in this case, we just load
       * the child route instead of loading all related data to the index
       *
       * @return {Boolean}
       */
      vm.shouldInitializeIndex = vm.$route.name === 'contact-index'
    })
  },

  /**
   * Before the cached route is updated
   * For all cases set that intialize index to be true
   * This helps when intially shouldInitializeIndex was false
   * But now when the user actually sees the index, it should be updated to true
   */
  beforeRouteUpdate(to, from, next) {
    this.shouldInitializeIndex = true

    next()
  },
  methods: {
    /**
     * Handle record deleted from table
     *
     * @return {Void}
     */
    refreshIndex() {
      Innoclapps.$emit('refresh-cards')
      this.$refs.table.reload()
    },
  },
}
</script>
