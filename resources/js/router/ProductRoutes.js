/**
 * Bartu CRM - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @version   1.1.7
 *
 * @link      Releases - https://github.com/kemal-can/BARTU-Comprehensive-CRM
 *
 * @copyright Copyright (c) 2019-2022 mail@kemalcan.net
 */
import i18n from '@/i18n'

import ProductIndex from '@/views/Products/Index'
import ProductCreate from '@/views/Products/Create'
import ProductEdit from '@/views/Products/Edit'

export default [
  {
    path: '/products',
    name: 'product-index',
    component: ProductIndex,
    meta: {
      title: i18n.t('product.products'),
    },
    children: [
      {
        path: 'create',
        name: 'create-product',
        component: ProductCreate,
        meta: { title: i18n.t('product.create') },
      },
      {
        path: ':id',
        name: 'view-product',
        component: ProductEdit,
      },
      {
        path: ':id/edit',
        name: 'edit-product',
        component: ProductEdit,
      },
    ],
  },
]
