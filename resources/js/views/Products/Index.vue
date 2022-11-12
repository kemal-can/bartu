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
                params: { resourceName: 'products' },
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
                params: { resourceName: 'products' },
              }"
              >{{ $t('app.soft_deletes.trashed') }}</i-dropdown-item
            >
            <i-dropdown-item icon="Cog" @click="customize">{{
              $t('table.list_settings')
            }}</i-dropdown-item>
          </i-minimal-dropdown>
        </div>
        <i-button :to="{ name: 'create-product' }" icon="Plus" size="sm">{{
          $t('product.create')
        }}</i-button>
      </div>
    </template>
    <resource-table
      resource-name="products"
      :table-id="tableId"
      :empty-state="{
        to: { name: 'create-product' },
        title: 'No products',
        buttonText: 'Create Product',
        description: 'Get started by creating a new product.',
      }"
    >
      <template #name="{ row, formatted }">
        <div class="flex w-full justify-between">
          <router-link
            class="link grow"
            :to="{ name: 'edit-product', params: { id: row.id } }"
            >{{ formatted }}</router-link
          >
          <div class="ml-2">
            <i-minimal-dropdown
              v-if="row.authorizations.update || row.authorizations.delete"
            >
              <i-dropdown-item
                v-if="row.authorizations.update"
                :to="{ name: 'edit-product', params: { id: row.id } }"
                >{{ $t('app.edit') }}</i-dropdown-item
              >
              <i-dropdown-item
                v-if="row.authorizations.delete"
                @click="remove(row.id)"
                >{{ $t('app.delete') }}</i-dropdown-item
              >
            </i-minimal-dropdown>
          </div>
        </div>
      </template>
    </resource-table>

    <product-export
      url-path="/products/export"
      resource-name="products"
      :title="$t('product.export')"
    />
    <!-- Create, Edit -->
    <router-view @created="reload" @updated="reload"></router-view>
  </i-layout>
</template>
<script>
import ResourceTable from '@/components/Table'
import ProductExport from '@/components/Export'

export default {
  components: {
    ResourceTable,
    ProductExport,
  },
  data: () => ({
    tableId: 'products',
  }),
  methods: {
    /**
     * Reload the table
     */
    reload() {
      this.$iTable.reload(this.tableId)
    },

    /**
     * Customize the table (refs usage)
     */
    customize() {
      this.$store.commit('table/SET_CUSTOMIZE_VISIBILTY', {
        id: this.tableId,
        value: true,
      })
    },

    /**
     * Remove product from storage
     *
     * @param  {Number} id
     *
     * @return {Void}
     */
    remove(id) {
      this.$store.dispatch('products/destroy', id).then(() => {
        this.reload()
        Innoclapps.success(this.$t('product.deleted'))
      })
    },
  },
}
</script>
