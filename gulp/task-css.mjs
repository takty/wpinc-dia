/**
 * Function for gulp (CSS)
 *
 * @author Takuto Yanagida
 * @version 2023-11-08
 */

import gulp from 'gulp';
import plumber from 'gulp-plumber';
import autoprefixer from 'gulp-autoprefixer';
import cleanCss from 'gulp-clean-css';
import rename from 'gulp-rename';
import changed, { compareContents } from 'gulp-changed';

export function makeCssTask(src, dest = './dist', base = null) {
	const cssTask = () => gulp.src(src, { base: base, sourcemaps: true })
		.pipe(plumber())
		.pipe(autoprefixer({ remove: false }))
		.pipe(cleanCss())
		.pipe(rename({ extname: '.min.css' }))
		.pipe(changed(dest, { hasChanged: compareContents }))
		.pipe(gulp.dest(dest, { sourcemaps: '.' }));
	return cssTask;
}
