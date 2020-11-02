<?php

require_once( dirname(__FILE__).'/base/basepermissionquark.php' );

/**
 * PermissionQuark_Model
 *
 * Representing one numbered pair of type/name, as part of the permission
 * quark system.
 *
 * Using permission quarks, each user object generates a regexp to match
 * against a comma seperated list of quark id:s for each object that
 * should have permission.
 *
 * The list should be prefixed and suffixed with an additional comma, to
 * simplify the regexps, for exmaple:
 * ,2,5,6,
 * should give access to quark id 2, 5 and 6.
 *
 * A user which have quarks 2,3,4 generates the regexp: ,(2|3|4), which
 * matches.
 *
 * quarks is a mapping table between permission objects (for exmaple "user
 * max", "group admins") and a unique id.
 */
class PermissionQuark_Model extends BasePermissionQuark_Model {
}
