<?php
/**
 * EventsCalendar Gadget
 *
 * @category    Gadget
 * @package     EventsCalendar
 * @author      Mohsen Khahani <mkhahani@gmail.com>
 * @copyright   2016 Jaws Development Group
 * @license     http://www.gnu.org/copyleft/gpl.html
 */
class EventsCalendar_Actions_Today extends Jaws_Gadget_Action
{
    /**
     * Displays today events
     *
     * @access  public
     * @return string XHTML UI
     */
    function Today()
    {
        $GLOBALS['app']->Layout->AddHeadLink('gadgets/EventsCalendar/Resources/index.css');
        $this->AjaxMe('index.js');
        $tpl = $this->gadget->template->load('Today.html');
        $tpl->SetBlock('today');

        $tpl->SetVariable('lbl_hour', _t('EVENTSCALENDAR_HOUR'));
        $tpl->SetVariable('lbl_events', _t('EVENTSCALENDAR_EVENTS'));

        // Menubar
        $action = $this->gadget->action->load('Menubar');
        $tpl->SetVariable('menubar', $action->Menubar());

        // Today
        $jDate = Jaws_Date::getInstance();
        $now = time();
        $today = $jDate->Format($now, 'DN d MN Y');

        $this->SetTitle($today . ' - ' . _t('EVENTSCALENDAR_EVENTS'));
        $tpl->SetVariable('today', $today);
        $tpl->SetVariable('lbl_subject', _t('EVENTSCALENDAR_EVENT_SUBJECT'));
        $tpl->SetVariable('lbl_location', _t('EVENTSCALENDAR_EVENT_LOCATION'));
        $tpl->SetVariable('lbl_type', _t('EVENTSCALENDAR_EVENT_TYPE'));
        $tpl->SetVariable('lbl_priority', _t('EVENTSCALENDAR_EVENT_PRIORITY'));
        $tpl->SetVariable('lbl_date', _t('EVENTSCALENDAR_DATE'));

        // Fetch events
        $info = $jDate->GetDateInfo($now);
        $dayStart = $jDate->ToBaseDate($info['year'], $info['mon'], $info['mday'], 0, 0, 0);
        $dayStart = $GLOBALS['app']->UserTime2UTC($dayStart['timestamp']);
        $dayEnd = $jDate->ToBaseDate($info['year'], $info['mon'], $info['mday'], 23, 59, 59);
        $dayEnd = $GLOBALS['app']->UserTime2UTC($dayEnd['timestamp']);
        $user = (int)$GLOBALS['app']->Session->GetAttribute('user');
        $model = $this->gadget->model->load('Calendar');
        $events = $model->GetTodayEvents($user, $dayStart, $dayEnd);
        if (Jaws_Error::IsError($events)){
            $events = array();
        }

        // Display events
        foreach ($events as $event) {
            $tpl->SetBlock('today/event');

            $tpl->SetVariable('subject', $event['subject']);
            $tpl->SetVariable('type', _t('EVENTSCALENDAR_EVENT_TYPE_' . $event['type']));
            $tpl->SetVariable('location', $event['location']);
            $tpl->SetVariable('priority', _t('EVENTSCALENDAR_EVENT_PRIORITY_' . $event['priority']));

            $startHour = $jDate->Format($event['start_time'], 'H:i');
            $stopHour = $jDate->Format($event['stop_time'], 'H:i');
            $tpl->SetVariable('date', $startHour . ' - ' . $stopHour);

            if ($event['shared']) {
                // TODO: display owner's name
            }

            $url = $user?
                $this->gadget->urlMap('ViewEvent', array('user' => $user, 'event' => $event['id'])) :
                $this->gadget->urlMap('ViewEvent', array('event' => $event['id']));
            $tpl->SetVariable('event_url', $url);

            $tpl->ParseBlock('today/event');
        }

        $tpl->ParseBlock('today');
        return $tpl->Get();
    }
}