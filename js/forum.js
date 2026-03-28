import { extend } from 'flarum/common/extend';
import app from 'flarum/forum/app';
import Button from 'flarum/common/components/Button';
import Badge from 'flarum/common/components/Badge';
import User from 'flarum/common/models/User';
import Model from 'flarum/common/Model';

import UnlockUserModal from './src/forum/components/UnlockUserModal';

app.initializers.add('ralkage/flarum-account-lockout', () => {
  User.prototype.isLocked = Model.attribute('isLocked');
  User.prototype.lockedUntil = Model.attribute('lockedUntil', Model.transformDate);
  User.prototype.lockedAt = Model.attribute('lockedAt', Model.transformDate);
  User.prototype.loginFailedCount = Model.attribute('loginFailedCount');
  User.prototype.canUnlock = Model.attribute('canUnlock');

  // Add unlock button to user moderation controls
  extend('flarum/forum/utils/UserControls', 'moderationControls', (items, user) => {
    if (user.canUnlock() && user.isLocked()) {
      items.add(
        'unlock',
        <Button icon="fas fa-lock-open" onclick={() => app.modal.show(UnlockUserModal, { user })}>
          {app.translator.trans('ralkage-account-lockout.forum.user_controls.unlock_button')}
        </Button>
      );
    }
  });

  // Add locked badge to user profiles
  extend(User.prototype, 'badges', function (items) {
    if (this.isLocked()) {
      items.add(
        'locked',
        <Badge icon="fas fa-lock" type="locked" label={app.translator.trans('ralkage-account-lockout.forum.user_badge.locked_tooltip')} />,
        100
      );
    }
  });

  // Handle 423 (Account Locked) error on login
  extend('flarum/forum/components/LogInModal', 'onerror', function (returnValue, error) {
    if (error.status === 423) {
      const errors = error.response && error.response.errors;
      const retryAfter = errors && errors[0] && errors[0].retry_after;

      if (retryAfter) {
        error.alert.content = app.translator.trans('ralkage-account-lockout.forum.log_in.locked_timed', {
          minutes: retryAfter,
        });
      } else {
        error.alert.content = app.translator.trans('ralkage-account-lockout.forum.log_in.locked_manual');
      }
    }
  });
});
