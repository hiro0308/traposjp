const { src, dest, watch, series, parallel }  = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const plumber = require('gulp-plumber');
const notify = require('gulp-notify');
const postcss = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssdeclsort = require('css-declaration-sorter');
const browserSync = require('browser-sync');
const browserify = require('browserify');
const source = require('vinyl-source-stream');

//sassコンパイル
const compileSass = (done) => {
 src('./src/scss/**/style.scss', { sourcemaps: true })
   .pipe(
     plumber({ errorHandler: notify.onError('Error: <%= error.message %>') })
   )
   .pipe(sass({ outputStyle: 'expanded' }))
   .pipe(postcss([autoprefixer(
     {
       grid: "autoplace",
       cascade: false
     }
   )]))
   .pipe(postcss([cssdeclsort({ order: 'alphabetical' })]))
   .pipe(dest('./dist/css/', { sourcemaps: './sourcemaps' }));
 done();
};

//
const buildServer = (done) => {
	browserSync.init({
		port: 8888,
		//静的サイト
		server: { baseDir: './' },
		//動的サイト
		// files: ['./**/*.php'],
		// proxy: 'http://localsite.local/',
		open: true,
		watchOptions: {
			debounceDelay: 1000,
		},
	});
	done();
};

const browserReload = (done) => {
	browserSync.reload();
	done();
};

const watchFiles = (done) => {
	watch( './src/scss/**/*.scss', series(compileSass, browserReload));
  watch( './**/*.html', series(browserReload));
  watch( './**/*.js', series(browserReload));
  done();
};



module.exports = {
 sass: compileSass,
 default: parallel(buildServer, watchFiles),
};