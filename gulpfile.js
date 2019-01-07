require("babel-polyfill")

const JS_SRC = "./resources/js/"
const DIST = "./resources/js/dist/"
const OUTPUT_PREFIX = "maps"
const SCSS_SRC = "./resources/scss"

const autoprefixer = require("gulp-autoprefixer")
const babelify = require("babelify")
const browserify = require("browserify")
const buffer = require("vinyl-buffer")
const concat = require("gulp-concat")
const gulp = require("gulp")
const minify = require("gulp-minify")
const sass = require("gulp-sass")
const source = require("vinyl-source-stream")
const sourcemaps = require("gulp-sourcemaps")

gulp.task("js", () => {
    var builder = browserify({
        entries: ["FilterList.js"],
        debug: true,
        basedir: JS_SRC,
        paths: ["./resources/js/src/app/components/Findologic"],
        transform: babelify
    })

    return builder
        .bundle()
        .on("error", function(err) {
            console.log(err.toString())
            this.emit("end")
        })
        .pipe(source(OUTPUT_PREFIX + "-component.js"))
        .pipe(buffer())
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(concat(OUTPUT_PREFIX + "-component.js"))
        .pipe(minify())
        .pipe(
            sourcemaps.write(".", {
                includeContent: false,
                sourceRoot: "../src"
            })
        )
        .pipe(gulp.dest(DIST))
})

gulp.task("scss", () => {
    const config = {
        scssOptions: {
            errLogToConsole: true,
            outputStyle: "compressed",
            data: ""
        },
        prefixOptions: {
            browsers: ["last 2 versions", "> 5%", "Firefox ESR"]
        }
    }

    return gulp
        .src(SCSS_SRC + "/maps.scss")
        .pipe(sourcemaps.init())
        .pipe(sass(config.scssOptions).on("error", sass.logError))
        // .pipe(rename(outputFile))
        .pipe(autoprefixer(config.prefixOptions))
        .pipe(sourcemaps.write("."))
        .pipe(gulp.dest(DIST))
})

gulp.task("default", ["js", "scss"])
