<?php
/**
 * Plugin Name:   DKO Column Edit
 * Plugin URI:    https://github.com/davidosomething/dko-coledit
 * Description:   Adds AJAX editable columns to the listing page for posts/pages/post-types
 * Author:        David O'Trakoun (@davidosomething)
 * Author Email:  me@davidosomething.com
 * Author URI:    http://www.davidosomething.com/
 * Version:       1.0.0
 *
 * License:       GPL2 (accompanying plugins licensed separately)
 *
  Copyright 2012  David O'Trakoun  (email: me@davidosomething.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

class DKOColEdit
{

  const slug    = 'DKOColEdit';
  const version = '1.0.0';

  /**
   * __construct
   *
   * @return void
   */
  public function __construct() {
  }

} // DKOColEdit


if (is_admin()) {
  require_once __DIR__.'/admin.php';
  $dkocoledit = new DKOColEdit_Admin();
}
else {
  $dkocoledit = new DKOColEdit();
}
