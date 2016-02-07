<?php
/**
 * Poll - SiteActivity hook
 *
 * @category    GadgetHook
 * @package     Poll
 * @author      Mojtaba Ebrahimi <ebrahimi@zehneziba.ir>
 * @copyright   2008-2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/lesser.html
 */
class Poll_Hooks_SiteActivity extends Jaws_Gadget_Hook
{
    /**
     * Defines translate statements of Site activity
     *
     * @access  public
     * @return  void
     */
    function Execute()
    {
        $items = array();
        $items['Poll'] = _t('POLL_SITEACTIVITY_ACTION_POLL');

        return $items;
    }

}