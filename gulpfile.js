/*
 * Gulpfile for Advanced Notifications
 * Enter below command in terminal in plugin's path to run gulp
 * $ npm install gulp gulp-sass gulp-autoprefixer gulp-notify gulp-clean-css --save-dev
 */

// Load plugins.
var gulp = require('gulp'),
    sass = require('gulp-sass'),
    autoprefixer = require('gulp-autoprefixer'),
    notify = require('gulp-notify'),
    clean = require('gulp-clean-css');

// Path variables.
var path = {
    styles: {
        src:    'src/scss/',
        build:  ''
    }
};

// Styles.
gulp.task('styles', function() {
    return gulp.src(path.styles.src + '/styles.scss')
        .pipe(sass({}).on('error', sass.logError))
        .pipe(autoprefixer('last 2 version'))
        .pipe(clean({format: 'beautify', level: {'1': {all: false, removeWhitespace: false}}}))
        .pipe(gulp.dest(path.styles.build))
        .pipe(notify({message: 'Styles task complete', onLast: true}));
});

// Default task.
gulp.task('default', ['styles', 'watch']);

// Watch.
gulp.task('watch', function() {
    // Watch .scss files.
    gulp.watch(path.styles.src + '/**/*.scss', ['styles']);
});