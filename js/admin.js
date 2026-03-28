import { extend } from 'flarum/common/extend';
import app from 'flarum/admin/app';
import User from 'flarum/common/models/User';
import Model from 'flarum/common/Model';
import Button from 'flarum/common/components/Button';
import Icon from 'flarum/common/components/Icon';
import extractText from 'flarum/common/utils/extractText';

app.initializers.add('ralkage/flarum-account-lockout', () => {
  User.prototype.isLocked = Model.attribute('isLocked');
  User.prototype.lockedUntil = Model.attribute('lockedUntil', Model.transformDate);
  User.prototype.lockedAt = Model.attribute('lockedAt', Model.transformDate);
  User.prototype.loginFailedCount = Model.attribute('loginFailedCount');
  User.prototype.canUnlock = Model.attribute('canUnlock');

  app.registry
    .for('ralkage-account-lockout')
    .registerSetting({
      setting: 'ralkage-account-lockout.max_attempts',
      label: app.translator.trans('ralkage-account-lockout.admin.settings.max_attempts_label'),
      help: app.translator.trans('ralkage-account-lockout.admin.settings.max_attempts_help'),
      type: 'number',
      min: 1,
      max: 100,
    })
    .registerSetting({
      setting: 'ralkage-account-lockout.lockout_mode',
      label: app.translator.trans('ralkage-account-lockout.admin.settings.lockout_mode_label'),
      help: app.translator.trans('ralkage-account-lockout.admin.settings.lockout_mode_help'),
      type: 'select',
      options: {
        timed: 'Timed (auto-unlock)',
        manual: 'Manual (admin/mod unlock only)',
      },
      default: 'timed',
    })
    .registerSetting({
      setting: 'ralkage-account-lockout.lockout_duration',
      label: app.translator.trans('ralkage-account-lockout.admin.settings.lockout_duration_label'),
      help: app.translator.trans('ralkage-account-lockout.admin.settings.lockout_duration_help'),
      type: 'select',
      options: {
        5: '5 minutes',
        10: '10 minutes',
        15: '15 minutes',
        30: '30 minutes',
        60: '1 hour',
      },
      default: '15',
    })
    .registerPermission(
      {
        icon: 'fas fa-lock-open',
        label: app.translator.trans('ralkage-account-lockout.admin.permissions.unlock_users_label'),
        permission: 'user.unlock',
      },
      'moderate'
    );

  // Add lock status column to admin user list
  extend('flarum/admin/components/UserListPage', 'columns', function (columns) {
    columns.add(
      'lockStatus',
      {
        name: app.translator.trans('ralkage-account-lockout.admin.users.lock_status_column'),
        content: (user) => {
          if (user.isLocked()) {
            return <span className="UserLockStatus UserLockStatus--locked" title={app.translator.trans('ralkage-account-lockout.admin.users.locked_tooltip')}><Icon name="fas fa-lock" /></span>;
          }

          return <span className="UserLockStatus UserLockStatus--unlocked"><Icon name="fas fa-lock-open" /></span>;
        },
      },
      65
    );
  });

  // Add unlock action to admin user list
  extend('flarum/admin/components/UserListPage', 'userActionItems', function (items, user) {
    if (user.canUnlock() && user.isLocked()) {
      items.add(
        'unlockUser',
        <Button
          icon="fas fa-lock-open"
          onclick={() => {
            if (confirm(extractText(app.translator.trans('ralkage-account-lockout.admin.users.unlock_confirmation', { username: user.displayName() })))) {
              user.save({ isLocked: false }).then(() => m.redraw());
            }
          }}
        >
          {app.translator.trans('ralkage-account-lockout.admin.users.unlock_button')}
        </Button>
      );
    }
  });
});
