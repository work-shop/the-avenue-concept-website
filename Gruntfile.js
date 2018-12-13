'use strict';

var path = require('path');
var sass = require('node-sass');


const bourbon_includePaths = require('node-bourbon').includePaths;
const slick_includePaths = path.join(__dirname,'node_modules','slick-carousel', 'slick');

const theme_root = './wp-content/themes/custom';
const output_dir = path.join( theme_root, 'bundles' );

const scss_main_dir = path.join( theme_root, 'scss' );
const scss_main_src = path.join( scss_main_dir, 'main.scss' );
const scss_admin_src = path.join( scss_main_dir, 'admin.scss' )
const scss_watch_src = path.join( scss_main_dir, '**', '*.scss' );
const css_main_dest = path.join( output_dir, 'bundle.css' );
const css_admin_dest = path.join( output_dir, 'admin-bundle.css' );


const js_main_dir = path.join( theme_root, 'js' );
const js_main_src = path.join( js_main_dir, 'main.js' );
const js_watch_src = path.join( js_main_dir, '**', '*.js');
const js_main_dest = path.join( output_dir, 'bundle.js' );

const php_watch_dest = path.join( theme_root, '**', '*.php');



module.exports = function(grunt) {
    require('jit-grunt')( grunt, {
        'sass': 'grunt-sass',
        'watch': 'grunt-contrib-watch',
        'extract_sourcemap': 'grunt-extract-sourcemap',
        'browserify': 'grunt-browserify'
    });

    var extract_files = {};
    extract_files[ output_dir ] = [ js_main_dest ];

    grunt.initConfig({
        pkg: grunt.file.readJSON( 'package.json' ),
        sass: {
            options: {
                implementation: sass,
                includePaths: [ slick_includePaths ].concat( bourbon_includePaths )
            },
            adminDev: {
                options: {
                    sourceMap: true, // sourcemaps
                    sourceComments: true,
                    outputStyle: 'expanded',
                },
                files: [{
                    src: [ scss_admin_src ],
                    dest: css_admin_dest,
                }]
            },
            adminDist: {
                options: {
                    sourceMap: true, // sourcemaps
                    sourceComments: false,
                    outputStyle: 'compressed'
                },
                files: [{
                    src: [ scss_admin_src ],
                    dest: css_admin_dest,
                }]
            },
            dev: {
                options: {
                    sourceMap: true, // sourcemaps
                    sourceComments: true,
                    outputStyle: 'expanded',
                },
                files: [{
                    src: [ scss_main_src ],
                    dest: css_main_dest,
                }]
            },
            dist: {
                options: {
                    sourceMap: true, // sourcemaps
                    sourceComments: false,
                    outputStyle: 'compressed'
                },
                files: [{
                    src: [ scss_main_src ],
                    dest: css_main_dest,
                }]
            }
        },
        browserify: {
            dev: {
                src: [ js_main_src ],
                dest: js_main_dest,
                options: {
                    watch: true,
                    browserifyOptions: {
                        debug: true // sourcemaps
                    },
                    transform: [
                        ['babelify', {presets: 'env'}]
                    ]
                }
            },
            dist: {
                src: [ js_main_src ],
                dest: js_main_dest,
                options: {
                    transform: [
                        ['babelify', {presets: 'env'}],
                        ['uglifyify', {global: true}]
                    ]
                }
            }
        },
        extract_sourcemap: {
            dev: {
                options: { removeSourcesContent: true },
                files: extract_files
            }
        },
        watch: {
            options: {
                atBegin: false,
                livereload: true
            },
            scss: {
                options: {
                    livereload: false,
                    interrupt: true
                },
                files: [ scss_watch_src ],
                tasks: ['sass:dev', 'sass:adminDev'],

            },
            css: {
                files: [ css_main_dest ],
                tasks: []
            },
            js: {
                files: [ js_main_dest ],
                tasks: []
            },
            php: {
                files: [ php_watch_dest ],
                tasks: []
            }
        },
    });

  // grunt.loadNpmTasks('grunt-contrib-watch');
    // grunt.loadNpmTasks('grunt-sass');
    // grunt.loadNpmTasks('grunt-browserify');
    // grunt.loadNpmTasks('grunt-extract-sourcemap');

  grunt.registerTask('default', ['sass:dev', 'browserify:dev', 'extract_sourcemap:dev', 'watch']);
  grunt.registerTask('dev', ['browserify:dev','sass:dev', 'sass:adminDev', 'extract_sourcemap:dev']);
  grunt.registerTask('dist', ['browserify:dist','sass:dist', 'sass:adminDist']);


};
