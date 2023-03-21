const path = require( 'path' );

const defaultConfig = require( '@wordpress/scripts/config/webpack.config.js' );

const CopyPlugin = require( 'copy-webpack-plugin' );

module.exports = {
	...defaultConfig,
	entry: {
		monal: path.resolve( __dirname, 'resources', 'monal.js' ),
	},
	output: {
		path: path.resolve( __dirname, 'assets' ),
		filename: '[name].js',
	},
	plugins: [
		...defaultConfig.plugins,
		new CopyPlugin( {
			patterns: [
				{ from: './resources/img', to: './img' },
			],
		} ),
	],
};
