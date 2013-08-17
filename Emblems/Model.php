<?php
/**
 * Emblems Gadget
 *
 * @category   GadgetModel
 * @package    Emblems
 * @author     Jorge A Gallegos <kad@gulags.org.mx>
 * @copyright  2004-2013 Jaws Development Group
 * @license    http://www.gnu.org/copyleft/gpl.html
 */
class Emblems_Model extends Jaws_Gadget_Model
{
    /**
     * Get Emblems
     *
     * @access  public
     * @param   bool    $onlyenabled    if need to get only the enabled emblems
     * @param   mixed   $limit          Optional. Limit of data to retrieve (false = returns all)
     * @return  array   An array emblems properties and Jaws_Error on error
     */
    function GetEmblems($onlyenabled = false, $limit = false)
    {
        if (is_numeric($limit)) {
            $rs = $GLOBALS['db']->setLimit(10, $limit);
            if (Jaws_Error::IsError($rs)) {
                return new Jaws_Error($rs->getMessage(), 'SQL');
            }
        }

        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        $emblemTable->select('id:integer', 'title', 'src', 'url', 'emblem_type', 'enabled:boolean');

        if ($onlyenabled){
            $emblemTable->where('enabled', true);
        }
        $rs = $emblemTable->orderBy('id asc')->fetchAll();
        if (Jaws_Error::IsError($rs)){
            return new Jaws_Error($rs->getMessage(), 'SQL');
        }
        return $rs;
    }

    /**
     * Get information of an emblem
     *
     * @access  public
     * @param   int     $id     Emblem's id
     * @return  mixed   An array contains emblems information and Jaws_Error on error
     */
    function GetEmblem($id)
    {
        $emblemTable = Jaws_ORM::getInstance()->table('emblem');
        $res = $emblemTable->select('id:integer', 'title', 'src', 'url')->where('id', $id)->fetchRow();
        if (Jaws_Error::IsError($res)) {
            return new Jaws_Error($res->getMessage(), 'SQL');
        }
        return $res;
    }

    /**
     * Serves as a 'dictionary' for emblem types
     *(I rather put it here than writing the switch everytime...)
     *
     * @access  public
     * @param   string    $type     code of the type
     * @return  string    The description of the type
     */
    function TranslateType($type)
    {
        switch (strtoupper($type)) {
        case 'P':
            return _t('EMBLEMS_POWERED_BY');
            break;
        case 'S':
            return _t('EMBLEMS_SUPPORTS');
            break;
        case 'V':
            return _t('EMBLEMS_IS_VALID');
            break;
        case 'L':
            return _t('EMBLEMS_LICENSED_UNDER');
            break;
        case 'B':
            return _t('EMBLEMS_BEST_VIEW');
            break;
        }
    }

}