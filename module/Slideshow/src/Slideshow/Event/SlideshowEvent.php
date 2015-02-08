<?php
namespace Slideshow\Event;

use User\Service\UserIdentity as UserIdentityService;
use Application\Event\ApplicationAbstractEvent;

class SlideshowEvent extends ApplicationAbstractEvent
{
    /**
     * Add category event
     */
    const ADD_CATEGORY = 'slideshow_add_category';

    /**
     * Edit category event
     */
    const EDIT_CATEGORY = 'slideshow_edit_category';

    /**
     * Fire edit category event
     *
     * @param integer $categoryId
     * @return void
     */
    public static function fireEditCategoryEvent($categoryId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Slideshow category edited by guest'
            : 'Event - Slideshow category edited by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$categoryId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $categoryId];

        self::fireEvent(self::EDIT_CATEGORY, 
                $categoryId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }

    /**
     * Fire add category event
     *
     * @param integer $categoryId
     * @return void
     */
    public static function fireAddCategoryEvent($categoryId)
    {
        // event's description
        $eventDesc = UserIdentityService::isGuest()
            ? 'Event - Slideshow category added by guest'
            : 'Event - Slideshow category added by user';

        $eventDescParams = UserIdentityService::isGuest()
            ? [$categoryId]
            : [UserIdentityService::getCurrentUserIdentity()['nick_name'], $categoryId];

        self::fireEvent(self::ADD_CATEGORY, 
                $categoryId, UserIdentityService::getCurrentUserIdentity()['user_id'], $eventDesc, $eventDescParams);
    }
}