import app from 'flarum/forum/app';
import Modal from 'flarum/components/Modal';
import Button from 'flarum/components/Button';
import humanTime from 'flarum/helpers/humanTime';
import fullTime from 'flarum/helpers/fullTime';

export default class UnlockUserModal extends Modal {
  className() {
    return 'UnlockUserModal Modal--small';
  }

  title() {
    return app.translator.trans('ralkage-account-lockout.forum.unlock_modal.title', {
      username: this.attrs.user.displayName(),
    });
  }

  content() {
    const user = this.attrs.user;
    const lockedAt = user.lockedAt();
    const lockedUntil = user.lockedUntil();
    const failedCount = user.loginFailedCount();

    return (
      <div className="Modal-body">
        <div className="Form">
          {lockedAt && (
            <p>
              {app.translator.trans('ralkage-account-lockout.forum.unlock_modal.locked_since', {
                date: fullTime(lockedAt),
              })}
            </p>
          )}

          {lockedUntil ? (
            <p>
              {app.translator.trans('ralkage-account-lockout.forum.unlock_modal.locked_until', {
                date: fullTime(lockedUntil),
              })}
            </p>
          ) : (
            <p>{app.translator.trans('ralkage-account-lockout.forum.unlock_modal.manually_locked')}</p>
          )}

          {typeof failedCount !== 'undefined' && (
            <p>
              {app.translator.trans('ralkage-account-lockout.forum.unlock_modal.failed_attempts', {
                count: failedCount,
              })}
            </p>
          )}

          <div className="Form-group">
            <Button className="Button Button--primary Button--block" loading={this.loading} onclick={() => this.unlock()}>
              {app.translator.trans('ralkage-account-lockout.forum.unlock_modal.unlock_button')}
            </Button>
          </div>
        </div>
      </div>
    );
  }

  unlock() {
    this.loading = true;

    this.attrs.user
      .save({ isLocked: false })
      .then(() => this.hide(), this.loaded.bind(this));
  }
}
