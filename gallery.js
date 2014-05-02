/**
 * Catalog
 * 
 * The catalog plugin adds a catalog or webshop to Wolf CMS.
 * 
 * @package     Plugins
 * @subpackage  catalog
 * 
 * @author      Nic Wortel <nic.wortel@nth-root.nl>
 * @copyright   Nic Wortel, 2012
 * @version     0.1.0
 */

window.onload = (function() {
    try {
        $('.remove-image').live('click', function() {
            $(this).parent().parent().remove();
            return false;
        });
    }
    catch(e) { alert(e) }

});