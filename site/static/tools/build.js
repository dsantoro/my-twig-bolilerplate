{
    baseUrl: '../js/lib',
    mainConfigFile: '../js/app.js',
    out: '../js/app.min.js',
    optimize: 'uglify2',
    include: ['app'],
    name: 'almond',
    uglify2: {
    	mangle: false
    },
    generateSourceMaps: true,
    preserveLicenseComments: false
}
