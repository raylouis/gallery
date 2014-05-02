<?php
if (!defined('IN_CMS')) { exit(); }

/**
 * Gallery
 * 
 * The Gallery plugin for Wolf CMS is a third-party plugin for managing photo albums and displaying them on your website.
 * 
 * @package     Plugins
 * @subpackage  gallery
 * 
 * @author      Nic Wortel <nic.wortel@nth-root.nl>
 * @copyright   Nic Wortel, 2013
 * @version     0.1.0
 */

?>
<h1><?php echo __('Albums'); ?></h1>

<div id="site-map-def">
    <div class="page"><?php echo __('Album'); ?> (<a href="#" id="toggle_reorder"><?php echo __('reorder'); ?></a>)</div>
    <div class="view"><?php echo __('View'); ?></div>
    <div class="modify"><?php echo __('Modify'); ?></div>
</div>

<ul id="site-map-root">
    <li id="page-1" class="node level-0">
      <div class="page" style="padding-left: 4px">
        <span class="w1">
            <a class="edit-link" href="<?php echo get_url('plugin/gallery/album/edit/'.$root->id); ?>" title="<?php echo $root->id; ?>">
                <img align="middle" class="icon" src="<?php echo URI_PUBLIC;?>wolf/icons/file-folder-32.png" alt="page icon" /> <span class="title"><?php echo $root->title; ?></span>
            </a>
        </span>
      </div>
      <div class="view">
        
      </div>
      <div class="modify">
        <?php if (AuthUser::hasPermission('catalog_category_add')): ?>
          <a href="<?php echo get_url('plugin/gallery/album/add', $root->id); ?>"><img src="<?php echo URI_PUBLIC;?>wolf/icons/action-add-16.png" align="middle" title="<?php echo __('Add child'); ?>" alt="<?php echo __('Add child'); ?>" /></a>&nbsp;
        <?php endif; ?>
        
        <?php if (AuthUser::hasPermission('catalog_category_delete')): ?>
          <img class="remove" src="<?php echo URI_PUBLIC;?>wolf/icons/action-delete-disabled-16.png" align="middle" alt="<?php echo __('remove icon disabled'); ?>" title="<?php echo __('Remove unavailable'); ?>"/>
        <?php endif; ?>
      </div>

<?php echo $content_children; ?>

    </li>
</ul>

