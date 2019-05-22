const gulp = require('gulp')
const gulpPhpUnit = require('gulp-phpunit')
const gulpPhpcs = require('gulp-phpcs')
const pump = require('pump')
const uglify = require('gulp-uglify')
const rename = require('gulp-rename')
const usage = require('gulp-help-doc')
const csso = require('gulp-csso')
const { exec } = require('child_process')
const gulpZip = require('gulp-zip')
const concat = require('gulp-concat')
const minimist = require('minimist')
const semver = require('semver')
const gulpDel = require('del')
const fs = require('fs')

const PACKAGE_NAME = 'agb-connector'
const PACKAGE_DESTINATION = './dist'
const PACKAGE_PATH = `${PACKAGE_DESTINATION}/${PACKAGE_NAME}`

/*
 * Set Options and their defaults
 */
const options = minimist(process.argv.slice(2), {
  string: [
    'packageVersion',
    'compressPath',
    'compressedName',
  ],
  default: {
    compressPath: process.compressPath || '.',
    compressedName: process.compressedName || '.',
  },
})

/**
 * Check the Package Version value is passed to the script
 * @param done
 * @throws Error if the package version option isn't found
 */
async function validatePackageVersion (done)
{
  await 1

  if (!'packageVersion' in options) {
    throw new Error('Missing --packageVersion option with a semver value.')
  }

  if (semver.valid(options.packageVersion) === null) {
    throw new Error(
      'Invalid package version, please follow MAJOR.MINOR.PATCH semver convention.',
    )
  }

  done()
}

/**
 * PHP Unit Task
 * @param done
 * @returns {Promise<any>}
 */
function phpunit (done)
{
  return new Promise(() => {
    pump(
      gulp.src('./phpunit.xml.dist'),
      gulpPhpUnit(
        './vendor/bin/phpunit',
        {
          debug: false,
          clear: false,
          notify: false,
          statusLine: false,
        },
      ),
      done,
    )
  })
}

/**
 * PHPCS Task
 * @returns {*}
 */
function phpcs ()
{
  return gulp.src('./src/**/*.php').pipe(gulpPhpcs({
    bin: './vendor/bin/phpcs',
    standard: 'Inpsyde',
  })).pipe(
    gulpPhpcs.reporter('fail', { failOnFirst: true }),
  )
}

/**
 * Create the package
 * @returns {Promise}
 */
function copyPackageFiles (done)
{
  return new Promise(() => {
    pump(
      gulp.src([
        './assets/**',
        './src/**',
        './agb-connector.php',
        './composer.json',
        './uninstall.php',
        './LICENSE',
      ], {
        base: './',
      }),
      gulp.dest(PACKAGE_PATH),
      done,
    )
  })
}

/**
 * Build Css
 *
 * @param done
 * @returns {Promise<any>}
 */
function buildCss (done)
{
  return new Promise(() => {
    pump(
      gulp.src([`${PACKAGE_PATH}/assets/css/**/*.css`]),
      csso(),
      rename({ suffix: '.min' }),
      gulp.dest([
        `${PACKAGE_PATH}/assets/css/`,
      ]),
      done,
    )
  })
}

/**
 * Build JavaScript
 *
 * @param done
 * @returns {Promise<any>}
 */
function buildJs (done)
{
  return new Promise(() => {
    pump(
      gulp.src([
        `${PACKAGE_PATH}/assets/js/**/*.js`,
      ]),
      uglify(),
      rename({ suffix: '.min' }),
      gulp.dest(`${PACKAGE_PATH}/assets/js/`),
      done,
    )
  })
}

/**
 * Build Readme.txt
 *
 * Merge Readme.txt with Changelog.txt
 */
function buildReadmeTxt (done)
{
  return new Promise(() => {
    pump(
      gulp.src(['./wp-org/readme.txt', './changelog.md']),
      concat('./readme.txt'),
      gulp.dest(`${PACKAGE_PATH}`),
      done,
    )
  })
}

/**
 * Run composer for Dist
 * @returns {Promise}
 */
function composer (done)
{
  return fs.access(
    `${PACKAGE_PATH}/composer.json`,
    fs.constants.F_OK,
    (error) => {
      if (error) {
        throw error
      }

      exec(
        `composer install --prefer-dist --optimize-autoloader --no-dev --working-dir=${PACKAGE_PATH}`,
        error => {
          if (error) {
            throw error
          }

          done()
        },
      )
    })
}

/**
 * Clean Up Dist
 */
async function cleanupDist ()
{
  return await gulpDel(
    [
      `${PACKAGE_PATH}/composer.json`,
      `${PACKAGE_PATH}/composer.lock`,
    ],
  )
}

/**
 * Compress the package
 * @returns {*}
 */
function compressPackage (done)
{
  const { packageVersion, compressPath } = options
  const timeStamp = new Date().getTime()

  if (!fs.existsSync(PACKAGE_DESTINATION)) {
    throw new Error(
      `Cannot create package, ${PACKAGE_DESTINATION} doesn't exists.`)
  }

  return new Promise(() => {
    exec(
      `git log -n 1 | head -n 1 | sed -e 's/^commit //' | head -c 8`,
      {},
      (error, stdout) => {
        let shortHash = error ? timeStamp : stdout
        const compressedName = options.compressedName || `${PACKAGE_NAME}-${packageVersion}-${shortHash}`;

          pump(
          gulp.src(`${PACKAGE_DESTINATION}/**/*`, {
            base: PACKAGE_DESTINATION,
          }),
          gulpZip(`${compressedName}.zip`),
          gulp.dest(
            compressPath,
            {
              base: PACKAGE_DESTINATION,
              cwd: './',
            },
          ),
          done,
        )
      },
    )
  })
}

/**
 * Delete content within the Dist directory
 * @returns {*}
 */
async function deleteDist ()
{
  return await gulpDel(PACKAGE_DESTINATION)
}

/**
 * Gulp Help
 * @returns {Promise}
 */
function help ()
{
  return usage(gulp)
}

exports.help = help
exports.default = help

/**
 * Run Tests
 *
 * @task {tests}
 */
exports.tests = gulp.series(
  phpcs,
  phpunit,
)

/**
 * Run Build Assets
 *
 * @task {buildAssets}
 */
exports.buildAssets = gulp.series(
  buildJs,
  buildCss,
)

/**
 * Build Package
 *
 * @task {dist}
 * @arg {packageVersion} Package version, the version must to be conformed to semver.
 * @arg {compressedName} The name of the file after compression. Optional. Default will be {plugin-name}-{version}-{shortash}.zip
 * @arg {compressPath} Where the resulting package zip have to be stored.
 */
exports.dist = gulp.series(
  validatePackageVersion,
  deleteDist,
  copyPackageFiles,
  buildJs,
  buildCss,
  buildReadmeTxt,
  composer,
  cleanupDist,
  compressPackage,
  deleteDist,
)
