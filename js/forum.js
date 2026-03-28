import { extend } from 'flarum/extend';
import app from 'flarum/app';
import UserControls from 'flarum/utils/UserControls';
import Button from 'flarum/components/Button';
import Badge from 'flarum/components/Badge';
import User from 'flarum/models/User';
import Model from 'flarum/Model';
import LogInModal from 'flarum/components/LogInModal';

import UnlockUserModal from './src/forum/components/UnlockUserModal';

app.initializers.add('ralkage/flarum-account-lockout', () => {
  User.prototype.isLocked = Model.attribute('isLocked');
  User.prototype.lockedUntil = Model.attribute('lockedUntil', Model.transformDate);
  User.prototype.lockedAt = Model.attribute('lockedAt', Model.transformDate);
  User.prototype.loginFailedCount = Model.attribute('loginFailedCount');
  User.prototype.canUnlock = Model.attribute('canUnlock');

  // Add "Unlock" button to user moderation controls
  extend(UserControls, 'moderationControls', (items, user) => {
    if (user.canUnlock() && user.isLocked()) {
      items.add(
        'unlock',
        <Button icon="fas fa-lock-open" onclick={() => app.modal.show(UnlockUserModal, { user })}>
          {app.translator.trans('ralkage-account-lockout.forum.user_controls.unlock_button')}
        </Button>
      );
    }
  });

  // Add "Locked" badge to locked users
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
  extend(LogInModal.prototype, 'onerror', function (returnValue, error) {
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
