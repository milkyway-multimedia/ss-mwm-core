<?php namespace Milkyway\SS\Core\Extensions;

/**
 * Milkyway Multimedia
 * Member.php
 *
 * @package milkyway-multimedia/ss-mwm
 * @author Mellisa Hankins <mell@milkywaymultimedia.com.au>
 */

use DataExtension;

class Member extends DataExtension {
    protected static $_cache_access_cms = [];

    function canAccessCMS($member = null) {
        if(!$member) $member = $this->owner;
        if(is_object($member)) $member = $member->ID;

        if(isset(self::$_cache_access_cms[$member]))
            return self::$_cache_access_cms[$member];

        $members = $this->owner->mapInCMSGroups();

        if($members && $members->count())
            $result = $members->offsetExists($member);
        else
            $result = false;

        self::$_cache_access_cms[$member] = $result;

        return $result;
    }
} 