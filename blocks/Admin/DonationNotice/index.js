/**
 * Donation notice (wp-admin dashboard / plugins screen).
 *
 * Reports the chosen action ("later", "never", "donate" or the native close
 * button) to admin-ajax and removes the notice. The AJAX endpoint, action
 * name and nonce are passed as data attributes on the notice element.
 */
const notice = document.querySelector( '.soccr-donation-notice' );

if ( notice ) {
	notice.addEventListener( 'click', ( event ) => {
		const target = event.target.closest(
			'[data-soccr-action], .notice-dismiss'
		);

		if ( ! target ) {
			return;
		}

		const body = new FormData();
		body.append( 'action', notice.dataset.ajaxAction );
		body.append( '_ajax_nonce', notice.dataset.nonce );
		body.append( 'soccr_action', target.dataset.soccrAction || 'later' );

		fetch( notice.dataset.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body,
			keepalive: true,
		} );

		notice.remove();
	} );
}
