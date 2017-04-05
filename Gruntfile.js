module.exports = function(grunt) {

	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-cssmin');
	grunt.loadNpmTasks('grunt-contrib-jshint');

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		cssmin: {
			minify: {
				expand: true,
				cwd: 'assets/css/',
				src: ['*.css', '!*.min.css'],
				dest: 'assets/css/',
				ext: '.min.css'
			}
		},
		jshint: {
			grunt: {
				src: ['Gruntfile.js']
			},
			pluginjs: {
				expand: true,
				cwd: 'assets/js/',
				src: [
					'*.js',
					'!*.min.js'
				]
			}
		},
		uglify: {
			theme: {
				expand: true,
				files: {
					//'js/general.min.js': [ 'js/general.js' ]
				}
			}
		}
	});

	// Register tasks
	grunt.registerTask('production', ['jshint', 'cssmin', 'uglify']);

	// Default task
	grunt.registerTask('default', ['production']);
};
