/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.scss in this case)
import '../styles/app.scss';

let colorPrimary = '#fd912f';
let colorSecondary = '#578ad6';

const $ = require('jquery');
global.$ = global.jQuery = $;

// start the Stimulus application
// import '../bootstrap';
require('startbootstrap-sb-admin/dist/js/scripts');
const bootstrap = require('bootstrap/dist/js/bootstrap.bundle.min');
global.bootstrap = bootstrap;

require('select2');


$(function() {
  $('[data-bs-toggle="tooltip"]').each(function (_, ee) {
    return new bootstrap.Tooltip(ee)
  });
})
