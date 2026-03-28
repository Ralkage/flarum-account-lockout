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

  // Handle login errors with lockout context
  extend(LogInModal.prototype, 'onerror', function (returnValue, error) {
    const errors = error.response && error.response.errors;
    const firstError = errors && errors[0];
    let changed = false;

    if (error.status === 423) {
      const retryAfter = firstError && firstError.retry_after;

      if (retryAfter) {
        error.alert.content = app.translator.trans('ralkage-account-lockout.forum.log_in.locked_timed', {
          minutes: retryAfter,
        });
      } else {
        error.alert.content = app.translator.trans('ralkage-account-lockout.forum.log_in.locked_manual');
      }
      changed = true;
    }

    // Show remaining attempts on failed login (401)
    if (error.status === 401 && firstError && typeof firstError.remaining_attempts !== 'undefined') {
      const remaining = firstError.remaining_attempts;
      const max = firstError.max_attempts;

      error.alert.content = app.translator.trans('ralkage-account-lockout.forum.log_in.attempts_remaining', {
        remaining,
        max,
      });
      changed = true;
    }

    if (changed) {
      m.redraw();
    }
  });
});
