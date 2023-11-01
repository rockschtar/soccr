const path = require( 'path' );

/**
 * WordPress Dependencies
 */

const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );
const defaultEntryPoints = defaultConfig.entry();
const entryPoints = {
  ...defaultEntryPoints
}

module.exports = {
  ...defaultConfig,
  ...{
    entry: entryPoints
  }
}
