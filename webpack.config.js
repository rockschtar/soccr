const path = require( 'path' );

/**
 * WordPress Dependencies
 */

const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const defaultEntryPoints = defaultConfig.entry();
const entryPoints = {
	...defaultEntryPoints,
	// Admin-only scripts that are not blocks and therefore not discovered via block.json.
	'Admin/DonationNotice/index': path.resolve(
		process.cwd(),
		'blocks',
		'Admin',
		'DonationNotice',
		'index.js'
	),
};

module.exports = {
	...defaultConfig,
	...{
		entry: entryPoints,
	},
};
