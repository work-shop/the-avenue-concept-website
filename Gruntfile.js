'use strict';

var path = require('path');
var sass = require('node-sass');



const bourbon_includePaths = require('node-bourbon').includePaths;
const slick_includePaths = path.join(__dirname,'node_modules','slick-carousel', 'slick');

const theme_root = './wp-content/themes/custom';
const output_dir = path.join( theme_root, 'bundles' );

const scss_main_dir = path.join( theme_root, 'scss' );
const scss_main_src = path.join( scss_main_dir, 'main.scss' );
const scss_watch_src = path.join( scss_main_dir, '**', '*.scss' );
const css_main_dest = path.join( output_dir, 'bundle.css' );


const js_main_dir = path.join( theme_root, 'js' );
const js_main_src = path.join( js_main_dir, 'main.js' );
const js_watch_src = path.join( js_main_dir, '**', '*.js');
const js_main_dest = path.join( output_dir, 'bundle.js' );

const assets_watch_dest = path.join( output_dir, 'bundle.*' );
const php_watch_dest = path.join( theme_root, '**', '*.php');



module.exports = function(grunt) {

    var browserify_files = {};
    browserify_files[ js_main_dest ] = [ js_main_src ];

    grunt.initConfig({
        sass: {
            options: {
                implementation: sass,
                sourceMap: true,
                includePaths: [ slick_includePaths ].concat( bourbon_includePaths )
            },
            dev: {
                src: [ scss_main_src ],
                dest: css_main_dest,
            }
        },
        browserify: {
            dev: {
                files: browserify_files,
                options: {
                    transform: [
                        ['babelify', {presets: 'env'}]
                    ]
                }
            },
        },
        watch: {
            sass: {
                // We watch and compile sass files as normal but don't live reload here
                files: [ scss_watch_src ],
                tasks: ['sass'],
            },
            browserify: {
                files: [ js_watch_src ],
                tasks: ['browserify']
            },
            livereload: {
                // Here we watch the files the sass task will compile to
                // These files are sent to the live reload server after sass compiles to them
                options: {
                    livereload: true
                },
                files: [ assets_watch_dest, php_watch_dest ]
            }
        },
    });

	grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks('grunt-browserify');
	grunt.registerTask('default', ['watch']);

};
