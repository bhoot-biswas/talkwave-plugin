const [
	scriptConfig,
	moduleConfig,
] = require("@wordpress/scripts/config/webpack.config");
const path = require("path");

module.exports = [
	scriptConfig,
	{
		...moduleConfig,
		entry: {
			...moduleConfig.entry(),
			frontend: [path.resolve(__dirname, "src/frontend.js")],
		},
	},
];
