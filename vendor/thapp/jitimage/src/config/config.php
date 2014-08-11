<?php

return array(

    /* ------------------------------------------------
     * the base route:
     * ------------------------------------------------
     */

    'route'      => 'images',

    /* ------------------------------------------------
     * the base directory from where to dynamically resolve
     * images
     * ------------------------------------------------
     */

    'base'       => public_path(),

    /* ------------------------------------------------
     * driver that powers the image manipulation:
     *
     * `gd` (the GDlibraray), `im` (the Imagemagick library),
     *  or `imagick` (this Imagick php extension).
     * ------------------------------------------------
     */

    'driver'      => 'im',

    /* ------------------------------------------------
     * cache settings
     * ------------------------------------------------
     */

    'cache'       => array(
    /* ------------------------------------------------
     * cache directory
     * ------------------------------------------------
     */
        'path'         => storage_path(),
    /* ------------------------------------------------
     * base route for resolving cached images
     * ------------------------------------------------
     */
        'route'        => 'jit/storage',
    /* ------------------------------------------------
     * name prefix for cached images
     * ------------------------------------------------
     */
        'prefix'       => 'jit',
    /* ------------------------------------------------
     * cache processed images only in this environments
     * ------------------------------------------------
     */
        'environments' => array('production')
    ),

    /* ------------------------------------------------
     * the compression quality:
     * 0 - 100
     * ------------------------------------------------
     */

    'quality'     => 60,

    /* ------------------------------------------------
     * imagemagick specific settings:
     * ------------------------------------------------
     */

    'imagemagick' => array(
        'path' => '/usr/local/bin',
        'bin'  => 'convert',
    ),

    /* ------------------------------------------------
     * image filter:
     * ------------------------------------------------
     */

    'filter' => array(
        'Circle'    => 'circ',
        'GreyScale' => 'gs'
    ),

    /* ------------------------------------------------
     * only this expression should be allowed
     *
     * allow mode 2 crop rescale, with a 200x200 px crop and a grey scale
     * filter:
     *
     *  'thumbs' => '2/200/200/5, filter:gs'
     *
     * allow mode 1 resize, with a resize of 800px width
     * greyscale filter:
     *
     *  'gallery' => '1/800/0, filter:gs',
     *
     * allow mode 4 best fit, with a resize of max 800px width
     * and 600px height, no filters:
     *
     *  'preview' => '4/800/600'
     *
     * ------------------------------------------------
     */

    'recipes' => array(),

    /* ------------------------------------------------
     * a list of trusted sites that deliver assets:
     *  e.g. 'http://25.media.tumblr.com'
     * ------------------------------------------------
     */
    'trusted-sites' => array(),

    /* ------------------------------------------------
     * `generic` or `xsend`
     * ------------------------------------------------
     */
    'response-type' => 'generic'
);

