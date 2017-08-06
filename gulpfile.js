// Include gulp
var gulp = require('gulp');

// Include Our Plugins
var jshint = require('gulp-jshint');
var sass = require('gulp-sass');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');

var applicationJsFiles = [
    'app/View/Accounting/src/js/util/*.js',
    'app/View/Accounting/src/js/i18n/lang-de.js',
    'app/View/Accounting/src/js/app/*.js'
];

// Lint Task
gulp.task('lint', function() {
    return gulp.src(applicationJsFiles)
        .pipe(jshint())
        .pipe(jshint.reporter('default'));
});

// Concatenate & Minify JS
gulp.task('buildJs', function() {
    return gulp.src(applicationJsFiles)
        .pipe(concat('app.js'))
        .pipe(gulp.dest('web/js/'))
        .pipe(rename('app.min.js'))
        .pipe(uglify())
        .pipe(gulp.dest('web/js/'));
});

gulp.task('concatVendor', function() {
    return gulp.src([
        'app/View/Accounting/src/js/vendor/jquery-2.1.3.min.js',
        'app/View/Accounting/src/js/vendor/jquery.mobile-1.4.5.min.js',
        'app/View/Accounting/src/js/vendor/knockout-3.3.0.js'])
        .pipe(concat('vendor.js'))
        .pipe(gulp.dest('web/js/'))
        .pipe(rename('vendor.min.js'))
        .pipe(gulp.dest('web/js/'));
});

gulp.task('concatAllJs', function() {
    return gulp.src([
        'app/View/Accounting/src/js/vendor/jquery-2.1.3.min.js',
        'app/View/Accounting/src/js/vendor/jquery.mobile-1.4.5.min.js',
        'app/View/Accounting/src/js/vendor/knockout-3.3.0.js'])
        .pipe(concat('vendor.js'))
        .pipe(gulp.dest('web/js/'))
        .pipe(rename('vendor.min.js'))
        .pipe(gulp.dest('web/js/'));
});

// Watch Files For Changes
gulp.task('watch', function() {
    gulp.watch(applicationJsFiles, ['lint', 'scripts']);
    gulp.watch('scss/*.scss', ['sass']);
});

// Default Task
gulp.task('default', ['lint', 'buildJs', 'concatVendor', 'watch']);