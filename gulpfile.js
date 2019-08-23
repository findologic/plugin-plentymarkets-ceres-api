require("babel-polyfill");

const JS_SRC = "./resources/js/src/";
const DIST = "./resources/js/dist/";
const OUTPUT_PREFIX = "filters";

const babelify = require("babelify");
const browserify = require("browserify");
const buffer = require("vinyl-buffer");
const concat = require("gulp-concat");
const gulp = require("gulp");
const sass = require("gulp-sass");
const minify = require("gulp-minify");
const source = require("vinyl-source-stream");
const sourcemaps = require("gulp-sourcemaps");
const cleanCss = require("gulp-clean-css");

gulp.task("js", () => {
    var builder = browserify({
        entries: [
            "app/components/itemList/filter/ItemFilter.js",
            "app/components/itemList/filter/ItemFilterPrice.js",
            "app/components/itemList/filter/ItemFilterTagList.js",
            "app/components/itemList/filter/ItemRangeSlider.js",
            "app/components/itemList/filter/ItemColorTiles.js",
            "app/components/itemList/ItemListSorting.js",
            "app/components/itemList/ItemsPerPage.js",
            "app/components/itemList/Pagination.js",
            "app/components/itemList/ItemSearch.js",
            "app/directives/navigation/renderCategory.js"
        ],
        debug: true,
        basedir: JS_SRC,
        paths: ["./resources/js"],
        transform: babelify
    });

    return builder
        .bundle()
        .on("error", function(err) {
            console.log(err.toString());
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
});

sass.compiler = require('node-sass');

gulp.task('sass', function () {
    return gulp.src('resources/scss/findologic.scss')
        .pipe(sass())
        .pipe(concat('findologic.min.css'))
        .pipe(cleanCss())
        .pipe(gulp.dest('resources/css'));
});

gulp.task("default", gulp.series("js", "sass"));