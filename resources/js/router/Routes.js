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

import Dashboard from '@/views/Dashboard/Index'
import EditDashboard from '@/views/Dashboard/Edit'

import SettingsRoutes from './SettingsRoutes'
import CompanyRoutes from './CompanyRoutes'
import ContactRoutes from './ContactRoutes'
import DealRoutes from './DealRoutes'
import ProductRoutes from './ProductRoutes'
import CalendarRoutes from './CalendarRoutes'
import ActivityRoutes from './ActivityRoutes'
import MailRoutes from './MailRoutes'
import InboxRoutes from './InboxRoutes'

import Profile from '@/views/Users/Profile'
import PersonalAccessTokens from '@/views/Users/PersonalAccessTokens'
import OAuthAccounts from '@/views/OAuth/Accounts'

import Notifications from '@/views/Notifications/Index'

import Error404 from '@/views/Error404'
import Error403 from '@/views/Error403'

import TrashedResourceRecords from '@/views/Resources/TrashedResourceRecords'
import ResourceImport from '@/views/Resources/Import'

const routes = [
  {
    alias: '/',
    path: '/dashboard',
    component: Dashboard,
    meta: {
      title: i18n.t('dashboard.insights'),
      scrollToTop: false,
    },
  },
  {
    path: '/',
    name: 'dashboard',
    component: Dashboard,
    meta: {
      title: i18n.t('dashboard.insights'),
      scrollToTop: false,
    },
  },
  {
    path: '/dashboard/:id/edit',
    name: 'edit-dashboard',
    component: EditDashboard,
  },
  {
    path: '/notifications',
    name: 'notifications',
    component: Notifications,
    meta: {
      title: i18n.t('notifications.your'),
    },
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: {
      title: i18n.t('profile.profile'),
    },
  },
  {
    path: '/import/:resourceName',
    name: 'import-resource',
    component: ResourceImport,
  },
  {
    path: '/personal-access-tokens',
    name: 'personal-access-tokens',
    component: PersonalAccessTokens,
    meta: {
      title: i18n.t('api.personal_access_tokens'),
      gate: 'access-api',
    },
  },
  {
    path: '/oauth/accounts',
    name: 'oauth-accounts',
    component: OAuthAccounts,
    meta: {
      title: i18n.t('app.oauth.connected_accounts'),
    },
  },
  {
    path: '/trashed/:resourceName',
    name: 'trashed-resource-records',
    component: TrashedResourceRecords,
    meta: {
      title: i18n.t('app.soft_deletes.trashed_records'),
    },
  },
  {
    name: '404',
    path: '/404',
    component: Error404,
  },
  {
    name: '403',
    path: '/403',
    component: Error403,
    props: true,
  },
  {
    name: 'not-found',
    path: '/:pathMatch(.*)*',
    component: Error404,
  },

  ...SettingsRoutes,
  ...ContactRoutes,
  ...CompanyRoutes,
  ...DealRoutes,
  ...ProductRoutes,
  ...ActivityRoutes,
  ...CalendarRoutes,
  ...MailRoutes,
  ...InboxRoutes,
]

export default routes
