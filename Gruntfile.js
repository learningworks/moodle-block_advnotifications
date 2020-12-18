/* eslint no-undef: "error" */
/* eslint camelcase: 2 */
/* eslint-env node */

"use strict";

/**
 * Gruntfile for the plugin.
 *
 * This file configures tasks to be run by Grunt
 * https://gruntjs.com/ for the current theme.
 *
 *
 * Requirements:
 * -------------
 * nodejs, npm, grunt-cli.
 *
 * Installation:
 * -------------
 * node and npm: instructions at https://nodejs.org/
 *
 * run for each task (and grunt-cli): `[sudo] npm install -g grunt-cli --save-dev`
 * run for each task to install locally:: `[sudo] npm install grunt-cli --save-dev`
 *
 * node dependencies: run `npm install` in the root directory.
 *
 *
 * Usage:
 * ------
 * Call tasks from the plugin root directory. Default behaviour
 * (calling only `grunt`) is to run the watch task detailed below.
 *
 *
 * Porcelain tasks:
 * ----------------
 * The nice user interface intended for everyday use. Provide a
 * high level of automation and convenience for specific use-cases.
 *
 * grunt css     Create CSS from the SCSS.
 *
 *
 * Based on https://github.com/willianmano/moodle-theme_moove/ Gruntfile.js - thank you!
 * Based on https://gitlab.com/jezhops/moodle-theme_adaptable/  Gruntfile.js - thank you!
 *
 * @package     block_advnotifications
 * @author      Based on code originally written by:
 *              Willian Mano - {@link https://conecti.me} & G J Barnard - {@link https://moodle.org/user/profile.php?id=442195}
 * @copyright   2020 LearningWorks <selma@learningworks.ac.nz>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

module.exports = function(grunt) {
    let decachephp = "../../admin/cli/purge_caches.php";
    const sass = require('node-sass');

    grunt.initConfig({
        watch: {
            options: {
                nospawn: true,
                livereload: true
            },
            css: {
                files: ["scss/**/*.scss"],
                tasks: ["css", "decache"]
            },
            js: {
                files: ["amd/src/*.js"],
                tasks: ["uglify", "decache"]
            }
        },
        sass: {
            dist: {
                files: {
                    "styles.css": "scss/styles.scss"
                }
            },
            options: {
                includePaths: ["scss"],
                implementation: sass,
                indentWidth: 4,
                outputStyle: "expanded"
            }
        },
        stylelint: {
            scss: {
                options: {syntax: "scss"},
                src: ["scss/**/*.scss"]
            },
            css: {
                src: ["styles.css"],
                options: {
                    configOverrides: {
                        rules: {
                            "at-rule-no-unknown": true,
                        }
                    }
                }
            }
        },
        uglify: {
            dist: {
                files: {
                    'amd/build/custom.min.js': 'amd/src/custom.js',
                    'amd/build/notif.min.js': 'amd/src/notif.js',
                }
            }
        },
        exec: {
            decache: {
                cmd: "php " + decachephp,
                callback: function(error) {
                    if (!error) {
                        grunt.log.writeln("Moodle caches purged.");
                    } else {
                        grunt.log.writeln("Some error occurred:");
                        grunt.log.writeln(error);
                    }
                }
            }
        }
    });

    // Load tasks.
    grunt.loadNpmTasks("grunt-exec");
    grunt.loadNpmTasks("grunt-contrib-watch");
    grunt.loadNpmTasks('grunt-sass');
    grunt.loadNpmTasks("grunt-stylelint");
    grunt.loadNpmTasks('grunt-contrib-uglify');

    // Register tasks.
    grunt.registerTask("default", ["watch"]);
    grunt.registerTask("css", ["stylelint:scss", "sass", "stylelint:css"]);
    grunt.registerTask("stylecheck", ["stylelint:scss", "stylelint:css"]);
    grunt.registerTask("js", ["uglify"]);
    grunt.registerTask("decache", ["exec:decache"]);
};