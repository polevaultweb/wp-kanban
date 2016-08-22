<?php
// Create a helper function for easy SDK access.
function wt_freemius() {
	global $wt_freemius;

	if ( ! isset( $wt_freemius ) ) {
		// Include Freemius SDK.
		require_once dirname(__FILE__) . '/freemius/start.php';

		$wt_freemius = fs_dynamic_init( array(
			'id'                => '378',
			'slug'              => 'wp-trello',
			'type'              => 'plugin',
			'public_key'        => 'pk_fdef636d6e588153bdbd455da5bb3',
			'is_premium'        => false,
			'has_addons'        => false,
			'has_paid_plans'    => false,
			'menu'              => array(
				'slug'       => 'wp-trello',
				'account'    => false,
				'contact'    => false,
				'support'    => false,
				'parent'     => array(
					'slug' => 'options-general.php',
				),
			),
		) );
	}

	return $wt_freemius;
}

// Init Freemius.
wt_freemius();