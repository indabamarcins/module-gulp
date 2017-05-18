/**
 * Lodash. Obviously.
 */
const _ = require('lodash');

/**
 * Stream manipulation.
 */
const through = require('through2');

/**
 * Path manipulation.
 */
const path = require('path');

/**
 * File objects.
 */
const Vinyl = require('vinyl');

module.exports = findStaticSourceDirectories;

/**
 * Based on the themes and modules, find all possible static directories.
 *
 * Emits a stream of directories containing static assets, and
 */
function findStaticSourceDirectories ({ themes, modules, config }) {
    const locale = config['general/locale/code'];

    function process (file, encoding, cb) {
        const baseDir = file.path;

        // lib/web directory is the global root.
        this.push(new Vinyl({
            path: path.join(baseDir, 'lib/web'),
            buildDir: ''
        }));

        // Module files
        _.each(modules, function (moduleLocation, moduleName) {
            this.push(new Vinyl({
                path: path.join(moduleLocation, 'view/base/web'),
                buildDir: moduleName
            }));

            this.push(new Vinyl({
                path: path.join(moduleLocation, 'view/frontend/web'),
                buildDir: moduleName
            }));

        }.bind(this));

        // Theme files, including module overrides
        _.each(themes, function (themeToBuild) {
            _.each(modules, function (moduleLocation, moduleName) {
                this.push(new Vinyl({
                    path: path.join(themeToBuild, moduleName, 'web'),
                    buildDir: moduleName
                }));
            }.bind(this));

            this.push(new Vinyl({
                path: path.join(themeToBuild, 'web'),
                buildDir: ''
            }));

            this.push(new Vinyl({
                path: path.join(themeToBuild, 'web/i18n', locale),
                buildDir: ''
            }));
        }.bind(this));
1
        cb();
    }

    return through.obj(process);
}
