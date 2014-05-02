<?php foreach ($album->images as $image): ?>
<a title="DSC8282" href="<?php echo $image->attachment->url(); ?>" rel="my-gallery" class="photo">
<?php echo $image->attachment->html_img('crop', 150); ?></a>
<?php endforeach; ?>