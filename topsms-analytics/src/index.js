import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import { SmsReport } from './components/SmsReport/SmsReport';

addFilter(
  'woocommerce_admin_reports_list',
  'wc-admin-topsms-analytics',
  (reports) => {
    return [
      ...reports,
      {
        report: 'topsms-analytics',
        title: __('TopSMS Analytics', 'wc-admin-topsms-analytics'),
        component: SmsReport,
      },
    ];
  }
);
