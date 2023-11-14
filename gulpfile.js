const gulp = require( 'gulp' );
const $ = require( 'gulp-load-plugins' )();
const webpack = require( 'webpack-stream' );
const webpackBundle = require( 'webpack' );
const named = require( 'vinyl-named' );
const { dumpSetting } = require( '@kunoichi/grab-deps' );
const sass = require( 'gulp-sass' )( require( 'sass' ) );

let plumber = true;

// Sassのタスク
gulp.task( 'sass', function () {

	return gulp.src( [ './assets/scss/**/*.scss' ] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( $.sourcemaps.init() )
		.pipe( sass( {
			errLogToConsole: true,
			outputStyle: 'compressed',
			sourceComments: false,
			sourcemap: true,
			includePaths: [
				'./assets/scss',
			]
		} ) )
		.pipe( $.autoprefixer() )
		.pipe( $.sourcemaps.write( './map' ) )
		.pipe( gulp.dest( './dist/css' ) );
} );

// Style lint.
gulp.task( 'stylelint', function () {
	let task = gulp.src( [ './assets/scss/**/*.scss' ] );
	if ( plumber ) {
		task = task.pipe( $.plumber() );
	}
	return task.pipe( $.stylelint( {
		reporters: [
			{
				formatter: 'string',
				console: true,
			},
		],
	} ) );
} );

// Package jsx.
gulp.task( 'jsx', function () {
	return gulp.src( [
		'./assets/js/**/*.js',
	] )
		.pipe( $.plumber( {
			errorHandler: $.notify.onError( '<%= error.message %>' )
		} ) )
		.pipe( named( ( file ) => {
			return file.relative.replace( /\.[^\.]+$/, '' );
		} ) )
		.pipe( webpack( require( './webpack.config.js' ), webpackBundle ) )
		.pipe( gulp.dest( './dist/js' ) );
} );

// ESLint
gulp.task( 'eslint', function () {
	let task = gulp.src( [
		'./assets/js/**/*.js',
	] );
	if ( plumber ) {
		task = task.pipe( $.plumber() );
	}
	return task.pipe( $.eslint( { useEslintrc: true } ) )
		.pipe( $.eslint.format() );
} );

// Dump dependencies.
gulp.task( 'dump', ( done ) => {
	dumpSetting( 'dist' );
	done();
} );

// watch
gulp.task( 'watch', ( done ) => {
	// Make SASS
	gulp.watch( 'assets/scss/**/*.scss', gulp.parallel( 'sass', 'stylelint' ) );
	// Bundle JS
	gulp.watch( [ 'assets/js/**/*.{js,jsx}' ], gulp.parallel( 'jsx', 'eslint' ) );
	// Dump setting.
	gulp.watch( [
		'dist/js/**/*.js',
		'dist/css/**/*.css',
	], gulp.task( 'dump' ) );
	done();
} );


// Toggle plumber.
gulp.task( 'noplumber', ( done ) => {
	plumber = false;
	done();
} );

// Build
gulp.task( 'build', gulp.series( gulp.parallel( 'jsx', 'sass' ), 'dump' ) );

// Default Tasks
gulp.task( 'default', gulp.series( 'watch' ) );

// Lint
gulp.task( 'lint', gulp.series( 'noplumber', gulp.parallel( 'stylelint', 'eslint' ) ) );
