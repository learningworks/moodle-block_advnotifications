/*
 * Gulpfile for Advanced Notifications
 * Enter below command in terminal in plugin's path to run gulp
 * $ npm install gulp gulp-sass gulp-autoprefixer gulp-notify gulp-cssbeautify --save-dev
 */

// Load plugins.
var gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    notify = require('gulp-notify'),
    cssbeautify = require('gulp-cssbeautify');

// Path variables.
var path = {
    styles: {
        src:    'src/scss/',
        build:  'src/..'
    }
};

// Styles.
gulp.task('styles', function() {
    return gulp.src(path.styles.src + '/styles.scss')
        .pipe(sass({}).on('error', sass.logError))
        .pipe(autoprefixer('last 2 version'))
        .pipe(cssbeautify())
        .pipe(gulp.dest(path.styles.build))
        .pipe(notify({message: 'Styles task complete', onLast: true}));
});

// Default task.
gulp.task('default', gulp.series('styles'));