<?php
namespace CB\Interfaces;

/**
 * Interface for user menu classes
 */
interface UserMenu
{
    /**
     * return items to be displayed in left accordion region
     * @return array an array of items with javascript properties defined
     */
    public function getAccordionItems();

    /**
     * return items to be displayed in top toolbar
     * @return array set of properties array for displayed buttons
     */
    public function getToolbarItems();
}
