require("babel-polyfill")

const JS_SRC = "./resources/js/"
const DIST = "./resources/js/dist/"
const OUTPUT_PREFIX = "filters"

const babelify = require("babelify")
const browserify = require("browserify")
const buffer = require("vinyl-buffer")
const concat = require("gulp-concat")
const gulp = require("gulp")
const minify = require("gulp-minify")
const source = require("vinyl-source-stream")
const sourcemaps = require("gulp-sourcemaps")

gulp.task("js", () => {
    var builder = browserify({
        entries: ["ItemFilter.js", "ItemFilterPrice.js", "ItemFilterTagList.js", "ItemListSorting.js", "ItemsPerPage.js"],
        debug: true,
        basedir: JS_SRC,
        paths: ["./resources/js"],
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

gulp.task("default", ["js"])