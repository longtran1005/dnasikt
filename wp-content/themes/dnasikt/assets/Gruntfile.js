module.exports = function(grunt) {

    // 1. All configuration goes here
    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        concat: {
            frontend: {
                src: [
                    'javascripts/libs/*.js', // All JS in the libs folder
                    'javascripts/app.js'  // This specific file
                ],
                dest: 'build/js/app.js',
            },
            admin: {
                src: [
                    'javascripts/admin/*.js', // All JS in the libs folder
                    'javascripts/admin.js'  // This specific file
                ],
                dest: 'build/js/admin.js',
            }
        },

        uglify: {
            frontend: {
                src: 'build/js/app.js',
                dest: 'build/js/app.min.js'
            },
            admin: {
                src: 'build/js/admin.js',
                dest: 'build/js/admin.min.js'
            }
        },

        cssmin: {
            combine: {
                files: {
                    'build/css/main.min.css': ['build/css/main.css']
                }
            }
        },

        compass: {
            dist: {
                options: {
                    sassDir: 'sass/',
                    cssDir: 'build/css/',
                    imagesDir: 'images',
                    images: 'images',
                    javascriptsDir: 'javascripts/',
                    fontsDir: 'fonts',
                    environment: 'production',
                    outputStyle: 'expanded',
                    relativeAssets: true,
                    force: true
                }
            }
        },

        watch: {
            compass: {
                files: [
                    'sass/*',
                    'sass/**/*'
                ],
                tasks: ['compass']
            },
            scripts: {
                files: ['javascripts/*.js'],
                tasks: ['concat:frontend', 'concat:admin', 'uglify'],
                options: {
                    spawn: false,
                },
            }
        }

    });

    // 3. Where we tell Grunt we plan to use this plug-in.
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    // 4. Where we tell Grunt what to do when we type "grunt" into the terminal.
    grunt.registerTask('default', ['watch']);
    grunt.registerTask('dev', ['concat','uglify', 'compass']);
    grunt.registerTask('prod', ['concat','uglify', 'compass', 'cssmin']);

};