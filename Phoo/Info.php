<?php
/**
 * Phoo Gadget
 *
 * @category   GadgetInfo
 * @package    Phoo
 * @author     Jonathan Hernandez <ion@suavizado.com>
 * @author     Pablo Fischer <pablo@pablo.com.mx>
 * @author     Raul Murciano <raul@murciano.net>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Phoo_Info extends Jaws_Gadget
{
    /**
     * Gadget version
     *
     * @var     string
     * @access  private
     */
    var $_Version = '0.8.4';

    /**
     * Gadget ACLs
     *
     * @var     array
     * @access  private
     */
    var $_ACLs = array(
        'AddPhotos',
        'DeletePhotos',
        'ManagePhotos',
        'ModifyOthersPhotos',
        'ManageComments',
        'ManageAlbums',
        'Settings',
        'Import',
    );

}