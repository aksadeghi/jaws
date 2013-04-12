<?php
/**
 * Comments URL maps
 *
 * @category    GadgetMaps
 * @package     Comments
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2013 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
$maps[] = array(
    'Comments',
    'comments[/page/{page}/perpage/{perpage}/order/{order}]',
    array(
        'page' => '[[:digit:]]+',
        'perpage' => '[[:digit:]]+',
        'order' => '[[:digit:]]+',
    )
);
