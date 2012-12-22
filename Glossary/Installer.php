<?php
/**
 * Glossary Installer
 *
 * @category    GadgetModel
 * @package     Glossary
 * @author      Ali Fazelzadeh <afz@php.net>
 * @copyright   2012 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class Glossary_Installer extends Jaws_Gadget_Installer
{
    /**
     * Install the gadget
     *
     * @access  public
     * @return  mixed   True on success and Jaws_Error on failure
     */
    function Install()
    {
        $result = $this->installSchema('schema.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        $variables = array();
        $variables['timestamp'] = $GLOBALS['db']->Date();

        $result = $this->installSchema('insert.xml', $variables, 'schema.xml', true);
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

    /**
     * Uninstalls the gadget
     *
     * @access  public
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Uninstall()
    {
        $result = $GLOBALS['db']->dropTable('glossary');
        if (Jaws_Error::IsError($result)) {
            $gName  = _t('GLOSSARY_NAME');
            $errMsg = _t('GLOBAL_ERROR_GADGET_NOT_UNINSTALLED', $gName);
            $GLOBALS['app']->Session->PushLastResponse($errMsg, RESPONSE_ERROR);
            return new Jaws_Error($errMsg, $gName);
        }

        return true;
    }

    /**
     * Upgrades the gadget
     *
     * @access  public
     * @param   string  $old    Current version (in registry)
     * @param   string  $new    New version (in the $gadgetInfo file)
     * @return  mixed   True on Success or Jaws_Error on Failure
     */
    function Upgrade($old, $new)
    {
        if (version_compare($old, '0.7.0', '<')) {
            $result = $this->installSchema('0.7.0.xml', '', "$old.xml");
            if (Jaws_Error::IsError($result)) {
                return $result;
            }
        }

        $result = $this->installSchema('schema.xml', '', '0.7.0.xml');
        if (Jaws_Error::IsError($result)) {
            return $result;
        }

        return true;
    }

}