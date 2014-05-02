<div class="page" id="page_album" style="display: block;">
    <div title="<?php echo __('Settings'); ?>" id="div-page_album">
      <table cellspacing="0" cellpadding="0" border="0">
        <tr>
          <td class="label"><label><?php echo __('Album'); ?></label></td>
          <td class="field">
              <select name="page_album[album_id]">
                  <option value="NULL">&#8212; <?php echo __('none'); ?> &#8212;</option>
                  <?php foreach ($albums as $album): ?>
                  <option value="<?php echo $album->id; ?>" <?php if ($album->id == $current): ?>selected="selected"<?php endif; ?>><?php echo $album->title; ?></option>
                  <?php endforeach; ?>
              </select>
          </td>
        </tr>
      </tbody></table>
    </div>
</div>