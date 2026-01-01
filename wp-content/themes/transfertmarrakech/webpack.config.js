const path = require('path');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = (env, argv) => {
    const isProd = argv.mode === 'production';
    return {
        mode: isProd ? 'production' : 'development',
        entry: {
            default: [
                './assets/js/components/global.js',
                './assets/js/animated-title.js',
                './assets/js/animated-lines.js',
                './assets/js/parallax.js',
            ],
            home: [
                './assets/js/carousel.js',
                './assets/js/vehicles.js',
            ],
        },
        output: {
            filename: 'js/[name].js',
            path: path.resolve(__dirname, 'assets'),
        },
        module: {
            rules: [
                {
                    test: /\.css$/i,
                    use: ['style-loader', 'css-loader'],
                },
            ],
        },
        devtool: isProd ? false : 'source-map',
        optimization: {
            minimize: true,
            minimizer: [
                new TerserPlugin({
                    terserOptions: {
                        compress: true,
                        mangle: true,
                    },
                    extractComments: false,
                }),
            ],
        },
        watch: !isProd,
    }
};
