/**
 *
 * Function for gulp (CSS)
 *
 * @author Takuto Yanagida
 * @version 2022-03-23
 *
 */

'use strict';

const gulp = require('gulp');
const $    = require('gulp-load-plugins')({ pattern: ['gulp-plumber', 'gulp-autoprefixer', 'gulp-clean-css', 'gulp-rename', 'gulp-changed'] });

function makeCssTask(src, dest = './dist', base = null) {
	const cssTask = () => gulp.src(src, { base: base, sourcemaps: true })
		.pipe($.plumber())
		.pipe($.autoprefixer({ remove: false }))
		.pipe($.cleanCss())
		.pipe($.rename({ extname: '.min.css' }))
		.pipe($.changed(dest, { hasChanged: $.changed.compareContents }))
		.pipe(gulp.dest(dest, { sourcemaps: '.' }));
	return cssTask;
}

exports.makeCssTask = makeCssTask;
