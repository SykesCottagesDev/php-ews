<?php

namespace jamesiarmes\PEWS\Calendar;

use jamesiarmes\PEWS\API\Type;
use jamesiarmes\PEWS\API;
use jamesiarmes\PEWS\API\Enumeration;
use DateTime;

/**
 * An API end point for Calendar items
 *
 * Class API
 * @package jamesiarmes\PEWS\Calendar
 */
class CalendarAPI extends API
{
    protected $_folderId;

    public function pickCalendar($displayName = null)
    {
        if ($displayName == 'default.calendar' || $displayName == null) {
            $folder = $this->getFolderByDistinguishedId('calendar');
        } else {
            $folder = $this->getFolderByDisplayName($displayName, 'calendar');
        }

        $this->_folderId = $folder->getFolderId();
        return $this;
    }

    public function getFolderId()
    {
        return $this->_folderId;
    }

    /**
     * Create one or more calendar items
     *
     * @param $items CalendarItem[]|CalendarItem|Array or more calendar items to create
     * @return \jamesiarmes\PEWS\API\CreateItemResponseType
     */
    public function createCalendarItems($items)
    {
        //If the item passed in is an object, or if it's an assosiative array waiting to be an object, let's put it in to an array
        if (!is_array($items) || Type::arrayIsAssoc($items)) {
            $items = array($items);
        }

        $item = array('CalendarItem'=>$items);
        $options = array(
            'SendMeetingInvitations' => Enumeration\CalendarItemCreateOrDeleteOperationType::SEND_TO_NONE,
            'SavedItemFolderId' => array(
                'FolderId' => array('Id' => $this->getFolderId()->getId())
            )
        );

        $items = $this->createItems($item, $options);

        if (!is_array($items)) {
            $items = array($items);
        }

        return $items;
    }

    /**
     * Get a list of calendar items between two dates/times
     *
     * @param string|DateTime $start
     * @param string|DateTime $end
     * @param array $options
     * @return mixed
     */
    public function getCalendarItems($start = '12:00 AM', $end = '11:59 PM', $options = array())
    {
        if (!($start instanceof DateTime)) {
            $start = new DateTime($start);
        }

        if (!($end instanceof DateTime)) {
            $end = new DateTime($end);
        }

        $request = array(
            'Traversal' => 'Shallow',
            'ItemShape' => array(
                'BaseShape' => 'AllProperties'
            ),
            'CalendarView' => array(
                'MaxEntriesReturned' => 100,
                'StartDate' => $start->format('c'),
                'EndDate' => $end->format('c')
            ),
            'ParentFolderIds' => array(
                'FolderId' => array(
                    'Id' => $this->getFolderId()->getId(),
                    'ChangeKey' => $this->getFolderId()->getChangeKey()
                )
            )
        );

        $request = array_merge($request, $options);

        $request = Type::buildFromArray($request);
        $response = $this->getClient()->FindItem($request);
        $items = $response->getItems()->getCalendarItem();
        if ($items == null) {
            return array();
        }

        if (!is_array($items)) {
            $items = array($items);
        }

        return $items;
    }

    public function getCalendarItem($id, $changeKey)
    {
        return $this->getItem([ 'Id' => $id, 'ChangeKey' => $changeKey ]);
    }

    /**
     * Updates a calendar item with changes
     *
     * @param $id
     * @param $changeKey
     * @param $changes
     * @return mixed
     */
    public function updateCalendarItem($id, $changeKey, $changes)
    {
        $setItemFields = array();

        //Add each property to a setItemField
        foreach ($changes as $key => $value) {
            $fullName = $this->getFieldUriByName($key, 'calendar');

            $setItemFields[] = array(
                'FieldURI' => array('FieldURI' => $fullName),
                'CalendarItem' => array($key => $value)
            );
        }

        //Create the request
        $request = array(
            'ItemChange' => array(
                'ItemId' => array('Id' => $id, 'ChangeKey' => $changeKey),
                'Updates' => array('SetItemField' =>$setItemFields)
            )
        );

        $options = array(
            'SendMeetingInvitationsOrCancellations' => 'SendToNone'
        );

        $items =  $this->updateItems($request, $options);
        $items = $items->getCalendarItem();

        if (!is_array($items)) {
            $items = array($items);
        }

        return $items;
    }

    public function deleteCalendarItem($itemId, $changeKey)
    {
        return $this->deleteItems(array(
            'Id' => $itemId,
            'ChangeKey' => $changeKey
        ), array(
            'SendMeetingCancellations' => 'SendToNone'
        ));
    }

    public function deleteAllCalendarItems($start = '12:00 AM', $end = '11:59 PM', $options = array())
    {
        $items = $this->getCalendarItems($start, $end, $options);
        foreach ($items as $item) {
            $this->deleteCalendarItem($item->getItemId()->getId(), $item->getItemId()->getChangeKey());
        }
    }

    /**
     * Get a list of changes on the calendar items
     *
     * @param null $syncState
     * @param array $options
     * @return mixed
     */
    public function listChanges($syncState = null, $options = array())
    {
        return parent::listItemChanges($this->getFolderId()->Id, $syncState, $options);
    }
}
