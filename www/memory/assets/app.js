/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)

global.$ = require('jquery');
import './styles/global.scss';

// this "modifies" the jquery module: adding behavior to it
// the bootstrap module doesn't export/return anything
//import { createPopper } from '@popperjs/core';
require('bootstrap');
//import { Tooltip, Toast, Popover } from 'bootstrap';
//require('bootstrap/dist/js/bootstrap.js');
// or you can include specific pieces
// require('bootstrap/js/dist/tooltip');

$(function () {
    $('[data-toggle="popover"]').popover();
});