<style type="text/css">

    ul {
        /*
        list-style: none inside;
        margin: 0;
        padding: 0;
        margin-top: 0.5em;
        order: 1px solid grey;
        min-height: 10px;
        height: auto !important;
        height: 30px;
        */
    }

    .child {
        min-height: 10px;
        height: auto !important;
        height: 30px;
    }

    .child li {
/*        padding: 0;
        padding-left: 0.5em;
        argin: 1px;
        margin: 0;
        margin-top: 0.5em;
        margin-left: 0.5em;*/
        padding-left: 0.5em;
        margin-left: 0.5em;
        border-left: 10px solid grey;
    }

    .i-sortable { display: block; background-color: #EDFE86; }
    .i-sortable li { display: block; background-color: #fff; }

    .placeholder {
        height: 2.4em;
        line-height: 1.2em;
        border: 1px solid #fcefa1;
        background-color: #fbf9ee;
        color: #363636;
        /*height: 5px;
        background: #f00;*/
    }


</style>

<script type="text/javascript">
    //jQuery(function() {
        jQuery.fn.spinnerSetup = function spinnerSetup() {
            this.each(function() {
                var pid = $(this).attr('id')
                $('#'+pid).hide()  // hide it initially
                .ajaxStop(function() {
                    $('#'+pid).hide();
                });
            });

            return this;
        };

        jQuery.fn.sitemapSetup = function sitemapSetup() {
            this.each(function () {
                if($('ul',this).length) return;
                var pid = $(this).attr('id').split('_')[1];
            });

            return this;
        };
        
        jQuery.fn.expandableSetup = function expandableSetup() {
            $(this).live('click', function() {
                if ($(this).hasClass("expanded")) {
                    $(this).removeClass("expanded");
                    $(this).attr('src', '<?php echo URI_PUBLIC; ?>wolf/admin/images/expand.png');

                    var parent = $(this).parents("li.node:first")
                    var parentId = parent.attr('id').split('_')[1];

                    $('#page_'+parentId).removeClass('children-visible').addClass('children-hidden').children('ul').hide();
                }
                else {
                    $(this).addClass("expanded");
                    $(this).attr('src', '<?php echo URI_PUBLIC; ?>wolf/admin/images/collapse.png');
                    var parent = $(this).parents("li.node:first");
                    var parentId = parent.attr('id').split('_')[1];
                    $('#page_'+parentId).removeClass('children-hidden').addClass('children-visible');

                    if ($('#page_'+parentId).children('ul').length == 0) {
                        $('#busy-'+parentId).show();
                        $.get("<?php echo get_url('plugin/gallery/albumChildren/'); ?>"+parentId+'/'+'1', function(data) {                        
                            $('#page_'+parentId).append(data);
                            $('#site-map li').sitemapSetup();
                            $('.busy').spinnerSetup();
                        });
                    }
                    else {
                        $('#page_'+parentId).children('ul').show();
                    }
                }
                // update parents with children list expanded
                (function persistExpanded() {
                    var expanded_rows = [];
                    $('ul#site-map img.expanded').parents('li').not('#page-0').each(function() {
                        expanded_rows.push( $(this).attr('id').split('_')[1] );
                    });
                    var rows = expanded_rows.reverse().toString();
                    if(rows===''){
                        rows += ';expires=Sat, 25 Dec 2010 06:07:00 UTC';
                    }
                    document.cookie = 'gallery_album_expanded_rows=' + rows + ';'
                })();
            });
        };
        
        jQuery.fn.sortableSetup = function sortableSetup() { 
            $('ul#site-map').nestedSortable({
                disableNesting: 'no-nest',
                forcePlaceholderSize: true,
                handle: 'div',
                items: 'li',
                opacity: .6,
                placeholder: 'placeholder',
                tabSize: 25,
                tolerance: 'pointer',
                toleranceElement: '> span',
                listType: 'ul',
                helper: 'clone',
                beforeStop: function(event, ui) {
                    // quick checks incase they have taken it out of the sitemap tree
                    if(ui.item.parents("#page-1").is('li') === false)
                    {    
                        $("ul#site-map").nestedSortable('cancel');
                    }
                },
                stop: function(event, ui) {                    
                    var order = $("ul#site-map").nestedSortable('serialize');
                    
                     $.ajax({
                        type: 'post',
                        url: '<?php echo get_url('plugin/gallery/reorder/album'); ?>',
                        data: order,
                        cache: false
                    });  
                                                 
                    // check where we have put the row so we can change styles if needbe
                    var parent = ui.item.parent().parents('li.node:first');                    
                                        
                    if(parent.hasClass('level-0'))
                    {
                        // put back as homepage child
                        var childClass = '';
                        if(ui.item.hasClass('no-children'))
                        {
                            childClass = 'no-children';
                        } else if(ui.item.hasClass('children-visible'))
                        {
                            childClass = 'children-visible';
                        } else if(ui.item.hasClass('children-hidden'))
                        {
                            childClass = 'children-hidden';
                        }                
                        ui.item.removeClass();
                        ui.item.addClass('node level-1 '+childClass);
                    } else if(parent.find('img.expander').hasClass('expanded') == false)
                    {
                        // put into a row that has children but is closed
                        ui.item.parent().hide().remove();
                        
                        // todo: improve
                        // dirty fix for reloading tree
                        window.location.reload(true);
                        
                    } else if(parent.find('img.expander').hasClass('expanded') == true)
                    {
                        // put into a row that has expanded children
                        var siblingClass = ui.item.siblings('li.node').attr('class');
                        var levelClass = siblingClass.split(' ');
                        var childClass = '';
                        if(ui.item.hasClass('no-children'))
                        {
                            childClass = 'no-children';
                        } else if(ui.item.hasClass('children-visible'))
                        {
                            childClass = 'children-visible';
                        } else if(ui.item.hasClass('children-hidden'))
                        {
                            childClass = 'children-hidden';
                        }
                        ui.item.removeClass();
                        ui.item.addClass('node '+levelClass[1]+' '+childClass);    
                    }
                }
            });
            return this;
        };       
         
        
$(document).ready(function(){
    $('#site-map li').sitemapSetup();
    $("img.expander").expandableSetup(); 
    $(".busy").spinnerSetup();
    $('ul#site-map').sortableSetup();
    $('ul#site-map').nestedSortable('disable');

    $('#toggle_reorder').toggle(
            function(){
                $('ul#site-map').nestedSortable('enable');  
                $('img.handle_reorder').show();
                $('#toggle_reorder').text('<?php echo __('disable reorder');?>');
            },
            function() {
                $('ul#site-map').nestedSortable('disable');               
                $('img.handle_reorder').hide();
                $('#toggle_reorder').text('<?php echo __('reorder');?>');
            }
    )      
});
</script>